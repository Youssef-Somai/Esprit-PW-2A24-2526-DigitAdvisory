<?php
/**
 * Controller · ElementPortfolioController
 * Digit Advisory — Portfolio Module
 *
 * Gestion des éléments liés à un portfolio (table element_portfolio).
 * Ce contrôleur est utilisé en complément de PortfolioController.
 *
 * Utilisation depuis les vues PHP (pas un endpoint JSON autonome).
 * Permet : lister, ajouter, supprimer, modifier des éléments.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/ElementPortfolio.php';

class ElementPortfolioController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = config::getConnexion();
    }

    /* ─────────────────────────────────────────────────────────
       LIST — tous les éléments d'un portfolio
    ───────────────────────────────────────────────────────── */
    public function listElements(int $idPortfolio): array
    {
        $sql  = 'SELECT * FROM element_portfolio WHERE id_portfolio = :id ORDER BY type_element, id_element';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idPortfolio]);
        return $stmt->fetchAll();
    }

    /* ─────────────────────────────────────────────────────────
       LIST PAR TYPE — skills / experiences / certifications
    ───────────────────────────────────────────────────────── */
    public function listByType(int $idPortfolio, string $type): array
    {
        $sql  = 'SELECT * FROM element_portfolio WHERE id_portfolio = :id AND type_element = :type ORDER BY id_element';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idPortfolio, ':type' => $type]);
        return $stmt->fetchAll();
    }

    /* ─────────────────────────────────────────────────────────
       LIST ALL (JOIN portfolio) — pour le back-office
    ───────────────────────────────────────────────────────── */
    public function listAllElements(): array
    {
        $sql = 'SELECT e.*, p.full_name, p.professional_title
                FROM element_portfolio e
                JOIN portfolio p ON e.id_portfolio = p.id_portfolio
                ORDER BY e.type_element, e.id_element';
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /* ─────────────────────────────────────────────────────────
       ADD — ajoute un élément via objet ElementPortfolio
    ───────────────────────────────────────────────────────── */
    public function addElement(ElementPortfolio $element): int
    {
        $sql = 'INSERT INTO element_portfolio
                    (id_portfolio, type_element, titre, description, date_debut, date_fin, niveau)
                VALUES
                    (:id_portfolio, :type_element, :titre, :description, :date_debut, :date_fin, :niveau)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_portfolio' => $element->getIdPortfolio(),
            ':type_element' => $element->getTypeElement(),
            ':titre'        => $element->getTitre(),
            ':description'  => $element->getDescription(),
            ':date_debut'   => $element->getDateDebut(),
            ':date_fin'     => $element->getDateFin(),
            ':niveau'       => $element->getNiveau(),
        ]);
        return (int)$this->db->lastInsertId();
    }

    /* ─────────────────────────────────────────────────────────
       UPDATE — modifie un élément existant
    ───────────────────────────────────────────────────────── */
    public function updateElement(ElementPortfolio $element, int $id): bool
    {
        $sql = 'UPDATE element_portfolio SET
                    type_element = :type_element,
                    titre        = :titre,
                    description  = :description,
                    date_debut   = :date_debut,
                    date_fin     = :date_fin,
                    niveau       = :niveau
                WHERE id_element = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':type_element' => $element->getTypeElement(),
            ':titre'        => $element->getTitre(),
            ':description'  => $element->getDescription(),
            ':date_debut'   => $element->getDateDebut(),
            ':date_fin'     => $element->getDateFin(),
            ':niveau'       => $element->getNiveau(),
            ':id'           => $id,
        ]);
    }

    /* ─────────────────────────────────────────────────────────
       DELETE — supprime un seul élément par son ID
    ───────────────────────────────────────────────────────── */
    public function deleteElement(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM element_portfolio WHERE id_element = :id');
        return $stmt->execute([':id' => $id]);
    }

    /* ─────────────────────────────────────────────────────────
       DELETE ALL — supprime tous les éléments d'un portfolio
    ───────────────────────────────────────────────────────── */
    public function deleteAllByPortfolio(int $idPortfolio): bool
    {
        $stmt = $this->db->prepare('DELETE FROM element_portfolio WHERE id_portfolio = :id');
        return $stmt->execute([':id' => $idPortfolio]);
    }

    /* ─────────────────────────────────────────────────────────
       SHOW — récupère un élément par son ID
    ───────────────────────────────────────────────────────── */
    public function showElement(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM element_portfolio WHERE id_element = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /* ─────────────────────────────────────────────────────────
       SEARCH — recherche par titre dans les éléments
    ───────────────────────────────────────────────────────── */
    public function searchElements(int $idPortfolio, string $keyword, string $type = ''): array
    {
        $sql = 'SELECT * FROM element_portfolio WHERE id_portfolio = :id AND titre LIKE :kw';
        $params = [':id' => $idPortfolio, ':kw' => '%' . $keyword . '%'];
        if ($type !== '') {
            $sql   .= ' AND type_element = :type';
            $params[':type'] = $type;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /* ─────────────────────────────────────────────────────────
       STATS — compte les éléments par type pour un portfolio
    ───────────────────────────────────────────────────────── */
    public function countByType(int $idPortfolio): array
    {
        $sql  = 'SELECT type_element, COUNT(*) as total
                 FROM element_portfolio
                 WHERE id_portfolio = :id
                 GROUP BY type_element';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idPortfolio]);

        $result = ['skill' => 0, 'experience' => 0, 'certification' => 0];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['type_element']] = (int)$row['total'];
        }
        return $result;
    }
}
?>
