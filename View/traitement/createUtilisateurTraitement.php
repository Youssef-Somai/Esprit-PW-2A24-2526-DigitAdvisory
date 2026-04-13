<?php

session_start();
require_once __DIR__ . '/../../Model/utilisateur.php';
require_once __DIR__ . '/../../Controller/utilisateur_controller.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = strtolower(trim($_POST['role'] ?? ''));
    $email = trim($_POST['email'] ?? '');
    $password = password_hash(trim($_POST['password'] ?? ''), PASSWORD_DEFAULT);

    // Si le rôle n'est pas correctement envoyé depuis le formulaire,
    // on tente de deviner le type de compte en fonction des champs renseignés.
    if ($role !== 'expert' && $role !== 'entreprise') {
        $isExpertFields = trim($_POST['nom'] ?? '') !== '' ||
                          trim($_POST['prenom'] ?? '') !== '' ||
                          trim($_POST['domaine'] ?? '') !== '' ||
                          trim($_POST['niveau_experience'] ?? '') !== '' ||
                          trim($_POST['tarif_journalier'] ?? '') !== '';
        $isEntrepriseFields = trim($_POST['nom_entreprise'] ?? '') !== '' ||
                               trim($_POST['secteur_activite'] ?? '') !== '' ||
                               trim($_POST['adresse'] ?? '') !== '' ||
                               trim($_POST['telephone'] ?? '') !== '';

        if ($isExpertFields) {
            $role = 'expert';
        } elseif ($isEntrepriseFields) {
            $role = 'entreprise';
        }
    }

    $controller = new UtilisateurController();

    if ($role === 'expert') {
        $user = new Utilisateur(
            null,
            $email,
            $password,
            'expert',
            'actif',
            null,
            null,
            null,
            null,
            trim($_POST['nom'] ?? ''),
            trim($_POST['prenom'] ?? ''),
            trim($_POST['domaine'] ?? ''),
            trim($_POST['niveau_experience'] ?? ''),
            isset($_POST['tarif_journalier']) ? (float) $_POST['tarif_journalier'] : null
        );
    } elseif ($role === 'entreprise') {
        $user = new Utilisateur(
            null,
            $email,
            $password,
            'entreprise',
            'actif',
            trim($_POST['nom_entreprise'] ?? ''),
            trim($_POST['secteur_activite'] ?? ''),
            trim($_POST['adresse'] ?? ''),
            trim($_POST['telephone'] ?? ''),
            null,
            null,
            null,
            null,
            null
        );
    } else {
        die('Rôle invalide');
    }

    if (!$controller->ajouterUtilisateur($user)) {
        die('Erreur lors de la création du compte.');
    }

    $_SESSION['register_success'] = true;
    header('Location: ../FrontOffice/login.php');
    exit;
}