<?php
/**
 * Controller · PortfolioController
 * Digit Advisory — Portfolio Module
 *
 * CRUD complet sur la table `portfolio`.
 * Utilise également la table `element_portfolio` via JOIN pour
 * récupérer les compétences, expériences et certifications.
 *
 * Endpoint JSON : Controller/PortfolioController.php?action=...
 * Actions : list | get | create | update | delete
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Portfolio.php';
require_once __DIR__ . '/../Model/ElementPortfolio.php';

// Le header JSON ne s'envoie que si le fichier est appelé directement (API)
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    header('Content-Type: application/json; charset=utf-8');
}

class PortfolioController
{
    private PDO $db;

    public function __construct()
    {
        try {
            $this->db = config::getConnexion();
        } catch (Exception $e) {
            $this->sendError('Erreur connexion base de données.', 500);
        }
    }

    /* ─────────────────────────────────────────────────────────
       ROUTER
    ───────────────────────────────────────────────────────── */
    public function handleRequest(): void
    {
        $action = $_GET['action'] ?? '';
        switch ($action) {
            case 'list':   $this->listPortfolios(); break;
            case 'get':    $this->getPortfolio();   break;
            case 'create': $this->create();         break;
            case 'update': $this->update();         break;
            case 'delete': $this->delete();         break;
            default:       $this->sendError('Action non reconnue.');
        }
    }

    /* ─────────────────────────────────────────────────────────
       LIST — récupère tous les portfolios d'un utilisateur
       + éléments liés via LEFT JOIN sur element_portfolio
    ───────────────────────────────────────────────────────── */
    private function listPortfolios(): void
    {
        $userId = (int)($_GET['user_id'] ?? 1);

        // Récupération des portfolios
        $stmt = $this->db->prepare('SELECT * FROM portfolio WHERE user_id = :uid ORDER BY created_at DESC');
        $stmt->execute([':uid' => $userId]);
        $rows = $stmt->fetchAll();

        // Pour chaque portfolio, on récupère ses éléments (JOIN sur element_portfolio)
        $result = [];
        foreach ($rows as $row) {
            $result[] = $this->attachElements($row);
        }

        echo json_encode(['success' => true, 'portfolios' => $result]);
    }

    /* ─────────────────────────────────────────────────────────
       GET — récupère un portfolio par ID (avec ses éléments)
    ───────────────────────────────────────────────────────── */
    private function getPortfolio(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) $this->sendError('ID manquant.');

        $row = $this->findById($id);
        if (!$row) $this->sendError('Portfolio introuvable.', 404);

        echo json_encode(['success' => true, 'portfolio' => $this->attachElements($row)]);
    }

    /* ─────────────────────────────────────────────────────────
       CREATE — INSERT dans portfolio + INSERT des éléments
    ───────────────────────────────────────────────────────── */
    private function create(): void
    {
        $data = $this->getJsonInput();
        $this->validateRequired($data);

        try {
            $this->db->beginTransaction();

            // 1) INSERT dans portfolio
            $sql = 'INSERT INTO portfolio
                        (user_id, full_name, professional_title, experience_level,
                         availability, preferred_industry, location, bio)
                    VALUES
                        (:user_id, :full_name, :professional_title, :experience_level,
                         :availability, :preferred_industry, :location, :bio)';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id'            => $data['user_id']            ?? 1,
                ':full_name'          => $data['full_name'],
                ':professional_title' => $data['professional_title'],
                ':experience_level'   => $data['experience_level']   ?? 'junior',
                ':availability'       => $data['availability']       ?? 'immediate',
                ':preferred_industry' => $data['preferred_industry'] ?? null,
                ':location'           => $data['location']           ?? null,
                ':bio'                => $data['bio']                ?? null,
            ]);
            $portfolioId = (int)$this->db->lastInsertId();

            // 2) INSERT des éléments dans element_portfolio
            $this->insertElements($portfolioId, $data);

            $this->db->commit();

            $portfolio = $this->attachElements($this->findById($portfolioId));
            echo json_encode(['success' => true, 'message' => 'Portfolio créé avec succès.', 'portfolio' => $portfolio]);

        } catch (Exception $e) {
            $this->db->rollBack();
            $this->sendError('Erreur lors de la création : ' . $e->getMessage());
        }
    }

    /* ─────────────────────────────────────────────────────────
       UPDATE — UPDATE portfolio + suppression/réinsertion éléments
    ───────────────────────────────────────────────────────── */
    private function update(): void
    {
        $data = $this->getJsonInput();
        $id   = (int)($data['id_portfolio'] ?? 0);
        if (!$id) $this->sendError('ID de portfolio manquant.');
        $this->validateRequired($data);

        try {
            $this->db->beginTransaction();

            // 1) UPDATE portfolio
            $sql = 'UPDATE portfolio SET
                        full_name          = :full_name,
                        professional_title = :professional_title,
                        experience_level   = :experience_level,
                        availability       = :availability,
                        preferred_industry = :preferred_industry,
                        location           = :location,
                        bio                = :bio
                    WHERE id_portfolio = :id';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':full_name'          => $data['full_name'],
                ':professional_title' => $data['professional_title'],
                ':experience_level'   => $data['experience_level']   ?? 'junior',
                ':availability'       => $data['availability']       ?? 'immediate',
                ':preferred_industry' => $data['preferred_industry'] ?? null,
                ':location'           => $data['location']           ?? null,
                ':bio'                => $data['bio']                ?? null,
                ':id'                 => $id,
            ]);

            // 2) Suppression des anciens éléments, puis réinsertion
            $del = $this->db->prepare('DELETE FROM element_portfolio WHERE id_portfolio = :id');
            $del->execute([':id' => $id]);
            $this->insertElements($id, $data);

            $this->db->commit();

            $portfolio = $this->attachElements($this->findById($id));
            echo json_encode(['success' => true, 'message' => 'Portfolio mis à jour.', 'portfolio' => $portfolio]);

        } catch (Exception $e) {
            $this->db->rollBack();
            $this->sendError('Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

    /* ─────────────────────────────────────────────────────────
       DELETE — supprime le portfolio (le CASCADE supprime les éléments)
    ───────────────────────────────────────────────────────── */
    private function delete(): void
    {
        $data = $this->getJsonInput();
        $id   = (int)($data['id_portfolio'] ?? 0);
        if (!$id) $this->sendError('ID manquant.');

        try {
            $stmt = $this->db->prepare('DELETE FROM portfolio WHERE id_portfolio = :id');
            $stmt->execute([':id' => $id]);
            echo json_encode(['success' => true, 'message' => 'Portfolio supprimé avec succès.']);
        } catch (Exception $e) {
            $this->sendError('Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /* ─────────────────────────────────────────────────────────
       HELPERS
    ───────────────────────────────────────────────────────── */

    /**
     * Récupère un portfolio par son ID (depuis la table portfolio uniquement)
     */
    private function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM portfolio WHERE id_portfolio = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Joint les éléments de element_portfolio à un portfolio
     * et les classe par type (skills, experiences, certifications)
     */
    private function attachElements(array $portfolio): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM element_portfolio WHERE id_portfolio = :id ORDER BY type_element, id_element'
        );
        $stmt->execute([':id' => $portfolio['id_portfolio']]);
        $elements = $stmt->fetchAll();

        $portfolio['skills']         = [];
        $portfolio['experiences']    = [];
        $portfolio['certifications'] = [];

        foreach ($elements as $el) {
            switch ($el['type_element']) {
                case 'skill':
                    $portfolio['skills'][] = [
                        'id_element'  => $el['id_element'],
                        'skill_name'  => $el['titre'],
                        'niveau'      => $el['niveau'],
                    ];
                    break;
                case 'experience':
                    $portfolio['experiences'][] = [
                        'id_element'  => $el['id_element'],
                        'job_title'   => $el['titre'],
                        'company'     => $el['description'],
                        'start_date'  => $el['date_debut'],
                        'end_date'    => $el['date_fin'],
                    ];
                    break;
                case 'certification':
                    $portfolio['certifications'][] = [
                        'id_element'  => $el['id_element'],
                        'cert_name'   => $el['titre'],
                        'issuer'      => $el['description'],
                    ];
                    break;
            }
        }

        // Compteurs pour le backoffice
        $portfolio['skills_count'] = count($portfolio['skills']);
        $portfolio['exp_count']    = count($portfolio['experiences']);
        $portfolio['certs_count']  = count($portfolio['certifications']);

        return $portfolio;
    }

    /**
     * Insère les éléments (skills, expériences, certifications) dans element_portfolio
     */
    private function insertElements(int $portfolioId, array $data): void
    {
        $sql = 'INSERT INTO element_portfolio
                    (id_portfolio, type_element, titre, description, date_debut, date_fin, niveau)
                VALUES
                    (:id_portfolio, :type_element, :titre, :description, :date_debut, :date_fin, :niveau)';
        $stmt = $this->db->prepare($sql);

        // Compétences (skills)
        foreach ($data['skills'] ?? [] as $skill) {
            $name = trim($skill['skill_name'] ?? $skill['titre'] ?? '');
            if ($name === '') continue;
            $stmt->execute([
                ':id_portfolio' => $portfolioId,
                ':type_element' => 'skill',
                ':titre'        => $name,
                ':description'  => null,
                ':date_debut'   => null,
                ':date_fin'     => null,
                ':niveau'       => $skill['niveau'] ?? $skill['skill_level'] ?? 'intermediate',
            ]);
        }

        // Expériences
        foreach ($data['experiences'] ?? [] as $exp) {
            $titre = trim($exp['job_title'] ?? '');
            if ($titre === '') continue;
            $stmt->execute([
                ':id_portfolio' => $portfolioId,
                ':type_element' => 'experience',
                ':titre'        => $titre,
                ':description'  => $exp['company']    ?? null,
                ':date_debut'   => $exp['start_date'] ?? null,
                ':date_fin'     => $exp['end_date']   ?? null,
                ':niveau'       => null,
            ]);
        }

        // Certifications
        foreach ($data['certifications'] ?? [] as $cert) {
            $titre = trim($cert['cert_name'] ?? '');
            if ($titre === '') continue;
            $stmt->execute([
                ':id_portfolio' => $portfolioId,
                ':type_element' => 'certification',
                ':titre'        => $titre,
                ':description'  => $cert['issuer'] ?? null,
                ':date_debut'   => null,
                ':date_fin'     => null,
                ':niveau'       => null,
            ]);
        }
    }

    /**
     * Valide les champs obligatoires du portfolio
     */
    private function validateRequired(array $data): void
    {
        if (empty($data['full_name']))
            $this->sendError('Le nom complet est obligatoire.');
        if (empty($data['professional_title']))
            $this->sendError('Le titre professionnel est obligatoire.');
    }

    /**
     * Récupère et sanitise le JSON envoyé en POST body
     */
    private function getJsonInput(): array
    {
        $raw  = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!is_array($data)) $this->sendError('Payload JSON invalide.');

        array_walk_recursive($data, function (&$val) {
            if (is_string($val)) $val = htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
        });

        return $data;
    }

    /**
     * Retourne une erreur JSON et arrête l'exécution
     */
    private function sendError(string $msg, int $code = 400): void
    {
        http_response_code($code);
        echo json_encode(['success' => false, 'error' => $msg]);
        exit;
    }

    /* ─────────────────────────────────────────────────────────
       MÉTHODES PUBLIQUES UTILITAIRES (pour les vues PHP)
    ───────────────────────────────────────────────────────── */

    /**
     * Retourne tous les portfolios (pour le back-office)
     */
    public function getAllPortfolios(): array
    {
        $stmt = $this->db->query('SELECT * FROM portfolio ORDER BY created_at DESC');
        $rows = $stmt->fetchAll();
        return array_map([$this, 'attachElements'], $rows);
    }

    /**
     * Retourne les portfolios d'un utilisateur (pour le front-office)
     */
    public function getByUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM portfolio WHERE user_id = :uid ORDER BY created_at DESC');
        $stmt->execute([':uid' => $userId]);
        $rows = $stmt->fetchAll();
        return array_map([$this, 'attachElements'], $rows);
    }

    /**
     * Suppression simple (depuis le back-office sans JSON)
     */
    public function deleteById(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM portfolio WHERE id_portfolio = :id');
        $stmt->execute([':id' => $id]);
    }
}

// Point d'entrée API — s'exécute UNIQUEMENT quand le fichier est appelé directement
// (via fetch() depuis le JS). Quand il est inclus par une vue PHP, rien ne s'exécute.
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    $controller = new PortfolioController();
    $controller->handleRequest();
}
