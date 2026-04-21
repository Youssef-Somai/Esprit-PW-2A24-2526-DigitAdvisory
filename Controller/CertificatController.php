<?php
/**
 * Controller : CertificatController
 * Logique métier + liaison Model/View pour le module Certificat.
 * CRUD complet via PDO, incluant les champs V2 (version, statut, validité).
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Certificat.php';
require_once __DIR__ . '/../Model/Critere.php';

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
                    $row['version'],
                    $row['statut'],
                    (int) $row['duree_validite'],
                    $row['description'],
                    $row['organisme'],
                    $row['logo_url'],
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
                    $row['version'],
                    $row['statut'],
                    (int) $row['duree_validite'],
                    $row['description'],
                    $row['organisme'],
                    $row['logo_url'],
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
        $sql = "INSERT INTO certificat (norme, titre, version, statut, duree_validite, description, organisme, logo_url)
                VALUES (:norme, :titre, :version, :statut, :duree_validite, :description, :organisme, :logo_url)";
        $db  = config::getConnexion();
        try {
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':norme',           $certificat->getNorme());
            $stmt->bindValue(':titre',           $certificat->getTitre());
            $stmt->bindValue(':version',         $certificat->getVersion());
            $stmt->bindValue(':statut',          $certificat->getStatut());
            $stmt->bindValue(':duree_validite',  $certificat->getDureeValidite(), PDO::PARAM_INT);
            $stmt->bindValue(':description',     $certificat->getDescription());
            $stmt->bindValue(':organisme',       $certificat->getOrganisme());
            $stmt->bindValue(':logo_url',        $certificat->getLogoUrl());
            $stmt->execute();
        } catch (Exception $e) {
            die('Erreur addCertificat: ' . $e->getMessage());
        }
    }

    // ─── UPDATE : updateCertificat($certificat) ───
    public function updateCertificat(Certificat $certificat): void
    {
        $sql = "UPDATE certificat
                SET norme          = :norme,
                    titre          = :titre,
                    version        = :version,
                    statut         = :statut,
                    duree_validite = :duree_validite,
                    description    = :description,
                    organisme      = :organisme,
                    logo_url       = :logo_url
                WHERE id = :id";
        $db  = config::getConnexion();
        try {
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':id',             $certificat->getId(), PDO::PARAM_INT);
            $stmt->bindValue(':norme',          $certificat->getNorme());
            $stmt->bindValue(':titre',          $certificat->getTitre());
            $stmt->bindValue(':version',        $certificat->getVersion());
            $stmt->bindValue(':statut',         $certificat->getStatut());
            $stmt->bindValue(':duree_validite', $certificat->getDureeValidite(), PDO::PARAM_INT);
            $stmt->bindValue(':description',    $certificat->getDescription());
            $stmt->bindValue(':organisme',      $certificat->getOrganisme());
            $stmt->bindValue(':logo_url',       $certificat->getLogoUrl());
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

    // ─── MANY-TO-MANY : Criteres <-> Certificats ───

    /**
     * Récupère les IDs et le poids des critères liés à une certification donnée.
     * @return array ex: [critere_id => poids]
     */
    public function getCriteresByCertificat(int $certificat_id): array
    {
        $sql = "SELECT critere_id, poids FROM certificat_critere WHERE certificat_id = :certificat_id";
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':certificat_id', $certificat_id, PDO::PARAM_INT);
            $stmt->execute();
            $result = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $result[$row['critere_id']] = (int) $row['poids'];
            }
            return $result;
        } catch (Exception $e) {
            die('Erreur getCriteresByCertificat: ' . $e->getMessage());
        }
    }

    /**
     * Récupère les objets Critères complets liés à une certification, avec le POIDS HYDRATÉ.
     * @return Critere[]
     */
    public function getDetailedCriteresByCertificat(int $certificat_id): array
    {
        $sql = "SELECT c.*, cc.poids as pivot_poids FROM critere c
                INNER JOIN certificat_critere cc ON c.id = cc.critere_id
                WHERE cc.certificat_id = :certificat_id
                ORDER BY c.nom ASC";
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':certificat_id', $certificat_id, PDO::PARAM_INT);
            $stmt->execute();
            $result = [];
            while ($row = $stmt->fetch()) {
                $crit = new Critere(
                    $row['id'],
                    $row['nom'],
                    $row['categorie'],
                    $row['description'],
                    $row['moyen_preuve'],
                    (int) $row['est_obligatoire'],
                    $row['difficulte'],
                    $row['document_template']
                );
                // On injecte le poids spécifique trouvé dans la table de jointure
                $crit->poids_specifique = (int) $row['pivot_poids'];
                $result[] = $crit;
            }
            return $result;
        } catch (Exception $e) {
            die('Erreur getDetailedCriteresByCertificat: ' . $e->getMessage());
        }
    }

    /**
     * Synchronise les critères liés à une certification (supprime anciens, insère nouveaux avec LEUR POIDS).
     * @param int $certificat_id
     * @param array $criteres_avec_poids Format: [ critere_id => poids, ... ]
     */
    public function syncCriteresForCertificat(int $certificat_id, array $criteres_avec_poids): void
    {
        $db = config::getConnexion();
        try {
            // 1. Supprimer les liens existants
            $sqlDelete = "DELETE FROM certificat_critere WHERE certificat_id = :certificat_id";
            $stmtDelete = $db->prepare($sqlDelete);
            $stmtDelete->bindValue(':certificat_id', $certificat_id, PDO::PARAM_INT);
            $stmtDelete->execute();

            // 2. Insérer les nouveaux liens avec leurs poids
            if (!empty($criteres_avec_poids)) {
                $sqlInsert = "INSERT INTO certificat_critere (certificat_id, critere_id, poids) VALUES (:certificat_id, :critere_id, :poids)";
                $stmtInsert = $db->prepare($sqlInsert);
                foreach ($criteres_avec_poids as $critere_id => $poids) {
                    $stmtInsert->bindValue(':certificat_id', $certificat_id, PDO::PARAM_INT);
                    $stmtInsert->bindValue(':critere_id', (int) $critere_id, PDO::PARAM_INT);
                    $stmtInsert->bindValue(':poids', (int) $poids, PDO::PARAM_INT);
                    $stmtInsert->execute();
                }
            }
        } catch (Exception $e) {
            die('Erreur syncCriteresForCertificat: ' . $e->getMessage());
        }
    }
}
?>
