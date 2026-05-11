<?php
/**
 * Controller · ExpertPortfolioController
 * JSON API Controller pour la gestion des Profils CV
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/ExpertPortfolioModel.php';

header('Content-Type: application/json; charset=utf-8');

class ExpertPortfolioController
{
    private $model;

    public function __construct()
    {
        try {
            $db = config::getConnexion();
            $this->model = new ExpertPortfolioModel($db);
        } catch (Exception $e) {
            $this->sendError('Erreur connexion base de données.', 500);
        }
    }

    public function handleRequest()
    {
        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'list':
                $userId = $_GET['user_id'] ?? 1; // Simulation session/user
                $this->list($userId);
                break;
            case 'get':
                $id = $_GET['id'] ?? null;
                if (!$id) $this->sendError('ID manquant');
                $this->get((int)$id);
                break;
            case 'create':
                $this->create();
                break;
            case 'update':
                $this->update();
                break;
            case 'delete':
                $this->delete();
                break;
            default:
                $this->sendError('Action non reconnue');
        }
    }

    private function list(int $userId)
    {
        $portfolios = $this->model->getAllByUser($userId);
        echo json_encode(['success' => true, 'portfolios' => $portfolios]);
    }

    private function get(int $id)
    {
        $pf = $this->model->getById($id);
        if (!$pf) $this->sendError('Portfolio introuvable', 404);
        echo json_encode(['success' => true, 'portfolio' => $pf]);
    }

    private function create()
    {
        $data = $this->getJsonInput();
        if (empty($data['full_name']) || empty($data['professional_title'])) {
            $this->sendError('Nom et titre sont requis');
        }
        
        try {
            $id = $this->model->create($data);
            $newPf = $this->model->getById($id);
            echo json_encode(['success' => true, 'message' => 'Portfolio créé avec succès', 'portfolio' => $newPf]);
        } catch (Exception $e) {
            $this->sendError('Erreur de création du portfolio: ' . $e->getMessage());
        }
    }

    private function update()
    {
        $data = $this->getJsonInput();
        $id = $data['id_portfolio'] ?? null;
        if (!$id) $this->sendError('ID de portfolio manquant pour la mise à jour');
        
        if (empty($data['full_name']) || empty($data['professional_title'])) {
            $this->sendError('Nom et titre sont requis');
        }

        try {
            $this->model->update((int)$id, $data);
            $upPf = $this->model->getById((int)$id);
            echo json_encode(['success' => true, 'message' => 'Portfolio mis à jour', 'portfolio' => $upPf]);
        } catch (Exception $e) {
            $this->sendError('Erreur de mise à jour: ' . $e->getMessage());
        }
    }

    private function delete()
    {
        $data = $this->getJsonInput();
        $id = $data['id_portfolio'] ?? null;
        if (!$id) $this->sendError('ID manquant');

        try {
            $this->model->delete((int)$id);
            echo json_encode(['success' => true, 'message' => 'Portfolio supprimé avec succès']);
        } catch (Exception $e) {
            $this->sendError('Erreur de suppression: ' . $e->getMessage());
        }
    }

    private function getJsonInput(): array
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        if (!is_array($data)) {
            $this->sendError('Payload JSON invalide');
        }
        
        // Basic sanitization
        array_walk_recursive($data, function (&$val, $key) {
            if (is_string($val)) {
                $val = htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
            }
        });
        
        return $data;
    }

    private function sendError(string $msg, int $code = 400)
    {
        http_response_code($code);
        echo json_encode(['success' => false, 'error' => $msg]);
        exit;
    }
}

$controller = new ExpertPortfolioController();
$controller->handleRequest();
