<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../email/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../../email/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../../email/PHPMailer/src/Exception.php';






if (
    !isset($_POST['email']) ||
    !isset($_POST['score']) ||
    !isset($_POST['niveau']) ||
    !isset($_POST['message'])
) {
    die("Données invalides.");
}

$email = trim($_POST['email']);
$score = (int) $_POST['score'];
$niveau = trim($_POST['niveau']);
$message = trim($_POST['message']);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Email invalide.");
}

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;

    $mail->Username = '';
    $mail->Password = '';

    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    $mail->setFrom('', 'DigitAdvisory Quiz');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Votre résultat du quiz';
   $printLink = "http://localhost/Esprit-PW-2A24-2526-DigitAdvisory/View/FrontOffice/print-result.php?score={$score}&niveau=" . urlencode($niveau) . "&message=" . urlencode($message);

$mail->Body = "
    <div style='font-family:Arial,sans-serif;background:#f1f5f9;padding:25px;'>
        <div style='max-width:600px;margin:auto;background:white;border-radius:18px;padding:25px;'>
            <h2 style='color:#2563eb;'>Résultat du questionnaire</h2>

            <p>Bonjour,</p>
            <p>Voici votre résultat :</p>

            <div style='font-size:34px;font-weight:bold;color:#2563eb;margin:20px 0;'>
                Score : {$score}%
            </div>

            <p><strong>Niveau :</strong> {$niveau}</p>
            <p>{$message}</p>

            <a href='{$printLink}' 
               style='display:inline-block;margin-top:20px;background:#2563eb;color:white;padding:12px 18px;border-radius:999px;text-decoration:none;font-weight:bold;'>
                Télécharger / Imprimer en PDF
            </a>
        </div>
    </div>
";

    $mail->send();










echo "
<div style='
    font-family: \"Segoe UI\", Roboto, sans-serif;
    text-align: center;
    margin-top: 100px;
'>
    <div style='
        display: inline-block;
        padding: 30px 40px;
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    '>
        <h2 style='
            color: #10b981;
            margin-bottom: 15px;
            font-weight: 600;
        '>
            Email envoyé avec succès ✅
        </h2>

        <p style='
            color: #374151;
            font-size: 15px;
            margin-bottom: 25px;
        '>
            Résultat envoyé à : 
            <strong style=\"color:#111827;\">" . htmlspecialchars($email) . "</strong>
        </p>

        <a href='front-quiz.php' style='
            display: inline-block;
            padding: 10px 18px;
            background: #10b981;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        ' 
        onmouseover=\"this.style.background='#059669'\" 
        onmouseout=\"this.style.background='#10b981'\">
            Retour aux quiz
        </a>
    </div>
</div>
";

} catch (Exception $e) {
    echo "
    <div style='font-family:Arial;text-align:center;margin-top:80px;'>
        <h2 style='color:#ef4444;'>Erreur lors de l'envoi ❌</h2>
        <p>" . htmlspecialchars($mail->ErrorInfo) . "</p>
        <a href='front-quiz.php'>Retour</a>
    </div>
    ";
}
?>