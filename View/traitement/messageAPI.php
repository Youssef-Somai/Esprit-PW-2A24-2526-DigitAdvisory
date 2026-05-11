<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controller/MessageController.php';

$session = $_SESSION['user'] ?? null;
$action  = $_POST['action'] ?? $_GET['action'] ?? '';

$adminOnlyActions = ['admin_get_all','admin_delete_conv','admin_get_stats','admin_get_messages'];
if (!$session && !in_array($action, $adminOnlyActions, true)) {
    echo json_encode(['error'=>'Non autorisé']); exit;
}

$ctrl   = new MessageController();
$userId = $session ? (int)$session['id_user'] : 0;
$role   = $session['role'] ?? '';

// ─── heartbeat: update user status every page call ───────────────────────────
if ($userId) $ctrl->updateUserStatus($userId);

switch ($action) {

    case 'get_conversations':
        $convs  = $ctrl->getConversations($userId);
        $result = [];
        foreach ($convs as $c) {
            $preview = '';
            if ($c['last_type']==='text')  $preview = mb_substr($c['last_content']??'',0,60);
            elseif ($c['last_type']==='file')  $preview = '📎 '.($c['last_file_name']??'Fichier');
            elseif ($c['last_type']==='audio') $preview = '🎤 Message vocal';
            $result[] = [
                'id_conversation' => $c['id_conversation'],
                'other_id'        => $c['other_id'],
                'other_name'      => $c['other_name'],
                'other_initials'  => $c['other_initials'],
                'other_role'      => $c['other_role'],
                'last_preview'    => $preview,
                'last_at'         => $c['last_at'] ?? $c['created_at'],
                'unread'          => (int)($c['unread'] ?? 0),
            ];
        }
        echo json_encode($result); break;

    case 'get_messages':
        $convId = (int)($_GET['id_conversation'] ?? 0);
        if (!$convId) { echo json_encode(['error'=>'id_conversation manquant']); break; }

        if ($userId) {
            $db = config::getConnexion();
            $st = $db->prepare('SELECT * FROM conversation WHERE id_conversation=:id AND (id_user1=:u OR id_user2=:u2)');
            $st->execute(['id'=>$convId,'u'=>$userId,'u2'=>$userId]);
            if (!$st->fetch()) { echo json_encode(['error'=>'Accès refusé']); break; }
            $ctrl->markRead($convId, $userId);
        }

        $messages = $ctrl->getMessagesWithReactions($convId, $userId);
        $meta     = $ctrl->getConversationMeta($convId, $userId);

        $result = [];
        foreach ($messages as $m) {
            $result[] = [
                'id_message'  => $m['id_message'],
                'id_sender'   => $m['id_sender'],
                'type'        => $m['type'],
                'content'     => $m['content'],
                'file_path'   => $m['file_path'],
                'file_name'   => $m['file_name'],
                'file_size'   => $m['file_size'],
                'is_read'     => (bool)$m['is_read'],
                'is_edited'   => (bool)$m['is_edited'],
                'is_deleted'  => (bool)$m['is_deleted'],
                'created_at'  => $m['created_at'],
                'reactions'   => $m['reactions'],
            ];
        }
        echo json_encode([
            'messages'     => $result,
            'other_online' => $meta['other_online'],
            'other_typing' => $meta['other_typing'],
            'other_name'   => $meta['other_name'],
        ]); break;

    case 'send_message':
        $convId = (int)($_POST['id_conversation'] ?? 0);
        $type   = $_POST['type'] ?? 'text';
        if (!$convId) { echo json_encode(['error'=>'id_conversation manquant']); break; }
        $db = config::getConnexion();
        $st = $db->prepare('SELECT * FROM conversation WHERE id_conversation=:id AND (id_user1=:u OR id_user2=:u2)');
        $st->execute(['id'=>$convId,'u'=>$userId,'u2'=>$userId]);
        if (!$st->fetch()) { echo json_encode(['error'=>'Accès refusé']); break; }
        if ($type==='text') {
            $content = trim($_POST['content'] ?? '');
            if ($content==='') { echo json_encode(['error'=>'Contenu vide']); break; }
            $id = $ctrl->sendMessage($convId, $userId, 'text', $content);
            echo json_encode(['success'=>true,'id_message'=>$id]);
        } elseif (in_array($type,['file','audio'],true) && isset($_FILES['file'])) {
            $info = $ctrl->handleFileUpload($_FILES['file'], __DIR__.'/../../uploads/messages/');
            if (!$info) { echo json_encode(['error'=>'Fichier non accepté']); break; }
            $id = $ctrl->sendMessage($convId,$userId,$type,null,$info['file_path'],$info['file_name'],$info['file_size']);
            echo json_encode(['success'=>true,'id_message'=>$id,'file_info'=>$info]);
        } else { echo json_encode(['error'=>'Type invalide']); }
        break;

    case 'edit_message':
        $msgId   = (int)($_POST['id_message'] ?? 0);
        $content = trim($_POST['content'] ?? '');
        if (!$msgId||$content==='') { echo json_encode(['error'=>'Données manquantes']); break; }
        echo json_encode(['success'=>$ctrl->editMessage($msgId,$userId,$content)]); break;

    case 'delete_message':
        $msgId = (int)($_POST['id_message'] ?? 0);
        if (!$msgId) { echo json_encode(['error'=>'id_message manquant']); break; }
        echo json_encode(['success'=>$ctrl->deleteMessage($msgId,$userId)]); break;

    case 'delete_conversation':
        $convId = (int)($_POST['id_conversation'] ?? 0);
        if (!$convId) { echo json_encode(['error'=>'id_conversation manquant']); break; }
        echo json_encode(['success'=>$ctrl->deleteConversation($convId,$userId)]); break;

    case 'create_conversation':
        $otherId = (int)($_POST['id_other_user'] ?? 0);
        if (!$otherId||$otherId===$userId) { echo json_encode(['error'=>'Utilisateur invalide']); break; }
        $convId = $ctrl->getOrCreateConversation($userId,$otherId);
        echo json_encode(['success'=>(bool)$convId,'id_conversation'=>$convId]); break;

    case 'get_users':
        $users  = $ctrl->getAvailableUsers($userId,$role);
        $result = [];
        foreach ($users as $u) {
            $result[] = [
                'id_user'      => $u['id_user'],
                'display_name' => $u['display_name'],
                'initials'     => $u['initials'],
                'role'         => $u['role'],
                'domaine'      => $u['domaine'] ?? $u['secteur_activite'] ?? '',
            ];
        }
        echo json_encode($result); break;

    case 'mark_read':
        $convId = (int)($_POST['id_conversation'] ?? 0);
        $ctrl->markRead($convId,$userId);
        echo json_encode(['success'=>true]); break;

    case 'toggle_reaction':
        $msgId = (int)($_POST['id_message'] ?? 0);
        $emoji = trim($_POST['emoji'] ?? '');
        if (!$msgId||$emoji==='') { echo json_encode(['error'=>'Données manquantes']); break; }
        $result = $ctrl->toggleReaction($msgId,$userId,$emoji);
        echo json_encode(array_merge(['success'=>true],$result)); break;

    case 'update_typing':
        $convId = (int)($_POST['id_conversation'] ?? 0);
        $ctrl->updateUserStatus($userId, $convId ?: null);
        echo json_encode(['success'=>true]); break;

    case 'admin_get_all':
        echo json_encode($ctrl->getAllConversations()); break;

    case 'admin_get_stats':
        echo json_encode($ctrl->getStats()); break;

    case 'admin_get_messages':
        $convId   = (int)($_GET['id_conversation'] ?? 0);
        $messages = $ctrl->getMessages($convId);
        $result   = [];
        foreach ($messages as $m) {
            $sender   = $ctrl->getUserById((int)$m['id_sender']);
            $result[] = [
                'id_message'  => $m['id_message'],
                'sender_name' => $sender ? $ctrl->getDisplayName($sender) : 'Inconnu',
                'type'        => $m['type'],
                'content'     => $m['content'],
                'file_name'   => $m['file_name'],
                'is_deleted'  => (bool)$m['is_deleted'],
                'is_edited'   => (bool)$m['is_edited'],
                'created_at'  => $m['created_at'],
            ];
        }
        echo json_encode($result); break;

    case 'admin_delete_conv':
        $convId = (int)($_POST['id_conversation'] ?? 0);
        echo json_encode(['success'=>$ctrl->adminDeleteConversation($convId)]); break;

    default:
        echo json_encode(['error'=>'Action inconnue: '.htmlspecialchars($action)]);
}
