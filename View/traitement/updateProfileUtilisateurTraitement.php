<?php
session_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controller/utilisateur_controller.php';
require_once __DIR__ . '/../../Model/utilisateur.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['user']['id_user'])) {
    header('Location: ../FrontOffice/login.php');
    exit;
}

$id_user = isset($_POST['id_user']) ? (int) $_POST['id_user'] : 0;
if ($id_user !== (int) $_SESSION['user']['id_user']) {
    header('Location: ../FrontOffice/front-utilisateur.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$rawPassword = trim($_POST['password'] ?? '');

$controller = new UtilisateurController();
$currentUser = $controller->getUserById($id_user);
if (!$currentUser) {
    header('Location: ../FrontOffice/login.php');
    exit;
}

$role = $_POST['role'] ?? $currentUser['role'];
$password = !empty($rawPassword)
    ? password_hash($rawPassword, PASSWORD_DEFAULT)
    : $currentUser['password'];

if ($role === 'expert') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $domaine = trim($_POST['domaine'] ?? '');
    $niveauExperience = trim($_POST['niveau_experience'] ?? '');
    $tarifJournalier = isset($_POST['tarif_journalier']) ? (float) $_POST['tarif_journalier'] : null;

    $user = new Utilisateur(
        $id_user,
        $email,
        $password,
        'expert',
        $currentUser['statut_compte'] ?? 'actif',
        null,
        null,
        null,
        null,
        $nom,
        $prenom,
        $domaine,
        $niveauExperience,
        $tarifJournalier
    );
    $redirect = '../FrontOffice/front-expert-profil.php?updated=1';
} else {
    $nomEntreprise = trim($_POST['nom_entreprise'] ?? '');
    $secteurActivite = trim($_POST['secteur_activite'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');

    $user = new Utilisateur(
        $id_user,
        $email,
        $password,
        'entreprise',
        $currentUser['statut_compte'] ?? 'actif',
        $nomEntreprise,
        $secteurActivite,
        $adresse,
        $telephone,
        null,
        null,
        null,
        null,
        null
    );
    $redirect = '../FrontOffice/front-utilisateur.php?updated=1';
}

$controller->modifierUtilisateur($user);

$_SESSION['user']['email'] = $email;
if ($role === 'expert') {
    $_SESSION['user']['nom'] = $prenom;
    $_SESSION['user']['prenom'] = $nom;
}

header('Location: ' . $redirect);
exit;
