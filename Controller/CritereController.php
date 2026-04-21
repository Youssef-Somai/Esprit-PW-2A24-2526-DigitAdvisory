<?php
/**
 * Controller : CritereController
 * Logique métier + liaison Model/View pour le module Critère (Indépendant).
 * CRUD complet via PDO avec les nouveaux champs avancés (est_obligatoire, difficulte, etc.).
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Critere.php';

class CritereController
{
    // ─── READ : listCriteres() ───
    public function listCriteres(): array
    {
        $sql = "SELECT * FROM critere ORDER BY id DESC";
        $db  = config::getConnexion();
        try {
            $stmt   = $db->query($sql);
            $result = [];
            while ($row = $stmt->fetch()) {
                $critere = new Critere(
                    $row['id'],
                    $row['nom'],
                    $row['categorie'],
                    $row['description'],
                    $row['moyen_preuve'],
                    (int) $row['est_obligatoire'],
                    $row['difficulte'],
                    $row['document_template']
                );
                $result[] = $critere;
            }
            return $result;
        } catch (Exception $e) {
            die('Erreur listCriteres: ' . $e->getMessage());
        }
    }

    // ─── READ ONE : getCritere($id) ───
    public function getCritere(int $id): ?Critere
    {
        $sql = "SELECT * FROM critere WHERE id = :id";
        $db  = config::getConnexion();
        try {
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            if ($row) {
                return new Critere(
                    $row['id'],
                    $row['nom'],
                    $row['categorie'],
                    $row['description'],
                    $row['moyen_preuve'],
                    (int) $row['est_obligatoire'],
                    $row['difficulte'],
                    $row['document_template']
                );
            }
            return null;
        } catch (Exception $e) {
            die('Erreur getCritere: ' . $e->getMessage());
        }
    }

    // ─── CREATE : addCritere($critere) ───
    public function addCritere(Critere $critere): void
    {
        $sql = "INSERT INTO critere (nom, categorie, description, moyen_preuve, est_obligatoire, difficulte, document_template)
                VALUES (:nom, :categorie, :description, :moyen_preuve, :est_obligatoire, :difficulte, :document_template)";
        $db  = config::getConnexion();
        try {
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':nom',               $critere->getNom());
            $stmt->bindValue(':categorie',         $critere->getCategorie());
            $stmt->bindValue(':description',       $critere->getDescription());
            $stmt->bindValue(':moyen_preuve',      $critere->getMoyenPreuve());
            $stmt->bindValue(':est_obligatoire',   $critere->getEstObligatoire(), PDO::PARAM_INT);
            $stmt->bindValue(':difficulte',        $critere->getDifficulte());
            $stmt->bindValue(':document_template', $critere->getDocumentTemplate());
            $stmt->execute();
        } catch (Exception $e) {
            die('Erreur addCritere: ' . $e->getMessage());
        }
    }

    // ─── UPDATE : updateCritere($critere) ───
    public function updateCritere(Critere $critere): void
    {
        $sql = "UPDATE critere
                SET nom               = :nom,
                    categorie         = :categorie,
                    description       = :description,
                    moyen_preuve      = :moyen_preuve,
                    est_obligatoire   = :est_obligatoire,
                    difficulte        = :difficulte,
                    document_template = :document_template
                WHERE id = :id";
        $db  = config::getConnexion();
        try {
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':id',                $critere->getId(), PDO::PARAM_INT);
            $stmt->bindValue(':nom',               $critere->getNom());
            $stmt->bindValue(':categorie',         $critere->getCategorie());
            $stmt->bindValue(':description',       $critere->getDescription());
            $stmt->bindValue(':moyen_preuve',      $critere->getMoyenPreuve());
            $stmt->bindValue(':est_obligatoire',   $critere->getEstObligatoire(), PDO::PARAM_INT);
            $stmt->bindValue(':difficulte',        $critere->getDifficulte());
            $stmt->bindValue(':document_template', $critere->getDocumentTemplate());
            $stmt->execute();
        } catch (Exception $e) {
            die('Erreur updateCritere: ' . $e->getMessage());
        }
    }

    // ─── DELETE : deleteCritere($id) ───
    public function deleteCritere(int $id): void
    {
        $sql = "DELETE FROM critere WHERE id = :id";
        $db  = config::getConnexion();
        try {
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            die('Erreur deleteCritere: ' . $e->getMessage());
        }
    }
}
?>
