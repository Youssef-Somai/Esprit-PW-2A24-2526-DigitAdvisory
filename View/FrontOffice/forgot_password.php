<?php
session_start();
$error = $_SESSION['reset_error'] ?? null;
$success = $_SESSION['reset_success'] ?? null;
unset($_SESSION['reset_error'], $_SESSION['reset_success']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié | Digit Advisory</title>
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
        .back-link {
            position: absolute;
            top: 2rem;
            left: 2rem;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
    </style>
</head>
<body>
    <a href="login.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Retour à la connexion</a>
    <div class="auth-container">
        <div class="auth-box">
            <h2 style="text-align: center; margin-bottom: 1rem; color: var(--primary);">Réinitialisation</h2>
            <p style="text-align: center; margin-bottom: 2rem; color: var(--gray);">Entrez votre adresse email pour recevoir un lien de réinitialisation.</p>

            <?php if ($error): ?>
                <div style="background-color: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: center;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div style="background-color: #d1fae5; color: #065f46; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: center; word-break: break-all;">
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <form action="../traitement/forgotPasswordTraitement.php" method="POST">
                <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Email professionnel</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="exemple@entreprise.com" required>
                <button type="submit" class="btn btn-primary btn-block">Envoyer le lien</button>
            </form>
        </div>
    </div>
</body>
</html>
