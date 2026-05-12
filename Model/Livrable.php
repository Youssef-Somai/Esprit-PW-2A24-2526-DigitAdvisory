<?php
require_once __DIR__ . '/../config/database.php';

class Livrable {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function findByMission(int $mission_id): array {
        $stmt = $this->pdo->prepare("SELECT * FROM livrables WHERE mission_id = :mid ORDER BY date_remise ASC");
        $stmt->execute([':mid' => $mission_id]);
        return $stmt->fetchAll();
    }

    public function findAll(): array {
        $stmt = $this->pdo->query("SELECT l.*, m.titre AS mission_titre FROM livrables l JOIN missions m ON l.mission_id = m.id ORDER BY l.created_at DESC");
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false {
        $stmt = $this->pdo->prepare("SELECT * FROM livrables WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function getAllMissions(): array {
        $stmt = $this->pdo->query("SELECT id, titre FROM missions ORDER BY titre");
        return $stmt->fetchAll();
    }

    public function create(int $mission_id, string $nom_fichier, string $date_remise, string $etat): bool {
        $stmt = $this->pdo->prepare("INSERT INTO livrables (mission_id, nom_fichier, date_remise, etat) VALUES (:mission_id, :nom_fichier, :date_remise, :etat)");
        return $stmt->execute([':mission_id'=>$mission_id,':nom_fichier'=>$nom_fichier,':date_remise'=>$date_remise,':etat'=>$etat]);
    }

    public function update(int $id, string $nom_fichier, string $date_remise, string $etat): bool {
        $stmt = $this->pdo->prepare("UPDATE livrables SET nom_fichier=:nom_fichier, date_remise=:date_remise, etat=:etat WHERE id=:id");
        return $stmt->execute([':nom_fichier'=>$nom_fichier,':date_remise'=>$date_remise,':etat'=>$etat,':id'=>$id]);
    }

    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM livrables WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
