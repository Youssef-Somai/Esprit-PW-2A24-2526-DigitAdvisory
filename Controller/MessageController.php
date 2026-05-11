<?php

require_once __DIR__ . '/../config.php';

class MessageController
{
    // ─── Helpers ────────────────────────────────────────────────────────────

    public function getUserById(int $id): ?array
    {
        try {
            $st = config::getConnexion()->prepare('SELECT * FROM user WHERE id_user=:id');
            $st->execute(['id'=>$id]);
            return $st->fetch() ?: null;
        } catch (Exception $e) { return null; }
    }

    public function getDisplayName(array $user): string
    {
        if ($user['role'] === 'entreprise') return $user['nom_entreprise'] ?? $user['email'];
        $full = trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? ''));
        return $full !== '' ? $full : $user['email'];
    }

    public function getInitials(array $user): string
    {
        $name  = $this->getDisplayName($user);
        $parts = preg_split('/\s+/', $name);
        $init  = strtoupper(mb_substr($parts[0], 0, 1));
        if (isset($parts[1])) $init .= strtoupper(mb_substr($parts[1], 0, 1));
        return $init ?: '??';
    }

    private function normalizePair(int $a, int $b): array
    {
        return [$a < $b ? $a : $b, $a < $b ? $b : $a];
    }

    // ─── Conversations ───────────────────────────────────────────────────────

    public function getConversations(int $userId): array
    {
        try {
            $db  = config::getConnexion();
            $sql = "SELECT c.*,
                        m.content    AS last_content,
                        m.type       AS last_type,
                        m.file_name  AS last_file_name,
                        m.created_at AS last_at,
                        (SELECT COUNT(*) FROM message
                         WHERE id_conversation=c.id_conversation
                           AND id_sender!=:uid2 AND is_read=0 AND is_deleted=0) AS unread
                    FROM conversation c
                    LEFT JOIN message m ON m.id_message=(
                        SELECT id_message FROM message
                        WHERE id_conversation=c.id_conversation AND is_deleted=0
                        ORDER BY created_at DESC LIMIT 1)
                    WHERE (c.id_user1=:uid3 OR c.id_user2=:uid4)
                      AND ((c.id_user1=:uid5 AND c.deleted_by1=0)
                        OR (c.id_user2=:uid6 AND c.deleted_by2=0))
                    ORDER BY COALESCE(m.created_at,c.created_at) DESC";
            $st  = $db->prepare($sql);
            $st->execute(['uid2'=>$userId,'uid3'=>$userId,'uid4'=>$userId,'uid5'=>$userId,'uid6'=>$userId]);
            $rows = $st->fetchAll();
            foreach ($rows as &$row) {
                $otherId = ($row['id_user1']==$userId) ? $row['id_user2'] : $row['id_user1'];
                $other   = $this->getUserById($otherId);
                $row['other_name']     = $other ? $this->getDisplayName($other) : 'Inconnu';
                $row['other_initials'] = $other ? $this->getInitials($other)    : '??';
                $row['other_role']     = $other['role'] ?? '';
                $row['other_id']       = $otherId;
            }
            return $rows;
        } catch (Exception $e) { return []; }
    }

    public function getOrCreateConversation(int $userId, int $otherId): ?int
    {
        try {
            $db = config::getConnexion();
            [$u1,$u2] = $this->normalizePair($userId,$otherId);
            $st = $db->prepare('SELECT id_conversation FROM conversation WHERE id_user1=:u1 AND id_user2=:u2');
            $st->execute(['u1'=>$u1,'u2'=>$u2]);
            $row = $st->fetch();
            if ($row) {
                $col = ($userId===$u1) ? 'deleted_by1' : 'deleted_by2';
                $db->prepare("UPDATE conversation SET $col=0 WHERE id_conversation=:id")->execute(['id'=>$row['id_conversation']]);
                return (int)$row['id_conversation'];
            }
            $db->prepare('INSERT INTO conversation (id_user1,id_user2) VALUES (:u1,:u2)')->execute(['u1'=>$u1,'u2'=>$u2]);
            return (int)$db->lastInsertId();
        } catch (Exception $e) { return null; }
    }

    public function deleteConversation(int $convId, int $userId): bool
    {
        try {
            $db = config::getConnexion();
            $st = $db->prepare('SELECT * FROM conversation WHERE id_conversation=:id');
            $st->execute(['id'=>$convId]);
            $conv = $st->fetch();
            if (!$conv) return false;
            $col = ($conv['id_user1']==$userId) ? 'deleted_by1' : 'deleted_by2';
            $db->prepare("UPDATE conversation SET $col=1 WHERE id_conversation=:id")->execute(['id'=>$convId]);
            $st2 = $db->prepare('SELECT deleted_by1,deleted_by2 FROM conversation WHERE id_conversation=:id');
            $st2->execute(['id'=>$convId]);
            $r = $st2->fetch();
            if ($r && $r['deleted_by1'] && $r['deleted_by2'])
                $db->prepare('DELETE FROM conversation WHERE id_conversation=:id')->execute(['id'=>$convId]);
            return true;
        } catch (Exception $e) { return false; }
    }

    public function adminDeleteConversation(int $convId): bool
    {
        try {
            config::getConnexion()->prepare('DELETE FROM conversation WHERE id_conversation=:id')->execute(['id'=>$convId]);
            return true;
        } catch (Exception $e) { return false; }
    }

    // ─── Messages ────────────────────────────────────────────────────────────

    public function getMessages(int $convId): array
    {
        try {
            $st = config::getConnexion()->prepare('SELECT * FROM message WHERE id_conversation=:id ORDER BY created_at ASC');
            $st->execute(['id'=>$convId]);
            return $st->fetchAll();
        } catch (Exception $e) { return []; }
    }

    public function getMessagesWithReactions(int $convId, int $myUserId): array
    {
        $messages = $this->getMessages($convId);
        foreach ($messages as &$msg) {
            $msg['reactions'] = $this->getReactions((int)$msg['id_message'], $myUserId);
        }
        return $messages;
    }

    public function sendMessage(int $convId, int $senderId, string $type='text',
        ?string $content=null, ?string $filePath=null, ?string $fileName=null, ?int $fileSize=null): ?int
    {
        try {
            $db = config::getConnexion();
            $st = $db->prepare('INSERT INTO message (id_conversation,id_sender,type,content,file_path,file_name,file_size)
                                 VALUES (:conv,:sender,:type,:content,:fp,:fn,:fs)');
            $st->execute(['conv'=>$convId,'sender'=>$senderId,'type'=>$type,'content'=>$content,'fp'=>$filePath,'fn'=>$fileName,'fs'=>$fileSize]);
            $db->prepare('UPDATE conversation SET updated_at=NOW() WHERE id_conversation=:id')->execute(['id'=>$convId]);
            return (int)$db->lastInsertId();
        } catch (Exception $e) { return null; }
    }

    public function editMessage(int $msgId, int $senderId, string $content): bool
    {
        try {
            $st = config::getConnexion()->prepare(
                'UPDATE message SET content=:c,is_edited=1 WHERE id_message=:id AND id_sender=:s AND is_deleted=0 AND type="text"');
            $st->execute(['c'=>$content,'id'=>$msgId,'s'=>$senderId]);
            return $st->rowCount() > 0;
        } catch (Exception $e) { return false; }
    }

    public function deleteMessage(int $msgId, int $senderId): bool
    {
        try {
            $st = config::getConnexion()->prepare('UPDATE message SET is_deleted=1 WHERE id_message=:id AND id_sender=:s');
            $st->execute(['id'=>$msgId,'s'=>$senderId]);
            return $st->rowCount() > 0;
        } catch (Exception $e) { return false; }
    }

    public function markRead(int $convId, int $userId): bool
    {
        try {
            $st = config::getConnexion()->prepare(
                'UPDATE message SET is_read=1 WHERE id_conversation=:conv AND id_sender!=:uid AND is_read=0');
            $st->execute(['conv'=>$convId,'uid'=>$userId]);
            return true;
        } catch (Exception $e) { return false; }
    }

    // ─── Reactions ───────────────────────────────────────────────────────────

    public function getReactions(int $msgId, int $myUserId): array
    {
        try {
            $st = config::getConnexion()->prepare(
                'SELECT emoji, COUNT(*) AS cnt, MAX(id_user=:uid) AS reacted_by_me
                 FROM message_reaction WHERE id_message=:id GROUP BY emoji');
            $st->execute(['id'=>$msgId,'uid'=>$myUserId]);
            $rows = $st->fetchAll();
            foreach ($rows as &$row) {
                $row['cnt']           = (int)$row['cnt'];
                $row['reacted_by_me'] = (bool)(int)$row['reacted_by_me'];
            }
            return $rows;
        } catch (Exception $e) { return []; }
    }

    public function toggleReaction(int $msgId, int $userId, string $emoji): array
    {
        try {
            $db = config::getConnexion();
            $st = $db->prepare('SELECT id_reaction FROM message_reaction WHERE id_message=:m AND id_user=:u AND emoji=:e');
            $st->execute(['m'=>$msgId,'u'=>$userId,'e'=>$emoji]);
            $existing = $st->fetch();
            if ($existing) {
                $db->prepare('DELETE FROM message_reaction WHERE id_reaction=:id')->execute(['id'=>$existing['id_reaction']]);
                return ['action'=>'removed'];
            }
            $db->prepare('INSERT INTO message_reaction (id_message,id_user,emoji) VALUES (:m,:u,:e)')->execute(['m'=>$msgId,'u'=>$userId,'e'=>$emoji]);
            return ['action'=>'added'];
        } catch (Exception $e) { return ['error'=>$e->getMessage()]; }
    }

    // ─── User status (en ligne + frappe) ─────────────────────────────────────

    public function updateUserStatus(int $userId, ?int $typingIn=null): void
    {
        try {
            $db      = config::getConnexion();
            $typingAt = $typingIn ? date('Y-m-d H:i:s') : null;
            $db->prepare('INSERT INTO user_status (id_user,last_seen,typing_in,typing_at) VALUES (:u,NOW(),:tin,:tat)
                          ON DUPLICATE KEY UPDATE last_seen=NOW(),typing_in=:tin2,typing_at=:tat2')
               ->execute(['u'=>$userId,'tin'=>$typingIn,'tat'=>$typingAt,'tin2'=>$typingIn,'tat2'=>$typingAt]);
        } catch (Exception $e) {}
    }

    public function getConversationMeta(int $convId, int $myUserId): array
    {
        try {
            $db   = config::getConnexion();
            $st   = $db->prepare('SELECT * FROM conversation WHERE id_conversation=:id');
            $st->execute(['id'=>$convId]);
            $conv = $st->fetch();
            if (!$conv) return ['other_online'=>false,'other_typing'=>false,'other_name'=>''];
            $otherId   = ($conv['id_user1']==$myUserId) ? $conv['id_user2'] : $conv['id_user1'];
            $other     = $this->getUserById($otherId);
            $otherName = $other ? $this->getDisplayName($other) : 'Utilisateur';
            $st2 = $db->prepare('SELECT * FROM user_status WHERE id_user=:uid');
            $st2->execute(['uid'=>$otherId]);
            $s = $st2->fetch();
            if (!$s) return ['other_online'=>false,'other_typing'=>false,'other_name'=>$otherName];
            $online  = (time() - strtotime($s['last_seen'])) < 120;
            $typing  = $s['typing_in']==$convId && $s['typing_at'] && (time()-strtotime($s['typing_at']))<10;
            return ['other_online'=>$online,'other_typing'=>(bool)$typing,'other_name'=>$otherName];
        } catch (Exception $e) { return ['other_online'=>false,'other_typing'=>false,'other_name'=>'']; }
    }

    // ─── Available users ─────────────────────────────────────────────────────

    public function getAvailableUsers(int $currentUserId, string $currentRole): array
    {
        try {
            $targetRole = ($currentRole==='entreprise') ? 'expert' : 'entreprise';
            $st = config::getConnexion()->prepare(
                "SELECT * FROM user WHERE role=:role AND id_user!=:id AND statut_compte='actif' ORDER BY nom,prenom,nom_entreprise");
            $st->execute(['role'=>$targetRole,'id'=>$currentUserId]);
            $rows = $st->fetchAll();
            foreach ($rows as &$row) {
                $row['display_name'] = $this->getDisplayName($row);
                $row['initials']     = $this->getInitials($row);
            }
            return $rows;
        } catch (Exception $e) { return []; }
    }

    // ─── Admin ───────────────────────────────────────────────────────────────

    public function getStats(): array
    {
        try {
            $db = config::getConnexion();
            return [
                'total_messages'      => (int)$db->query('SELECT COUNT(*) FROM message WHERE is_deleted=0')->fetchColumn(),
                'total_conversations' => (int)$db->query('SELECT COUNT(*) FROM conversation')->fetchColumn(),
                'messages_today'      => (int)$db->query("SELECT COUNT(*) FROM message WHERE DATE(created_at)=CURDATE() AND is_deleted=0")->fetchColumn(),
                'active_users'        => (int)$db->query("SELECT COUNT(DISTINCT id_sender) FROM message WHERE created_at>=DATE_SUB(NOW(),INTERVAL 7 DAY)")->fetchColumn(),
            ];
        } catch (Exception $e) { return ['total_messages'=>0,'total_conversations'=>0,'messages_today'=>0,'active_users'=>0]; }
    }

    public function getAllConversations(): array
    {
        try {
            $db   = config::getConnexion();
            $sql  = "SELECT c.*,
                         m.content AS last_content, m.type AS last_type, m.created_at AS last_at,
                         (SELECT COUNT(*) FROM message WHERE id_conversation=c.id_conversation AND is_deleted=0) AS msg_count
                     FROM conversation c
                     LEFT JOIN message m ON m.id_message=(
                         SELECT id_message FROM message WHERE id_conversation=c.id_conversation AND is_deleted=0
                         ORDER BY created_at DESC LIMIT 1)
                     ORDER BY COALESCE(m.created_at,c.created_at) DESC";
            $rows = $db->query($sql)->fetchAll();
            foreach ($rows as &$row) {
                $u1=$this->getUserById($row['id_user1']); $u2=$this->getUserById($row['id_user2']);
                $row['name_user1']=$u1?$this->getDisplayName($u1):'Inconnu';
                $row['name_user2']=$u2?$this->getDisplayName($u2):'Inconnu';
                $row['role_user1']=$u1['role']??''; $row['role_user2']=$u2['role']??'';
            }
            return $rows;
        } catch (Exception $e) { return []; }
    }

    // ─── File upload ─────────────────────────────────────────────────────────

    public function handleFileUpload(array $file, string $uploadDir): ?array
    {
        $allowed = ['jpg','jpeg','png','gif','pdf','doc','docx','xls','xlsx','txt','zip','webm','ogg','mp3','wav'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext,$allowed,true)) return null;
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $uniqueName = time().'_'.preg_replace('/[^a-zA-Z0-9._-]/','_',$file['name']);
        if (!move_uploaded_file($file['tmp_name'],$uploadDir.$uniqueName)) return null;
        return ['file_path'=>'uploads/messages/'.$uniqueName,'file_name'=>$file['name'],'file_size'=>$file['size']];
    }
}
