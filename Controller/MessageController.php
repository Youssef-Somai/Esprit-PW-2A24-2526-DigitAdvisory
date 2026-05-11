<?php

require_once __DIR__ . '/../config.php';

class MessageController
{
    // ─── Helpers ────────────────────────────────────────────────────────────

    public function getUserById(int $id): ?array
    {
        try {
            $db  = config::getConnexion();
            $sql = 'SELECT * FROM user WHERE id_user = :id';
            $st  = $db->prepare($sql);
            $st->execute(['id' => $id]);
            return $st->fetch() ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getDisplayName(array $user): string
    {
        if ($user['role'] === 'entreprise') {
            return $user['nom_entreprise'] ?? $user['email'];
        }
        $full = trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? ''));
        return $full !== '' ? $full : $user['email'];
    }

    public function getInitials(array $user): string
    {
        $name  = $this->getDisplayName($user);
        $parts = preg_split('/\s+/', $name);
        $init  = strtoupper(mb_substr($parts[0], 0, 1));
        if (isset($parts[1])) {
            $init .= strtoupper(mb_substr($parts[1], 0, 1));
        }
        return $init;
    }

    // ─── Conversations ───────────────────────────────────────────────────────

    /** Normalise la paire (min, max) pour éviter les doublons */
    private function normalizePair(int $a, int $b): array
    {
        return [$a < $b ? $a : $b, $a < $b ? $b : $a];
    }

    public function getConversations(int $userId): array
    {
        try {
            $db  = config::getConnexion();
            // Fetch conversations where user participates and hasn't soft-deleted
            $sql = "SELECT c.*,
                        m.content        AS last_content,
                        m.type           AS last_type,
                        m.file_name      AS last_file_name,
                        m.created_at     AS last_at,
                        m.id_sender      AS last_sender,
                        (SELECT COUNT(*) FROM message
                         WHERE id_conversation = c.id_conversation
                           AND id_sender != :uid2 AND is_read = 0 AND is_deleted = 0) AS unread
                    FROM conversation c
                    LEFT JOIN message m ON m.id_message = (
                        SELECT id_message FROM message
                        WHERE id_conversation = c.id_conversation AND is_deleted = 0
                        ORDER BY created_at DESC LIMIT 1
                    )
                    WHERE (c.id_user1 = :uid3 OR c.id_user2 = :uid4)
                      AND (
                          (c.id_user1 = :uid5 AND c.deleted_by1 = 0)
                       OR (c.id_user2 = :uid6 AND c.deleted_by2 = 0)
                      )
                    ORDER BY COALESCE(m.created_at, c.created_at) DESC";
            $st = $db->prepare($sql);
            $st->execute([
                'uid2' => $userId, 'uid3' => $userId, 'uid4' => $userId,
                'uid5' => $userId, 'uid6' => $userId,
            ]);
            $rows = $st->fetchAll();

            foreach ($rows as &$row) {
                $otherId = ($row['id_user1'] == $userId) ? $row['id_user2'] : $row['id_user1'];
                $other   = $this->getUserById($otherId);
                if ($other) {
                    $row['other_name']     = $this->getDisplayName($other);
                    $row['other_initials'] = $this->getInitials($other);
                    $row['other_role']     = $other['role'];
                    $row['other_id']       = $otherId;
                } else {
                    $row['other_name']     = 'Utilisateur inconnu';
                    $row['other_initials'] = '??';
                    $row['other_role']     = '';
                    $row['other_id']       = $otherId;
                }
            }
            return $rows;
        } catch (Exception $e) {
            return [];
        }
    }

    public function getOrCreateConversation(int $userId, int $otherId): ?int
    {
        try {
            $db = config::getConnexion();
            [$u1, $u2] = $this->normalizePair($userId, $otherId);

            // Try to find existing
            $sql = 'SELECT id_conversation FROM conversation WHERE id_user1 = :u1 AND id_user2 = :u2';
            $st  = $db->prepare($sql);
            $st->execute(['u1' => $u1, 'u2' => $u2]);
            $row = $st->fetch();

            if ($row) {
                // Re-activate if the current user had deleted it
                $col = ($userId === $u1) ? 'deleted_by1' : 'deleted_by2';
                $db->prepare("UPDATE conversation SET $col = 0 WHERE id_conversation = :id")
                   ->execute(['id' => $row['id_conversation']]);
                return (int)$row['id_conversation'];
            }

            // Create new
            $st = $db->prepare('INSERT INTO conversation (id_user1, id_user2) VALUES (:u1, :u2)');
            $st->execute(['u1' => $u1, 'u2' => $u2]);
            return (int)$db->lastInsertId();
        } catch (Exception $e) {
            return null;
        }
    }

    public function deleteConversation(int $conversationId, int $userId): bool
    {
        try {
            $db  = config::getConnexion();
            $sql = 'SELECT * FROM conversation WHERE id_conversation = :id';
            $st  = $db->prepare($sql);
            $st->execute(['id' => $conversationId]);
            $conv = $st->fetch();
            if (!$conv) return false;

            $col = ($conv['id_user1'] == $userId) ? 'deleted_by1' : 'deleted_by2';
            $db->prepare("UPDATE conversation SET $col = 1 WHERE id_conversation = :id")
               ->execute(['id' => $conversationId]);

            // Hard delete if both users deleted it
            $st2 = $db->prepare('SELECT deleted_by1, deleted_by2 FROM conversation WHERE id_conversation = :id');
            $st2->execute(['id' => $conversationId]);
            $r = $st2->fetch();
            if ($r && $r['deleted_by1'] && $r['deleted_by2']) {
                $db->prepare('DELETE FROM conversation WHERE id_conversation = :id')
                   ->execute(['id' => $conversationId]);
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function adminDeleteConversation(int $conversationId): bool
    {
        try {
            $db = config::getConnexion();
            $db->prepare('DELETE FROM conversation WHERE id_conversation = :id')
               ->execute(['id' => $conversationId]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // ─── Messages ────────────────────────────────────────────────────────────

    public function getMessages(int $conversationId): array
    {
        try {
            $db  = config::getConnexion();
            $sql = 'SELECT * FROM message WHERE id_conversation = :id ORDER BY created_at ASC';
            $st  = $db->prepare($sql);
            $st->execute(['id' => $conversationId]);
            return $st->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function sendMessage(
        int     $conversationId,
        int     $senderId,
        string  $type     = 'text',
        ?string $content  = null,
        ?string $filePath = null,
        ?string $fileName = null,
        ?int    $fileSize = null
    ): ?int {
        try {
            $db  = config::getConnexion();
            $sql = 'INSERT INTO message
                        (id_conversation, id_sender, type, content, file_path, file_name, file_size)
                    VALUES (:conv, :sender, :type, :content, :fp, :fn, :fs)';
            $st = $db->prepare($sql);
            $st->execute([
                'conv'    => $conversationId,
                'sender'  => $senderId,
                'type'    => $type,
                'content' => $content,
                'fp'      => $filePath,
                'fn'      => $fileName,
                'fs'      => $fileSize,
            ]);
            // Touch conversation updated_at
            $db->prepare('UPDATE conversation SET updated_at = NOW() WHERE id_conversation = :id')
               ->execute(['id' => $conversationId]);
            return (int)$db->lastInsertId();
        } catch (Exception $e) {
            return null;
        }
    }

    public function editMessage(int $messageId, int $senderId, string $newContent): bool
    {
        try {
            $db  = config::getConnexion();
            $sql = 'UPDATE message SET content = :c, is_edited = 1
                    WHERE id_message = :id AND id_sender = :sender AND is_deleted = 0 AND type = "text"';
            $st  = $db->prepare($sql);
            $st->execute(['c' => $newContent, 'id' => $messageId, 'sender' => $senderId]);
            return $st->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    public function deleteMessage(int $messageId, int $senderId): bool
    {
        try {
            $db  = config::getConnexion();
            $sql = 'UPDATE message SET is_deleted = 1 WHERE id_message = :id AND id_sender = :sender';
            $st  = $db->prepare($sql);
            $st->execute(['id' => $messageId, 'sender' => $senderId]);
            return $st->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    public function markRead(int $conversationId, int $userId): bool
    {
        try {
            $db  = config::getConnexion();
            $sql = 'UPDATE message SET is_read = 1
                    WHERE id_conversation = :conv AND id_sender != :uid AND is_read = 0';
            $st  = $db->prepare($sql);
            $st->execute(['conv' => $conversationId, 'uid' => $userId]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // ─── Users for new conversation ──────────────────────────────────────────

    public function getAvailableUsers(int $currentUserId, string $currentRole): array
    {
        try {
            $db          = config::getConnexion();
            $targetRole  = ($currentRole === 'entreprise') ? 'expert' : 'entreprise';
            $sql         = "SELECT * FROM user WHERE role = :role AND id_user != :id AND statut_compte = 'actif' ORDER BY nom, prenom, nom_entreprise";
            $st          = $db->prepare($sql);
            $st->execute(['role' => $targetRole, 'id' => $currentUserId]);
            $rows = $st->fetchAll();
            foreach ($rows as &$row) {
                $row['display_name'] = $this->getDisplayName($row);
                $row['initials']     = $this->getInitials($row);
            }
            return $rows;
        } catch (Exception $e) {
            return [];
        }
    }

    // ─── Admin stats ─────────────────────────────────────────────────────────

    public function getStats(): array
    {
        try {
            $db = config::getConnexion();
            $stats = [];

            $stats['total_messages']       = (int)$db->query('SELECT COUNT(*) FROM message WHERE is_deleted = 0')->fetchColumn();
            $stats['total_conversations']  = (int)$db->query('SELECT COUNT(*) FROM conversation')->fetchColumn();
            $stats['messages_today']       = (int)$db->query("SELECT COUNT(*) FROM message WHERE DATE(created_at) = CURDATE() AND is_deleted = 0")->fetchColumn();
            $stats['active_users']         = (int)$db->query("SELECT COUNT(DISTINCT id_sender) FROM message WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
            return $stats;
        } catch (Exception $e) {
            return ['total_messages' => 0, 'total_conversations' => 0, 'messages_today' => 0, 'active_users' => 0];
        }
    }

    public function getAllConversations(): array
    {
        try {
            $db  = config::getConnexion();
            $sql = "SELECT c.*,
                        m.content    AS last_content,
                        m.type       AS last_type,
                        m.created_at AS last_at,
                        (SELECT COUNT(*) FROM message WHERE id_conversation = c.id_conversation AND is_deleted = 0) AS msg_count
                    FROM conversation c
                    LEFT JOIN message m ON m.id_message = (
                        SELECT id_message FROM message
                        WHERE id_conversation = c.id_conversation AND is_deleted = 0
                        ORDER BY created_at DESC LIMIT 1
                    )
                    ORDER BY COALESCE(m.created_at, c.created_at) DESC";
            $st  = $db->prepare($sql);
            $st->execute();
            $rows = $st->fetchAll();

            foreach ($rows as &$row) {
                $u1 = $this->getUserById($row['id_user1']);
                $u2 = $this->getUserById($row['id_user2']);
                $row['name_user1'] = $u1 ? $this->getDisplayName($u1) : 'Inconnu';
                $row['name_user2'] = $u2 ? $this->getDisplayName($u2) : 'Inconnu';
                $row['role_user1'] = $u1['role'] ?? '';
                $row['role_user2'] = $u2['role'] ?? '';
            }
            return $rows;
        } catch (Exception $e) {
            return [];
        }
    }

    // ─── File upload helper ──────────────────────────────────────────────────

    public function handleFileUpload(array $file, string $uploadDir): ?array
    {
        $allowed = ['jpg','jpeg','png','gif','pdf','doc','docx','xls','xlsx','txt','zip','webm','ogg','mp3','wav'];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) return null;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $safeName  = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
        $uniqueName = time() . '_' . $safeName;
        $destPath   = $uploadDir . $uniqueName;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) return null;

        return [
            'file_path' => 'uploads/messages/' . $uniqueName,
            'file_name' => $file['name'],
            'file_size' => $file['size'],
        ];
    }
}
