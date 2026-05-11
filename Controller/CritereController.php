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

    public function updateCritereTemplate(int $id, ?string $documentTemplate): void
    {
        $sql = "UPDATE critere SET document_template = :document_template WHERE id = :id";
        $db  = config::getConnexion();
        try {
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':document_template', $documentTemplate);
            $stmt->execute();
        } catch (Exception $e) {
            die('Erreur updateCritereTemplate: ' . $e->getMessage());
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

    // ─── HELPER : handleFileUpload() ───
    public function handleFileUpload(): ?string
    {
        if (isset($_FILES['template_file']) && $_FILES['template_file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/templates/';
            $allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'html'];
            $extension = strtolower(pathinfo($_FILES['template_file']['name'], PATHINFO_EXTENSION));

            if (!in_array($extension, $allowedExtensions, true)) {
                return null;
            }

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = basename($_FILES['template_file']['name']);
            // Sécurisation du nom de fichier
            $fileName = preg_replace("/[^a-zA-Z0-9\._-]/", "_", $fileName);
            $uniqueName = time() . '_' . $fileName;
            $targetPath = $uploadDir . $uniqueName;

            if (move_uploaded_file($_FILES['template_file']['tmp_name'], $targetPath)) {
                // On retourne le chemin relatif pour l'accès depuis le Front/Back
                return '../../uploads/templates/' . $uniqueName;
            }
        }
        return null;
    }
}

// ─── ROUTAGE CENTRALISÉ (Si le fichier est appelé directement) ───
if (basename($_SERVER['PHP_SELF']) === 'CritereController.php') {
    $critereController = new CritereController();

    // ─── ACTIONS GET (Ex: Suppression) ───
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && isset($_GET['id'])) {
        if ($_GET['action'] === 'delete_critere') {
            $critereController->deleteCritere((int) $_GET['id']);
            header('Location: ../View/BackOffice/back-certification.php?success=delete_critere&tab=criteres');
            exit;
        }
    }

    // ─── ACTIONS POST (Ex: Ajout, Modification) ───
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        // 1. Ajouter Critère
        if ($_POST['action'] === 'add_critere') {
            $templatePath = $critereController->handleFileUpload();
            
            $critere = new Critere(
                null,
                $_POST['nom'],
                $_POST['categorie'] ?? 'Général',
                $_POST['description'],
                $_POST['moyen_preuve'] ?? null,
                isset($_POST['est_obligatoire']) ? 1 : 0,
                $_POST['difficulte'] ?? 'Moyen',
                $templatePath ?? $_POST['document_template'] ?? null
            );
            $critereController->addCritere($critere);
            header('Location: ../View/BackOffice/back-certification.php?success=add_critere&tab=criteres');
            exit;
        }

        // 2. Modifier Critère
        if ($_POST['action'] === 'update_critere') {
            $templatePath = $critereController->handleFileUpload();
            
            $critere = new Critere(
                (int) $_POST['id'],
                $_POST['nom'],
                $_POST['categorie'],
                $_POST['description'],
                $_POST['moyen_preuve'],
                isset($_POST['est_obligatoire']) ? 1 : 0,
                $_POST['difficulte'],
                $templatePath ?? $_POST['document_template'] ?? null
            );
            $critereController->updateCritere($critere);
            header('Location: ../View/BackOffice/back-certification.php?success=update_critere&tab=criteres');
            exit;
        }
    }
    header('Location: ../View/BackOffice/back-certification.php');
    exit;
}
?>
