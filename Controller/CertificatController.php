<?php
/**
 * Controller : CertificatController
 * Logique métier + liaison Model/View pour le module Certificat.
 * CRUD complet via PDO.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Certificat.php';

class CertificatController
{
    // ─── READ : listCertificats() ───
    public function listCertificats(): array
    {
        $sql = "SELECT * FROM certificat ORDER BY id DESC";
        $db  = config::getConnexion();
        try {
            $stmt   = $db->query($sql);
            $result = [];
            while ($row = $stmt->fetch()) {
                $result[] = new Certificat(
                    $row['id'],
                    $row['norme'],
                    $row['titre'],
                    $row['description'],
                    $row['organisme'],
                    $row['date_ajout']
                );
            }
            return $result;
        } catch (Exception $e) {
            die('Erreur listCertificats: ' . $e->getMessage());
        }
    }

    // ─── READ ONE : getCertificat($id) ───
    public function getCertificat(int $id): ?Certificat
    {
        $sql = "SELECT * FROM certificat WHERE id = :id";
        $db  = config::getConnexion();
        try {
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            if ($row) {
                return new Certificat(
                    $row['id'],
                    $row['norme'],
                    $row['titre'],
                    $row['description'],
                    $row['organisme'],
                    $row['date_ajout']
                );
            }
            return null;
        } catch (Exception $e) {
            die('Erreur getCertificat: ' . $e->getMessage());
        }
    }

    // ─── CREATE : addCertificat($certificat) ───
    public function addCertificat(Certificat $certificat): void
    {
        $sql = "INSERT INTO certificat (norme, titre, description, organisme)
                VALUES (:norme, :titre, :description, :organisme)";
        $db  = config::getConnexion();
        try {
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':norme',       $certificat->getNorme());
            $stmt->bindValue(':titre',       $certificat->getTitre());
            $stmt->bindValue(':description', $certificat->getDescription());
            $stmt->bindValue(':organisme',   $certificat->getOrganisme());
            $stmt->execute();
        } catch (Exception $e) {
            die('Erreur addCertificat: ' . $e->getMessage());
        }
    }

    // ─── UPDATE : updateCertificat($certificat) ───
    public function updateCertificat(Certificat $certificat): void
    {
        $sql = "UPDATE certificat
                SET norme       = :norme,
                    titre       = :titre,
                    description = :description,
                    organisme   = :organisme
                WHERE id = :id";
        $db  = config::getConnexion();
        try {
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':id',          $certificat->getId(), PDO::PARAM_INT);
            $stmt->bindValue(':norme',       $certificat->getNorme());
            $stmt->bindValue(':titre',       $certificat->getTitre());
            $stmt->bindValue(':description', $certificat->getDescription());
            $stmt->bindValue(':organisme',   $certificat->getOrganisme());
            $stmt->execute();
        } catch (Exception $e) {
            die('Erreur updateCertificat: ' . $e->getMessage());
        }
    }

    // ─── DELETE : deleteCertificat($id) ───
    public function deleteCertificat(int $id): void
    {
        $sql = "DELETE FROM certificat WHERE id = :id";
        $db  = config::getConnexion();
        try {
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            die('Erreur deleteCertificat: ' . $e->getMessage());
        }
    }

    // ─── showObject (Display helper) ───
    public function showObject(Certificat $certificat): string
    {
        return $certificat->show();
    }
}
?>
