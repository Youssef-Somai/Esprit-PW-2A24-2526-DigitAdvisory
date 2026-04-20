<?php
session_start();
require_once '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['reset_error'] = "Adresse email invalide.";
        header('Location: ../FrontOffice/forgot_password.php');
        exit;
    }

    try {
        $db = config::getConnexion();
        $query = $db->prepare('SELECT id_user FROM user WHERE email = :email');
        $query->execute(['email' => $email]);
        $user = $query->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Generate token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Check if column exists, if not, we must create it or inform user
            // We assume the user added reset_token and reset_token_expires
            // ALTER TABLE user ADD COLUMN reset_token VARCHAR(255) DEFAULT NULL;
            // ALTER TABLE user ADD COLUMN reset_token_expires DATETIME DEFAULT NULL;
            try {
                $update = $db->prepare('UPDATE user SET reset_token = :token, reset_token_expires = :expires WHERE email = :email');
                $update->execute(['token' => $token, 'expires' => $expires, 'email' => $email]);
            } catch (PDOException $e) {
                // Ignore if columns don't exist yet, we will inform the user in the prompt
                die("Erreur : Veuillez exécuter la requête SQL pour ajouter les colonnes 'reset_token' et 'reset_token_expires' dans la table 'user'.");
            }

            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/Esprit-PW-2A24-2526-DigitAdvisory/View/FrontOffice/reset_password.php?token=" . $token;

            // TODO: Intégration de PHPMailer avec Google OAuth2
            // Pour utiliser les clés Client ID et Secret fournies, il faut configurer PHPMailer avec le provider Google OAuth.
            // Comme c'est souvent complexe sur localhost sans Composer, on simule l'envoi ici.
            
            // mail($email, "Réinitialisation de mot de passe", "Cliquez sur ce lien pour réinitialiser votre mot de passe : " . $reset_link);
            
            // Pour le test en local, on affiche le lien directement dans le message de succès (à retirer en production)
            $_SESSION['reset_success'] = "Un lien a été envoyé à votre adresse email. (Simulation Localhost: <a href='$reset_link' style='color:#065f46;text-decoration:underline;'>Cliquez ici</a>)";
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
