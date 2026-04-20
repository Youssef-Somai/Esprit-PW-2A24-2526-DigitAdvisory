<?php
session_start();
$token = $_GET['token'] ?? '';
$error = $_SESSION['reset_error'] ?? null;
unset($_SESSION['reset_error']);

if (empty($token)) {
    die("Jeton de réinitialisation invalide ou absent.");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau mot de passe | Digit Advisory</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .auth-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--light) 0%, #e0e7ff 100%);
            padding: 2rem;
        }
        .auth-box {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 500px;
            padding: 2rem;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-light);
            border-radius: var(--radius);
            margin-bottom: 1rem;
            box-sizing: border-box;
        }
        .btn-block {
            width: 100%;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h2 style="text-align: center; margin-bottom: 1rem; color: var(--primary);">Nouveau mot de passe</h2>
            <p style="text-align: center; margin-bottom: 2rem; color: var(--gray);">Veuillez entrer votre nouveau mot de passe.</p>

            <?php if ($error): ?>
                <div style="background-color: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: center;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="../traitement/resetPasswordTraitement.php" method="POST">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                
                <label for="password" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Nouveau mot de passe</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required minlength="6">
                
                <label for="password_confirm" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Confirmez le mot de passe</label>
                <input type="password" name="password_confirm" id="password_confirm" class="form-control" placeholder="••••••••" required minlength="6">
                
                <button type="submit" class="btn btn-primary btn-block">Enregistrer</button>
            </form>
        </div>
    </div>
</body>
</html>
