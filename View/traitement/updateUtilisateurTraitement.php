<?php

require_once __DIR__ . '/../../Model/utilisateur.php';
require_once __DIR__ . '/../../Controller/utilisateur_controller.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_user = isset($_POST['id_user']) ? (int) $_POST['id_user'] : 0;
    $role = $_POST['role'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $rawPassword = trim($_POST['password'] ?? '');
    $password = !empty($rawPassword) ? password_hash($rawPassword, PASSWORD_DEFAULT) : '';
    $statut_compte = $_POST['statut_compte'] ?? 'actif';

    if (empty($password)) {
        die('Le mot de passe est obligatoire.');
    }

    $controller = new UtilisateurController();

    if ($role === 'expert') {
        $user = new Utilisateur(
            $id_user,
            $email,
            $password,
            'expert',
            $statut_compte,
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
            $id_user,
            $email,
            $password,
            'entreprise',
            $statut_compte,
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

    $controller->modifierUtilisateur($user);

    header('Location: ../FrontOffice/listUtilisateurs.php');
    exit;
}