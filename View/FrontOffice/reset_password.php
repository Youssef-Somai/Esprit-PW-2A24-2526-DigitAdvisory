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
        .form-control.error {
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12);
        }
        .error-text {
            color: #dc2626;
            font-size: 0.85rem;
            margin-top: -0.5rem;
            margin-bottom: 1rem;
            display: block;
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

            <form id="resetForm" action="../traitement/resetPasswordTraitement.php" method="POST" novalidate>
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                
                <label for="password" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Nouveau mot de passe</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••">
                <span class="error-text" id="password-error"></span>
                
                <label for="password_confirm" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Confirmez le mot de passe</label>
                <input type="password" name="password_confirm" id="password_confirm" class="form-control" placeholder="••••••••">
                <span class="error-text" id="password-confirm-error"></span>
                
                <button type="submit" class="btn btn-primary btn-block">Enregistrer</button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            let isValid = true;
            
            const pwdInput = document.getElementById('password');
            const pwdConfirmInput = document.getElementById('password_confirm');
            const pwdError = document.getElementById('password-error');
            const pwdConfirmError = document.getElementById('password-confirm-error');

            // Reset errors
            pwdInput.classList.remove('error');
            pwdConfirmInput.classList.remove('error');
            pwdError.textContent = '';
            pwdConfirmError.textContent = '';

            const pwdValue = pwdInput.value.trim();
            const pwdConfirmValue = pwdConfirmInput.value.trim();

            if (pwdValue === '') {
                pwdInput.classList.add('error');
                pwdError.textContent = 'Le mot de passe est requis.';
                isValid = false;
            } else if (pwdValue.length < 6) {
                pwdInput.classList.add('error');
                pwdError.textContent = 'Le mot de passe doit comporter au moins 6 caractères.';
                isValid = false;
            }

            if (pwdConfirmValue === '') {
                pwdConfirmInput.classList.add('error');
                pwdConfirmError.textContent = 'Veuillez confirmer votre mot de passe.';
                isValid = false;
            } else if (pwdValue !== pwdConfirmValue) {
                pwdConfirmInput.classList.add('error');
                pwdConfirmError.textContent = 'Les mots de passe ne correspondent pas.';
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            }
        });

        // Supprimer l'erreur quand l'utilisateur commence à taper
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('error');
                const errorSpan = document.getElementById(this.id + '-error');
                if (errorSpan) {
                    errorSpan.textContent = '';
                }
            });
        });
    </script>
</body>
</html>
