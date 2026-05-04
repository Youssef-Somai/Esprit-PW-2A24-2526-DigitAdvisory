<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../config.php';
require __DIR__ . '/../../lib/PHPMailer/Exception.php';
require __DIR__ . '/../../lib/PHPMailer/PHPMailer.php';
require __DIR__ . '/../../lib/PHPMailer/SMTP.php';

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
    $loginSuccess = false;
    $userData = [];

    // Connexion admin spéciale si l’utilisateur n’est pas enregistré en base.
    if ((strtolower($email) === 'mohamed@gmail.com' && $password === 'mohamed123') || 
        (strtolower($email) === 'elabenkedher@gmail.com' && $password === 'elaelaela')) {
        $userData = [
            'id_user' => 0,
            'email' => $email,
            'role' => 'admin',
            'nom' => 'Admin',
            'prenom' => 'Super',
        ];
        $loginSuccess = true;
    } else {
        $db = config::getConnexion();
        $sql = 'SELECT * FROM user WHERE email = :email';
        $stmt = $db->prepare($sql);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            if ($user['locked_until'] !== null && strtotime($user['locked_until']) > time()) {
                $remaining_time = strtotime($user['locked_until']) - time();
                $minutes = floor($remaining_time / 60);
                $seconds = $remaining_time % 60;
                $_SESSION['login_error'] = "locked:" . $minutes . ":" . str_pad((string)$seconds, 2, '0', STR_PAD_LEFT);
                header('Location: ../FrontOffice/login.php');
                exit;
            }

            $passwordValid = false;
            if (password_verify($password, $user['password'])) {
                $passwordValid = true;
            } elseif ($password === $user['password']) {
                $passwordValid = true;
            }

            if (!$passwordValid) {
                $attempts = (int)$user['login_attempts'] + 1;
                if ($attempts >= 3) {
                    $lockedUntil = date('Y-m-d H:i:s', strtotime('+5 minutes'));
                    $sql_upd = 'UPDATE user SET login_attempts = :attempts, locked_until = :locked WHERE email = :email';
                    $stmt_upd = $db->prepare($sql_upd);
                    $stmt_upd->execute(['attempts' => $attempts, 'locked' => $lockedUntil, 'email' => $email]);
                    $_SESSION['login_error'] = "locked:5:00";
                } else {
                    $sql_upd = 'UPDATE user SET login_attempts = :attempts WHERE email = :email';
                    $stmt_upd = $db->prepare($sql_upd);
                    $stmt_upd->execute(['attempts' => $attempts, 'email' => $email]);
                    $_SESSION['login_error'] = 1;
                }
                header('Location: ../FrontOffice/login.php');
                exit;
            } else {
                $sql_upd = 'UPDATE user SET login_attempts = 0, locked_until = NULL WHERE email = :email';
                $stmt_upd = $db->prepare($sql_upd);
                $stmt_upd->execute(['email' => $email]);
            }
        } else {
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
        $userData = [
            'id_user' => $user['id_user'],
            'email' => $user['email'],
            'role' => $userRole,
            'nom' => $user['nom'] ?? null,
            'prenom' => $user['prenom'] ?? null,
        ];
        $loginSuccess = true;
    }

    if ($loginSuccess) {
        if (isset($_POST['remember_me'])) {
            setcookie('remember_email', $email, time() + (86400 * 30), "/"); // 30 jours
            setcookie('remember_password', $password, time() + (86400 * 30), "/"); 
        } else {
            setcookie('remember_email', '', time() - 3600, "/");
            setcookie('remember_password', '', time() - 3600, "/");
        }

        // Envoi de l'email 2FA
        $_SESSION['pending_user'] = $userData;
        $code_2fa = sprintf("%06d", mt_rand(1, 999999));
        $_SESSION['2fa_code'] = $code_2fa;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'ranimbenothmen0@gmail.com'; 
            $mail->Password   = 'cccv edtn lcpr tktc';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('ranimbenothmen0@gmail.com', 'Digit Advisory');
            $mail->addAddress($userData['email'], ($userData['prenom'] ?? '') . ' ' . ($userData['nom'] ?? ''));

            $mail->isHTML(true);
            $mail->Subject = 'Code de verification 2FA - Digit Advisory';
            $mail->Body    = "<h2>Bonjour " . htmlspecialchars($userData['prenom'] ?? 'Utilisateur') . ",</h2><p>Voici votre code de vérification pour vous connecter à Digit Advisory : <b style='font-size: 24px; color: #0d6efd;'>" . $code_2fa . "</b></p><p>Ne partagez ce code avec personne.</p>";
            
            $mail->send();
            header('Location: ../FrontOffice/verify_2fa.php');
            exit;
        } catch (Exception $e) {
            $_SESSION['login_error'] = 3;
            header('Location: ../FrontOffice/login.php');
            exit;
        }
    }

} catch (Exception $e) {
    $_SESSION['login_error'] = 3;
    header('Location: ../FrontOffice/login.php');
    exit;
}
