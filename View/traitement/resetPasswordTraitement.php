<?php
session_start();
require_once '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($token)) {
        die("Jeton manquant.");
    }

    if ($password !== $password_confirm) {
        $_SESSION['reset_error'] = "Les mots de passe ne correspondent pas.";
        header('Location: ../FrontOffice/reset_password.php?token=' . urlencode($token));
        exit;
    }

    if (strlen($password) < 6) {
        $_SESSION['reset_error'] = "Le mot de passe doit contenir au moins 6 caractères.";
        header('Location: ../FrontOffice/reset_password.php?token=' . urlencode($token));
        exit;
    }

    try {
        $db = config::getConnexion();
        
        // Supprimez les jetons expirés
        $db->query("UPDATE user SET reset_token = NULL, reset_token_expires = NULL WHERE reset_token_expires < NOW()");

        $query = $db->prepare('SELECT id_user FROM user WHERE reset_token = :token');
        $query->execute(['token' => $token]);
        $user = $query->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $update = $db->prepare('UPDATE user SET password = :password, reset_token = NULL, reset_token_expires = NULL WHERE id_user = :id');
            $update->execute(['password' => $hashedPassword, 'id' => $user['id_user']]);

            $_SESSION['register_success'] = true; // Use existing success message variable on login page
            header('Location: ../FrontOffice/login.php');
            exit;
        } else {
            $_SESSION['reset_error'] = "Le jeton de réinitialisation est invalide ou expiré.";
            header('Location: ../FrontOffice/reset_password.php?token=' . urlencode($token));
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['reset_error'] = "Erreur système : " . $e->getMessage();
        header('Location: ../FrontOffice/reset_password.php?token=' . urlencode($token));
        exit;
    }
}
