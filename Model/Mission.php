<?php
require_once __DIR__ . '/../config/database.php';

class Mission {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function findAll(): array {
        $stmt = $this->pdo->query("SELECT * FROM missions ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false {
        $stmt = $this->pdo->prepare("SELECT * FROM missions WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function create(string $titre, string $date_debut, string $statut): bool {
        $stmt = $this->pdo->prepare(
            "INSERT INTO missions (titre, date_debut, statut) VALUES (:titre, :date_debut, :statut)"
        );
        return $stmt->execute([':titre'=>$titre,':date_debut'=>$date_debut,':statut'=>$statut]);
    }

    public function update(int $id, string $titre, string $date_debut, string $statut): bool {
        $stmt = $this->pdo->prepare(
            "UPDATE missions SET titre=:titre, date_debut=:date_debut, statut=:statut WHERE id=:id"
        );
        return $stmt->execute([':titre'=>$titre,':date_debut'=>$date_debut,':statut'=>$statut,':id'=>$id]);
    }

    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM missions WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function countByStatut(): array {
        $stmt = $this->pdo->query("SELECT statut, COUNT(*) as total FROM missions GROUP BY statut");
        return $stmt->fetchAll();
    }
}
