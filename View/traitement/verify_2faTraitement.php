<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../FrontOffice/login.php');
    exit;
}

$code_saisi = trim($_POST['code'] ?? '');

if (!isset($_SESSION['pending_user']) || !isset($_SESSION['2fa_code'])) {
    header('Location: ../FrontOffice/login.php');
    exit;
}

if ($code_saisi === (string)$_SESSION['2fa_code']) {
    // Code correct, connecter l'utilisateur
    $_SESSION['user'] = $_SESSION['pending_user'];
    $userRole = $_SESSION['user']['role'];
    
    // Nettoyer les sessions de vérification
    unset($_SESSION['pending_user']);
    unset($_SESSION['2fa_code']);

    if ($userRole === 'admin') {
        header('Location: ../BackOffice/back-utilisateur.php');
    } elseif ($userRole === 'expert') {
        header('Location: ../FrontOffice/front-expert-dashboard.php');
    } else {
        header('Location: ../FrontOffice/front-entreprise-dashboard.php');
    }
    exit;
} else {
    // Code incorrect
    $_SESSION['2fa_error'] = "Le code de vérification est incorrect.";
    header('Location: ../FrontOffice/verify_2fa.php');
    exit;
}
