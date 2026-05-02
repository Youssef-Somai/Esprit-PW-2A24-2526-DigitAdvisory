<?php
session_start();
if (!isset($_SESSION['pending_user']) || !isset($_SESSION['2fa_code'])) {
    header('Location: login.php');
    exit;
}
$error = $_SESSION['2fa_error'] ?? null;
unset($_SESSION['2fa_error']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification 2FA | Digit Advisory</title>
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
        }

        .auth-header {
            text-align: center;
            padding: 2rem;
            background: rgba(37, 99, 235, 0.05);
            border-bottom: 1px solid var(--gray-light);
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        }

        .auth-header .logo {
            justify-content: center;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            font-family: var(--font-heading);
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--dark);
            gap: 0.5rem;
        }

        .auth-body {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-light);
            border-radius: var(--radius);
            font-family: var(--font-main);
            font-size: 1rem;
            transition: var(--transition);
            box-sizing: border-box;
            text-align: center;
            letter-spacing: 5px;
            font-weight: 600;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn-block {
            width: 100%;
            margin-top: 1rem;
        }
    </style>
</head>
<body>

    <div class="auth-container">
        <div class="auth-box fade-in-up">
            <div class="auth-header">
                <div class="logo">
                    <i class="fa-solid fa-shield-halved text-primary"></i>
                    Digit Advisory
                </div>
                <p style="color: var(--gray); font-size: 0.9rem;">Double authentification</p>
            </div>

            <div class="auth-body">
                <p style="text-align: center; margin-bottom: 1.5rem; color: var(--gray);">
                    Un code de vérification a été envoyé à votre adresse email (<b><?php echo htmlspecialchars($_SESSION['pending_user']['email'] ?? ''); ?></b>). Veuillez le saisir ci-dessous.
                </p>

                <?php if ($error): ?>
                    <div style="background-color: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: center; border: 1px solid #fca5a5; font-weight: 500;">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form action="../traitement/verify_2faTraitement.php" method="POST">
                    <div class="form-group">
                        <label for="code">Code de vérification</label>
                        <input type="text" name="code" id="code" class="form-control" placeholder="000000" maxlength="6" autocomplete="off" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Vérifier</button>
                    
                    <div style="text-align: center; margin-top: 1rem;">
                        <a href="login.php" style="color: var(--gray); text-decoration: none; font-size: 0.9rem;">Annuler et retourner à la connexion</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
