<?php
session_start();
require_once '../../config.php';

// Import des classes PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Requérir les fichiers PHPMailer téléchargés
require '../../lib/PHPMailer/Exception.php';
require '../../lib/PHPMailer/PHPMailer.php';
require '../../lib/PHPMailer/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['reset_error'] = "Adresse email invalide.";
        header('Location: ../FrontOffice/forgot_password.php');
        exit;
    }

    try {
        $db = config::getConnexion();
        $query = $db->prepare('SELECT id_user, prenom, nom FROM user WHERE email = :email');
        $query->execute(['email' => $email]);
        $user = $query->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Générer le token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            try {
                $update = $db->prepare('UPDATE user SET reset_token = :token, reset_token_expires = :expires WHERE email = :email');
                $update->execute(['token' => $token, 'expires' => $expires, 'email' => $email]);
            } catch (PDOException $e) {
                die("Erreur : Veuillez exécuter la requête SQL pour ajouter les colonnes 'reset_token' et 'reset_token_expires' dans la table 'user'.");
            }

            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/Esprit-PW-2A24-2526-DigitAdvisory/View/FrontOffice/reset_password.php?token=" . $token;

            // Configuration de PHPMailer
            $mail = new PHPMailer(true);

            try {
                // Paramètres du serveur SMTP
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                // Remplacez par votre adresse Gmail
                $mail->Username   = 'ranimbenothmen0@gmail.com'; 
                // Mot de passe d'application fourni
                $mail->Password   = 'cccv edtn lcpr tktc';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Destinataires
                $mail->setFrom('ranimbenothmen0@gmail.com', 'Digit Advisory');
                $mail->addAddress($email, $user['prenom'] . ' ' . $user['nom']);

                // Contenu de l'e-mail
                $mail->isHTML(true);
                $mail->Subject = 'Réinitialisation de votre mot de passe - Digit Advisory';
                
                $mailContent = "
                    <h2>Bonjour " . htmlspecialchars($user['prenom']) . ",</h2>
                    <p>Vous avez demandé la réinitialisation de votre mot de passe sur Digit Advisory.</p>
                    <p>Veuillez cliquer sur le lien ci-dessous pour créer un nouveau mot de passe :</p>
                    <p><a href='" . $reset_link . "' style='padding: 10px 15px; background-color: #0d6efd; color: white; text-decoration: none; border-radius: 5px;'>Réinitialiser mon mot de passe</a></p>
                    <p>Ce lien expirera dans 1 heure.</p>
                    <p>Si vous n'avez pas fait cette demande, vous pouvez ignorer cet e-mail.</p>
                    <br>
                    <p>Cordialement,<br>L'équipe Digit Advisory</p>
                ";
                
                $mail->Body    = $mailContent;
                $mail->AltBody = "Bonjour,\n\nVous avez demandé la réinitialisation de votre mot de passe.\n\nCliquez sur ce lien: " . $reset_link . "\n\nCe lien expirera dans 1 heure.";

                $mail->send();
                $_SESSION['reset_success'] = "Un lien a été envoyé à votre adresse email. Veuillez vérifier votre boîte de réception.";
            } catch (Exception $e) {
                $_SESSION['reset_error'] = "Le message n'a pas pu être envoyé. Erreur Mailer : {$mail->ErrorInfo}";
            }
        } else {
            // Pour des raisons de sécurité, afficher le même message même si l'email n'existe pas
            $_SESSION['reset_success'] = "Si cette adresse est associée à un compte, un lien a été envoyé.";
        }

        header('Location: ../FrontOffice/forgot_password.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['reset_error'] = "Erreur système : " . $e->getMessage();
        header('Location: ../FrontOffice/forgot_password.php');
        exit;
    }
}
