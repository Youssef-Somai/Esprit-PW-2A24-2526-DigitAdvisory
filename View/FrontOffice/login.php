<?php
session_start();
$login_error = $_SESSION['login_error'] ?? null;
$register_success = $_SESSION['register_success'] ?? null;
session_unset();
session_destroy();
session_start(); // Redémarrer une session vierge pour les erreurs éventuelles
if ($login_error) $_SESSION['login_error'] = $login_error;
if ($register_success) $_SESSION['register_success'] = $register_success;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion / Inscription | Digit Advisory</title>
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
            max-height: 95vh;
            overflow-y: auto;
            overflow-x: hidden;
            position: relative;
        }

        .auth-header {
            text-align: center;
            padding: 2rem;
            background: rgba(37, 99, 235, 0.05);
            border-bottom: 1px solid var(--gray-light);
        }

        .auth-header .logo {
            justify-content: center;
            margin-bottom: 1rem;
        }

        .auth-tabs {
            display: flex;
            background: var(--light);
        }

        .auth-tab {
            flex: 1;
            text-align: center;
            padding: 1rem;
            font-weight: 600;
            cursor: pointer;
            color: var(--gray);
            border-bottom: 2px solid transparent;
            transition: var(--transition);
        }

        .auth-tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
            background: white;
        }

        .auth-body {
            padding: 2rem;
            position: relative;
        }

        .auth-form {
            display: none;
            transition: all 0.4s ease;
        }

        .auth-form.active {
            display: block;
            opacity: 1;
            visibility: visible;
            transform: translateX(0);
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
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-control.error {
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12);
        }

        .error-text {
            color: #dc2626;
            font-size: 0.85rem;
            margin-top: 0.35rem;
            display: block;
        }

        .role-selector {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .role-option {
            flex: 1;
            border: 1px solid var(--gray-light);
            border-radius: var(--radius);
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .role-option:hover {
            border-color: var(--primary);
            background: rgba(37, 99, 235, 0.05);
        }

        .role-option.selected {
            border-color: var(--primary);
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary);
            font-weight: 600;
        }

        .role-option i {
            display: block;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
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
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            z-index: 10;
            text-decoration: none;
        }

        .back-link:hover {
            color: var(--primary-hover);
        }

        #expert-fields,
        #entreprise-fields {
            margin-top: 1rem;
        }
    </style>
</head>
<body>

    <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Retour</a>

    <div class="auth-container">
        <div class="auth-box fade-in-up">
            <div class="auth-header">
                <div class="logo">
                    <i class="fa-solid fa-chart-pie text-primary"></i>
                    Digit Advisory
                </div>
                <p style="color: var(--gray); font-size: 0.9rem;">Accédez à l'espace de gestion et d'expertise</p>
            </div>

            <div class="auth-tabs">
                <div class="auth-tab active" data-target="login">Connexion</div>
                <div class="auth-tab" data-target="register">Inscription</div>
            </div>

            <div class="auth-body">

                <?php if (isset($_SESSION['register_success']) && $_SESSION['register_success'] === true): ?>
                    <div style="background-color: #d1fae5; color: #065f46; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: center; border: 1px solid #10b981; font-weight: 500;">
                        Votre compte a bien été créé ! Vous pouvez maintenant vous connecter.
                    </div>
                    <?php unset($_SESSION['register_success']); ?>
                <?php endif; ?>
                <?php if (isset($_SESSION['login_error'])): ?>
                    <div class="login-error" style="background-color: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: center; border: 1px solid #fca5a5; font-weight: 500;">
                        <?php
                            $msg = 'Une erreur est survenue, veuillez réessayer.';
                            if ($_SESSION['login_error'] == 1) {
                                $msg = 'Email ou mot de passe incorrect.';
                            } elseif ($_SESSION['login_error'] == 2) {
                                $msg = 'Ce compte est désactivé.';
                            }
                            echo $msg;
                        ?>
                    </div>
                    <?php unset($_SESSION['login_error']); ?>
                <?php endif; ?>

                <!-- LOGIN -->
                <form id="login" class="auth-form active" action="../traitement/loginTraitement.php" method="POST" onsubmit="return validateLoginForm();" novalidate>
                    <div class="form-group">
                        <label for="login-email">Email professionnel</label>
                        <input type="email" name="email" id="login-email" class="form-control" placeholder="exemple@entreprise.com">
                        <span class="error-text" id="login-email-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="login-password">Mot de passe</label>
                        <input type="password" name="password" id="login-password" class="form-control" placeholder="••••••••">
                        <span class="error-text" id="login-password-error"></span>
                    </div>

                    <div style="display: flex; justify-content: space-between; margin-bottom: 1.5rem; font-size: 0.9rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox"> Se souvenir de moi
                        </label>
                        <a href="forgot_password.php" class="text-primary">Mot de passe oublié ?</a>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
                </form>

                <!-- REGISTER -->
<form id="register" class="auth-form" action="../traitement/createUtilisateurTraitement.php" method="POST" onsubmit="return validateRegisterForm();" novalidate>
                    <div class="role-selector">
                        <div class="role-option selected" data-role="entreprise" onclick="selectRole('entreprise')">
                            <i class="fa-regular fa-building"></i>
                            Entreprise
                        </div>
                        <div class="role-option" data-role="expert" onclick="selectRole('expert')">
                            <i class="fa-solid fa-user-tie"></i>
                            Expert
                        </div>
                    </div>

                    <input type="hidden" name="role" id="register-role" value="entreprise">

                    <div class="form-group">
                        <label for="reg-email">Email</label>
                        <input type="email" name="email" id="reg-email" class="form-control" placeholder="exemple@domaine.com">
                        <span class="error-text" id="error-reg-email"></span>
                    </div>

                    <div class="form-group">
                        <label for="reg-password">Mot de passe</label>
                        <input type="password" name="password" id="reg-password" class="form-control" placeholder="Créez un mot de passe">
                        <span class="error-text" id="error-reg-password"></span>
                    </div>

                    <!-- CHAMPS ENTREPRISE -->
                    <div id="entreprise-fields">
                        <div class="form-group">
                            <label for="nom_entreprise">Nom de l'entreprise</label>
                            <input type="text" name="nom_entreprise" id="nom_entreprise" class="form-control" placeholder="Nom de l'entreprise" maxlength="10" pattern="[A-Za-zÀ-ÖØ-öø-ÿ ]*">
                        </div>

                        <div class="form-group">
                            <label for="secteur_activite">Secteur d'activité</label>
                            <input type="text" name="secteur_activite" id="secteur_activite" class="form-control" placeholder="Secteur d'activité" maxlength="10" pattern="[A-Za-zÀ-ÖØ-öø-ÿ ]*">
                        </div>

                        <div class="form-group">
                            <label for="adresse">Adresse</label>
                            <input type="text" name="adresse" id="adresse" class="form-control" placeholder="Adresse complète">
                        </div>

                        <div class="form-group">
                            <label for="telephone">Téléphone</label>
                            <input type="text" name="telephone" id="telephone" class="form-control" placeholder="Téléphone" inputmode="numeric" maxlength="8">
                            <span class="error-text" id="error-telephone"></span>
                        </div>
                    </div>

                    <!-- CHAMPS EXPERT -->
                    <div id="expert-fields" style="display:none;">
                        <div class="form-group">
                            <label for="nom">Nom</label>
                            <input type="text" name="nom" id="nom" class="form-control" placeholder="Nom" maxlength="10" pattern="[A-Za-zÀ-ÖØ-öø-ÿ ]*">
                        </div>

                        <div class="form-group">
                            <label for="prenom">Prénom</label>
                            <input type="text" name="prenom" id="prenom" class="form-control" placeholder="Prénom" maxlength="10" pattern="[A-Za-zÀ-ÖØ-öø-ÿ ]*">
                        </div>

                        <div class="form-group">
                            <label for="domaine">Domaine</label>
                            <input type="text" name="domaine" id="domaine" class="form-control" placeholder="Domaine d'expertise" maxlength="10" pattern="[A-Za-zÀ-ÖØ-öø-ÿ ]*">
                        </div>

                        <div class="form-group">
                            <label for="niveau_experience">Niveau d'expérience</label>
                            <input type="text" name="niveau_experience" id="niveau_experience" class="form-control" placeholder="Ex: Junior, Senior..." maxlength="10" pattern="[A-Za-zÀ-ÖØ-öø-ÿ ]*">
                            <span class="error-text" id="error-niveau_experience"></span>
                        </div>

                        <div class="form-group">
                            <label for="tarif_journalier">Tarif journalier</label>
                            <input type="text" name="tarif_journalier" id="tarif_journalier" class="form-control" placeholder="Tarif journalier" inputmode="numeric" maxlength="6">
                            <span class="error-text" id="error-tarif_journalier"></span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Créer mon compte</button>

                    <p style="text-align: center; margin-top: 1rem; font-size: 0.8rem; color: var(--gray);">
                        En créant un compte, vous acceptez nos <a href="#" class="text-primary">Conditions d'utilisation</a>.
                    </p>
                </form>
            </div>
        </div>
    </div>

    <script>
        const tabs = document.querySelectorAll('.auth-tab');
        const forms = document.querySelectorAll('.auth-form');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                forms.forEach(f => f.classList.remove('active'));

                tab.classList.add('active');
                const targetId = tab.getAttribute('data-target');
                document.getElementById(targetId).classList.add('active');
            });
        });

        if (window.location.hash === '#register') {
            document.querySelector('[data-target="register"]').click();
        }

        if (document.querySelector('.login-error')) {
            document.querySelector('[data-target="login"]').click();
        }

        function selectRole(role) {
            document.querySelectorAll('.role-option').forEach(el => el.classList.remove('selected'));
            document.querySelector(`.role-option[data-role="${role}"]`).classList.add('selected');

            document.getElementById('register-role').value = role;

            if (role === 'expert') {
                document.getElementById('expert-fields').style.display = 'block';
                document.getElementById('entreprise-fields').style.display = 'none';
            } else {
                document.getElementById('expert-fields').style.display = 'none';
                document.getElementById('entreprise-fields').style.display = 'block';
            }
        }

        function clearRegisterErrors() {
            document.querySelectorAll('.error-text').forEach(el => el.textContent = '');
            document.querySelectorAll('#register .form-control').forEach(el => el.classList.remove('error'));
        }

        function showRegisterError(input, message) {
            if (!input) return;
            input.classList.add('error');
            const id = input.id ? `error-${input.id}` : null;
            let errorEl = id ? document.getElementById(id) : null;

            if (!errorEl) {
                errorEl = document.createElement('span');
                errorEl.className = 'error-text';
                if (id) errorEl.id = id;
                input.insertAdjacentElement('afterend', errorEl);
            }
            errorEl.textContent = message;
        }

        function showLoginError(input, message) {
            if (!input) return;
            input.classList.add('error');
            const errorEl = document.getElementById(`login-${input.id}-error`);
            if (errorEl) {
                errorEl.textContent = message;
            }
        }

        function clearLoginErrors() {
            document.querySelectorAll('#login .error-text').forEach(el => el.textContent = '');
            document.querySelectorAll('#login .form-control').forEach(el => el.classList.remove('error'));
        }

        function validateLoginForm() {
            clearLoginErrors();
            const emailField = document.getElementById('login-email');
            const passwordField = document.getElementById('login-password');
            let valid = true;
            const email = emailField.value.trim();
            const password = passwordField.value.trim();

            if (email === '') {
                showLoginError(emailField, 'L’email est requis.');
                valid = false;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showLoginError(emailField, 'Veuillez entrer un email valide.');
                valid = false;
            }

            if (password === '') {
                showLoginError(passwordField, 'Le mot de passe est requis.');
                valid = false;
            }

            return valid;
        }

        function attachRegisterFieldListeners() {
            const fields = document.querySelectorAll('#register .form-control');
            fields.forEach(field => {
                field.addEventListener('input', () => {
                    if (field.classList.contains('error')) {
                        field.classList.remove('error');
                        const errorEl = document.getElementById(`error-${field.id}`);
                        if (errorEl) {
                            errorEl.textContent = '';
                        }
                    }
                });
            });

            const nameFields = [
                document.getElementById('nom_entreprise'),
                document.getElementById('secteur_activite'),
                document.getElementById('nom'),
                document.getElementById('prenom'),
                document.getElementById('domaine'),
                document.getElementById('niveau_experience')
            ];
            nameFields.forEach(field => {
                if (!field) return;
                field.addEventListener('input', () => {
                    field.value = field.value.replace(/[^A-Za-zÀ-ÖØ-öø-ÿ ]/g, '').slice(0, 10);
                });
            });

            const phoneField = document.getElementById('telephone');
            if (phoneField) {
                phoneField.addEventListener('input', () => {
                    phoneField.value = phoneField.value.replace(/[^0-9]/g, '').slice(0, 8);
                });
            }

            const tarifField = document.getElementById('tarif_journalier');
            if (tarifField) {
                tarifField.addEventListener('input', () => {
                    tarifField.value = tarifField.value.replace(/[^0-9]/g, '').slice(0, 6);
                });
            }
        }

        function validateRegisterForm() {
            clearRegisterErrors();

            const roleInput = document.getElementById('register-role');
            const selectedRoleOption = document.querySelector('.role-option.selected');
            if (selectedRoleOption) {
                roleInput.value = selectedRoleOption.dataset.role;
            }
            const role = roleInput.value;
            const emailField = document.getElementById('reg-email');
            const passwordField = document.getElementById('reg-password');
            const email = emailField.value.trim();
            const password = passwordField.value.trim();
            let valid = true;

            if (email === '') {
                showRegisterError(emailField, 'L’email est requis.');
                valid = false;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showRegisterError(emailField, 'Veuillez entrer un email valide.');
                valid = false;
            }

            if (password.length < 6) {
                showRegisterError(passwordField, 'Le mot de passe doit contenir au moins 6 caractères.');
                valid = false;
            }

            const checkLength = (field, fieldName, max = 50) => {
                const value = field.value.trim();
                if (value.length > max) {
                    showRegisterError(field, `${fieldName} ne doit pas dépasser ${max} caractères.`);
                    valid = false;
                    return false;
                }
                return true;
            };

            if (role === 'expert') {
                const nom = document.getElementById('nom');
                const prenom = document.getElementById('prenom');
                const domaine = document.getElementById('domaine');
                const niveau = document.getElementById('niveau_experience');
                const tarif = document.getElementById('tarif_journalier');
                const onlyLettersRegex = /^[A-Za-zÀ-ÖØ-öø-ÿ ]+$/;

                if (nom.value.trim() === '') {
                    showRegisterError(nom, 'Le nom est requis.');
                    valid = false;
                } else if (!onlyLettersRegex.test(nom.value.trim())) {
                    showRegisterError(nom, 'Le nom ne doit contenir que des lettres.');
                    valid = false;
                } else if (nom.value.trim().length > 10) {
                    showRegisterError(nom, 'Le nom doit contenir au maximum 10 caractères.');
                    valid = false;
                }

                if (prenom.value.trim() === '') {
                    showRegisterError(prenom, 'Le prénom est requis.');
                    valid = false;
                } else if (!onlyLettersRegex.test(prenom.value.trim())) {
                    showRegisterError(prenom, 'Le prénom ne doit contenir que des lettres.');
                    valid = false;
                } else if (prenom.value.trim().length > 10) {
                    showRegisterError(prenom, 'Le prénom doit contenir au maximum 10 caractères.');
                    valid = false;
                }

                if (domaine.value.trim() === '') {
                    showRegisterError(domaine, 'Le domaine est requis.');
                    valid = false;
                } else if (!onlyLettersRegex.test(domaine.value.trim())) {
                    showRegisterError(domaine, 'Le domaine ne doit contenir que des lettres.');
                    valid = false;
                } else if (domaine.value.trim().length > 10) {
                    showRegisterError(domaine, 'Le domaine doit contenir au maximum 10 caractères.');
                    valid = false;
                }

                if (niveau.value.trim() === '') {
                    showRegisterError(niveau, 'Le niveau d’expérience est requis.');
                    valid = false;
                } else if (!onlyLettersRegex.test(niveau.value.trim())) {
                    showRegisterError(niveau, 'Le niveau d’expérience ne doit contenir que des lettres.');
                    valid = false;
                } else if (niveau.value.trim().length > 10) {
                    showRegisterError(niveau, 'Le niveau d’expérience doit contenir au maximum 10 caractères.');
                    valid = false;
                }

                if (tarif.value.trim() === '') {
                    showRegisterError(tarif, 'Le tarif journalier est requis.');
                    valid = false;
                } else if (!/^\d+$/.test(tarif.value.trim())) {
                    showRegisterError(tarif, 'Le tarif journalier doit contenir uniquement des chiffres.');
                    valid = false;
                }
            }

            if (role === 'entreprise') {
                const nomEntreprise = document.getElementById('nom_entreprise');
                const secteur = document.getElementById('secteur_activite');
                const adresse = document.getElementById('adresse');
                const telephone = document.getElementById('telephone');

                const onlyLettersRegex = /^[A-Za-zÀ-ÖØ-öø-ÿ ]+$/;

                if (nomEntreprise.value.trim() === '') {
                    showRegisterError(nomEntreprise, 'Le nom de l’entreprise est requis.');
                    valid = false;
                } else if (!onlyLettersRegex.test(nomEntreprise.value.trim())) {
                    showRegisterError(nomEntreprise, 'Le nom de l’entreprise ne doit contenir que des lettres.');
                    valid = false;
                } else if (nomEntreprise.value.trim().length > 10) {
                    showRegisterError(nomEntreprise, 'Le nom de l’entreprise doit contenir au maximum 10 caractères.');
                    valid = false;
                }

                if (secteur.value.trim() === '') {
                    showRegisterError(secteur, 'Le secteur d’activité est requis.');
                    valid = false;
                } else if (!onlyLettersRegex.test(secteur.value.trim())) {
                    showRegisterError(secteur, 'Le secteur d’activité ne doit contenir que des lettres.');
                    valid = false;
                } else if (secteur.value.trim().length > 10) {
                    showRegisterError(secteur, 'Le secteur d’activité doit contenir au maximum 10 caractères.');
                    valid = false;
                }

                if (adresse.value.trim() === '') {
                    showRegisterError(adresse, 'L’adresse est requise.');
                    valid = false;
                }

                if (telephone.value.trim() === '') {
                    showRegisterError(telephone, 'Le téléphone est requis.');
                    valid = false;
                } else {
                    const telValue = telephone.value.trim();
                    if (!/^\d+$/.test(telValue)) {
                        showRegisterError(telephone, 'Le téléphone doit contenir uniquement des chiffres.');
                        valid = false;
                    } else if (telValue.length > 8) {
                        showRegisterError(telephone, 'Le téléphone ne doit pas dépasser 8 chiffres.');
                        valid = false;
                    }
                }

                checkLength(nomEntreprise, 'Nom de l’entreprise');
                checkLength(secteur, 'Secteur d’activité');
                checkLength(adresse, 'Adresse');
            }

            return valid;
        }

        attachRegisterFieldListeners();

    </script>
</body>
</html>