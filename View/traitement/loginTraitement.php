<?php
session_start();

require_once __DIR__ . '/../../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../FrontOffice/login.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    $_SESSION['login_error'] = 1;
    header('Location: ../FrontOffice/login.php');
    exit;
}

try {
    // Connexion admin spéciale si l’utilisateur n’est pas enregistré en base.
    if (strtolower($email) === 'mohamed@gmail.com' && $password === 'mohamed123') {
        $_SESSION['user'] = [
            'id_user' => 0,
            'email' => $email,
            'role' => 'admin',
            'nom' => null,
            'prenom' => null,
        ];
        header('Location: ../BackOffice/back-utilisateur.php');
        exit;
    }

    $db = config::getConnexion();
    $sql = 'SELECT * FROM user WHERE email = :email';
    $stmt = $db->prepare($sql);
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    $passwordValid = false;
    if ($user) {
        if (password_verify($password, $user['password'])) {
            $passwordValid = true;
        } elseif ($password === $user['password']) {
            // Fall back pour un mot de passe stocké en clair dans la base.
            $passwordValid = true;
        }
    }

    if (!$user || !$passwordValid) {
        $_SESSION['login_error'] = 1;
        header('Location: ../FrontOffice/login.php');
        exit;
    }

    if ($user['statut_compte'] !== 'actif') {
        $_SESSION['login_error'] = 2;
        header('Location: ../FrontOffice/login.php');
        exit;
    }

    $userRole = strtolower(trim($user['role'] ?? ''));

    $_SESSION['user'] = [
        'id_user' => $user['id_user'],
        'email' => $user['email'],
        'role' => $userRole,
        'nom' => $user['nom'] ?? null,
        'prenom' => $user['prenom'] ?? null,
    ];

    if ($userRole === 'admin') {
        header('Location: ../BackOffice/back-utilisateur.php');
    } elseif ($userRole === 'expert') {
        header('Location: ../FrontOffice/front-expert-dashboard.php');
    } else {
        header('Location: ../FrontOffice/front-entreprise-dashboard.php');
    }
    exit;
} catch (Exception $e) {
    $_SESSION['login_error'] = 3;
    header('Location: ../FrontOffice/login.php');
    exit;
}
