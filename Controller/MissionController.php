<?php
require_once __DIR__ . '/../Model/Mission.php';
require_once __DIR__ . '/../Model/Livrable.php';

class MissionController {
    private Mission  $missionModel;
    private Livrable $livrableModel;

    public function __construct() {
        $this->missionModel  = new Mission();
        $this->livrableModel = new Livrable();
    }

    // ─── FRONT OFFICE ─────────────────────────────────────────────
    public function frontList(): void {
        $missions = $this->missionModel->findAll();
        require __DIR__ . '/../View/mission/front_list.php';
    }

    public function frontDetail(int $id): void {
        $mission = $this->missionModel->findById($id);
        if (!$mission) { header('Location: index.php?action=front_list'); exit; }
        $livrables = $this->livrableModel->findByMission($id);
        require __DIR__ . '/../View/mission/front_detail.php';
    }

    // ─── BACK OFFICE — MISSIONS ───────────────────────────────────
    public function backList(): void {
        $missions  = $this->missionModel->findAll();
        $livrables = $this->livrableModel->findAll();
        require __DIR__ . '/../View/mission/back_list.php';
    }

    public function backCreate(): void {
        $errors  = [];
        $mission = ['titre'=>'','date_debut'=>'','statut'=>''];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titre      = trim($_POST['titre']      ?? '');
            $date_debut = trim($_POST['date_debut'] ?? '');
            $statut     = trim($_POST['statut']     ?? '');

            // PHP validation — HTML5 NOT used per constraints
            if (strlen($titre) < 3)
                $errors[] = "Le titre doit contenir au moins 3 caractères.";
            if (strlen($titre) > 150)
                $errors[] = "Le titre ne doit pas dépasser 150 caractères.";
            if (empty($date_debut) || !strtotime($date_debut))
                $errors[] = "La date de début est invalide.";
            $allowed = ['En cours','Terminée','Suspendue'];
            if (!in_array($statut, $allowed, true))
                $errors[] = "Le statut sélectionné est invalide.";

            if (empty($errors)) {
                $this->missionModel->create($titre, $date_debut, $statut);
                header('Location: index.php?action=back_list&success=created'); exit;
            }
            $mission = compact('titre','date_debut','statut');
        }
        require __DIR__ . '/../View/mission/back_form.php';
    }

    public function backEdit(int $id): void {
        $mission = $this->missionModel->findById($id);
        if (!$mission) { header('Location: index.php?action=back_list'); exit; }
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titre      = trim($_POST['titre']      ?? '');
            $date_debut = trim($_POST['date_debut'] ?? '');
            $statut     = trim($_POST['statut']     ?? '');

            if (strlen($titre) < 3)
                $errors[] = "Le titre doit contenir au moins 3 caractères.";
            if (strlen($titre) > 150)
                $errors[] = "Le titre ne doit pas dépasser 150 caractères.";
            if (empty($date_debut) || !strtotime($date_debut))
                $errors[] = "La date de début est invalide.";
            $allowed = ['En cours','Terminée','Suspendue'];
            if (!in_array($statut, $allowed, true))
                $errors[] = "Le statut sélectionné est invalide.";

            if (empty($errors)) {
                $this->missionModel->update($id, $titre, $date_debut, $statut);
                header('Location: index.php?action=back_list&success=updated'); exit;
            }
            $mission = array_merge($mission, compact('titre','date_debut','statut'));
        }
        require __DIR__ . '/../View/mission/back_form.php';
    }

    public function backDelete(int $id): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->missionModel->delete($id);
        }
        header('Location: index.php?action=back_list&success=deleted'); exit;
    }

    // ─── BACK OFFICE — LIVRABLES ──────────────────────────────────
    public function backLivrableCreate(int $mission_id): void {
        $mission  = $this->missionModel->findById($mission_id);
        if (!$mission) { header('Location: index.php?action=back_list'); exit; }
        $missions = $this->livrableModel->getAllMissions();
        $errors   = [];
        $livrable = ['mission_id'=>$mission_id,'nom_fichier'=>'','date_remise'=>'','etat'=>''];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom_fichier  = trim($_POST['nom_fichier']  ?? '');
            $date_remise  = trim($_POST['date_remise']  ?? '');
            $etat         = trim($_POST['etat']         ?? '');
            $mid          = (int)($_POST['mission_id']  ?? 0);

            if (strlen($nom_fichier) < 2)
                $errors[] = "Le nom du fichier est trop court (min. 2 caractères).";
            if (!preg_match('/\.[a-zA-Z0-9]{2,5}$/', $nom_fichier))
                $errors[] = "Le nom du fichier doit avoir une extension (ex: rapport.pdf).";
            if (empty($date_remise) || !strtotime($date_remise))
                $errors[] = "La date de remise est invalide.";
            $etats = ['En attente','Validé','Rejeté'];
            if (!in_array($etat, $etats, true))
                $errors[] = "L'état sélectionné est invalide.";
            if ($mid <= 0)
                $errors[] = "Mission associée invalide.";

            if (empty($errors)) {
                $this->livrableModel->create($mid, $nom_fichier, $date_remise, $etat);
                header("Location: index.php?action=back_list&success=livrable_created"); exit;
            }
            $livrable = compact('nom_fichier','date_remise','etat') + ['mission_id'=>$mid];
        }
        require __DIR__ . '/../View/mission/back_livrable_form.php';
    }

    public function backLivrableEdit(int $id): void {
        $livrable = $this->livrableModel->findById($id);
        if (!$livrable) { header('Location: index.php?action=back_list'); exit; }
        $missions = $this->livrableModel->getAllMissions();
        $errors   = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom_fichier = trim($_POST['nom_fichier'] ?? '');
            $date_remise = trim($_POST['date_remise'] ?? '');
            $etat        = trim($_POST['etat']        ?? '');

            if (strlen($nom_fichier) < 2)
                $errors[] = "Le nom du fichier est trop court.";
            if (!preg_match('/\.[a-zA-Z0-9]{2,5}$/', $nom_fichier))
                $errors[] = "Le nom du fichier doit avoir une extension.";
            if (empty($date_remise) || !strtotime($date_remise))
                $errors[] = "La date de remise est invalide.";
            $etats = ['En attente','Validé','Rejeté'];
            if (!in_array($etat, $etats, true))
                $errors[] = "L'état sélectionné est invalide.";

            if (empty($errors)) {
                $this->livrableModel->update($id, $nom_fichier, $date_remise, $etat);
                header('Location: index.php?action=back_list&success=livrable_updated'); exit;
            }
            $livrable = array_merge($livrable, compact('nom_fichier','date_remise','etat'));
        }
        require __DIR__ . '/../View/mission/back_livrable_form.php';
    }

    public function backLivrableDelete(int $id): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->livrableModel->delete($id);
        }
        header('Location: index.php?action=back_list&success=livrable_deleted'); exit;
    }

    // ─── MÉTIER AVANCÉ 1 : Rapport IA ────────────────────────────
    public function metier1Rapport(int $id): void {
        $mission = $this->missionModel->findById($id);
        if (!$mission) { header('Location: index.php?action=back_list'); exit; }
        $livrables = $this->livrableModel->findByMission($id);
        require __DIR__ . '/../View/mission/metier1_rapport.php';
    }

    // ─── MÉTIER AVANCÉ 2 : Alertes IA ────────────────────────────
    public function metier2Alertes(): void {
        $missions  = $this->missionModel->findAll();
        $livrables = $this->livrableModel->findAll();
        require __DIR__ . '/../View/mission/metier2_alertes.php';
    }
}
