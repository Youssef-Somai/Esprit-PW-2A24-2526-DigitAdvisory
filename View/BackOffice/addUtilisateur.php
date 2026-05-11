<?php
session_start();
if (!isset($_SESSION['user']['id_user']) || strtolower($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: ../FrontOffice/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Utilisateur</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .container { max-width: 600px; margin: 2rem auto; padding: 2rem; background: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
        .form-control { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .form-control.error { border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12); }
        .error-text { color: #dc2626; font-size: 0.85rem; margin-top: 0.35rem; display: block; }
        .btn { padding: 0.75rem 1.5rem; background: #2563eb; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; width: 100%; margin-top: 1rem; }
        .btn:hover { background: #1d4ed8; }
        .role-selector { display: flex; gap: 1rem; margin-bottom: 1.5rem; }
        .role-option { flex: 1; padding: 1rem; text-align: center; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; }
        .role-option.selected { border-color: #2563eb; background: rgba(37,99,235,0.1); color: #2563eb; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <a href="back-utilisateur.php" style="color: #2563eb; text-decoration: none; margin-bottom: 1rem; display: inline-block;">&larr; Retour</a>
        <h2 style="margin-top:0;">Ajouter un nouvel utilisateur</h2>
        
        <form id="register" action="../traitement/createUtilisateurTraitement.php" method="POST" onsubmit="return validateRegisterForm();" novalidate>
            <div class="role-selector">
                <div class="role-option selected" data-role="entreprise" onclick="selectRole('entreprise')">Entreprise</div>
                <div class="role-option" data-role="expert" onclick="selectRole('expert')">Expert</div>
            </div>
            
            <input type="hidden" name="role" id="register-role" value="entreprise">

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" id="reg-email" class="form-control" placeholder="exemple@domaine.com">
                <span class="error-text" id="error-reg-email"></span>
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" id="reg-password" class="form-control" placeholder="Mot de passe">
                <span class="error-text" id="error-reg-password"></span>
            </div>

            <!-- Entreprise -->
            <div id="entreprise-fields">
                <div class="form-group">
                    <label>Nom de l'entreprise</label>
                    <input type="text" name="nom_entreprise" id="nom_entreprise" class="form-control" placeholder="Nom de l'entreprise" maxlength="10">
                </div>
                <div class="form-group">
                    <label>Secteur d'activité</label>
                    <input type="text" name="secteur_activite" id="secteur_activite" class="form-control" placeholder="Secteur d'activité" maxlength="10">
                </div>
                <div class="form-group">
                    <label>Adresse</label>
                    <input type="text" name="adresse" id="adresse" class="form-control" placeholder="Adresse complète">
                </div>
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="text" name="telephone" id="telephone" class="form-control" placeholder="Téléphone" maxlength="8">
                </div>
            </div>

            <!-- Expert -->
            <div id="expert-fields" style="display:none;">
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="nom" id="nom" class="form-control" placeholder="Nom" maxlength="10">
                </div>
                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" name="prenom" id="prenom" class="form-control" placeholder="Prénom" maxlength="10">
                </div>
                <div class="form-group">
                    <label>Domaine</label>
                    <input type="text" name="domaine" id="domaine" class="form-control" placeholder="Domaine d'expertise" maxlength="10">
                </div>
                <div class="form-group">
                    <label>Niveau d'expérience</label>
                    <input type="text" name="niveau_experience" id="niveau_experience" class="form-control" placeholder="Ex: Junior, Senior..." maxlength="10">
                </div>
                <div class="form-group">
                    <label>Tarif journalier</label>
                    <input type="text" name="tarif_journalier" id="tarif_journalier" class="form-control" placeholder="Tarif journalier" maxlength="6">
                </div>
            </div>

            <button type="submit" class="btn">Enregistrer l'utilisateur</button>
        </form>
    </div>

    <script>
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

            const role = document.getElementById('register-role').value;
            const emailField = document.getElementById('reg-email');
            const passwordField = document.getElementById('reg-password');
            const email = emailField.value.trim();
            const password = passwordField.value.trim();
            let valid = true;

            if (email === '') {
                showRegisterError(emailField, 'L\'email est requis.');
                valid = false;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showRegisterError(emailField, 'Veuillez entrer un email valide.');
                valid = false;
            }

            if (password.length < 6) {
                showRegisterError(passwordField, 'Le mot de passe doit contenir au moins 6 caractères.');
                valid = false;
            }

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
                }

                if (prenom.value.trim() === '') {
                    showRegisterError(prenom, 'Le prénom est requis.');
                    valid = false;
                } else if (!onlyLettersRegex.test(prenom.value.trim())) {
                    showRegisterError(prenom, 'Le prénom ne doit contenir que des lettres.');
                    valid = false;
                }

                if (domaine.value.trim() === '') {
                    showRegisterError(domaine, 'Le domaine est requis.');
                    valid = false;
                } else if (!onlyLettersRegex.test(domaine.value.trim())) {
                    showRegisterError(domaine, 'Le domaine ne doit contenir que des lettres.');
                    valid = false;
                }

                if (niveau.value.trim() === '') {
                    showRegisterError(niveau, 'Le niveau d\'expérience est requis.');
                    valid = false;
                } else if (!onlyLettersRegex.test(niveau.value.trim())) {
                    showRegisterError(niveau, 'Le niveau d\'expérience ne doit contenir que des lettres.');
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
                    showRegisterError(nomEntreprise, 'Le nom de l\'entreprise est requis.');
                    valid = false;
                } else if (!onlyLettersRegex.test(nomEntreprise.value.trim())) {
                    showRegisterError(nomEntreprise, 'Le nom de l\'entreprise ne doit contenir que des lettres.');
                    valid = false;
                }

                if (secteur.value.trim() === '') {
                    showRegisterError(secteur, 'Le secteur d\'activité est requis.');
                    valid = false;
                } else if (!onlyLettersRegex.test(secteur.value.trim())) {
                    showRegisterError(secteur, 'Le secteur d\'activité ne doit contenir que des lettres.');
                    valid = false;
                }

                if (adresse.value.trim() === '') {
                    showRegisterError(adresse, 'L\'adresse est requise.');
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
            }

            return valid;
        }

        attachRegisterFieldListeners();
    </script>
</body>
</html>
