<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controller/utilisateur_controller.php';

if (empty($_SESSION['user']['id_user'])) {
    header('Location: login.php');
    exit;
}

$controller = new UtilisateurController();
$user = $controller->getUserById((int) $_SESSION['user']['id_user']);
if (!$user) {
    header('Location: login.php');
    exit;
}

if (strtolower($user['role'] ?? '') !== 'expert') {
    header('Location: ../FrontOffice/front-utilisateur.php');
    exit;
}

$displayName = htmlspecialchars($user['prenom'] . ' ' . $user['nom']);
$expertEmail = htmlspecialchars($user['email']);
$nom = htmlspecialchars($user['nom'] ?? '');
$prenom = htmlspecialchars($user['prenom'] ?? '');
$domaine = htmlspecialchars($user['domaine'] ?? '');
$niveauExperience = htmlspecialchars($user['niveau_experience'] ?? '');
$tarifJournalier = htmlspecialchars($user['tarif_journalier'] ?? '');
$avatarText = strtoupper(substr(trim($user['prenom'] ?: $user['email']), 0, 2));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Expert | Mon Profil</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        body { background-color: #f1f5f9; }
        .dashboard-container { display: flex; min-height: 100vh; }
        .sidebar { width: 280px; background: white; box-shadow: var(--shadow-md); display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 100; transition: var(--transition); }
        .sidebar-header { padding: 1.5rem; border-bottom: 1px solid var(--gray-light); display: flex; align-items: center; }
        .sidebar-menu { padding: 1rem 0; flex: 1; overflow-y: auto; }
        .menu-item { padding: 0.75rem 1.5rem; display: flex; align-items: center; gap: 1rem; color: var(--gray); font-weight: 500; cursor: pointer; transition: var(--transition); border-left: 3px solid transparent; text-decoration: none;}
        .menu-item:hover, .menu-item.active { background: rgba(14, 165, 233, 0.05); color: var(--secondary); }
        .menu-item.active { border-left-color: var(--secondary); }
        .menu-item i { width: 20px; text-align: center; font-size: 1.1rem; }
        .user-profile-widget { padding: 1rem 1.5rem; border-top: 1px solid var(--gray-light); display: flex; align-items: center; gap: 1rem; background: white; }
        .user-avatar { width: 40px; height: 40px; border-radius: 50%; background: var(--secondary); color: white; display: flex; justify-content: center; align-items: center; font-weight: 600; }
        .main-content { flex: 1; margin-left: 280px; padding: 2rem; }
        .top-navbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; background: white; padding: 1rem 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); }
        .card { background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-sm); margin-bottom: 2rem; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--dark); }
        .form-control { width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--gray-light); border-radius: var(--radius); font-family: var(--font-main); font-size: 1rem; transition: var(--transition); outline: none; }
        .form-control:focus { border-color: var(--secondary); box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1); }
        .form-control.error { border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1); }
        .error-text { color: #dc2626; font-size: 0.85rem; margin-top: 0.35rem; min-height: 1.1rem; display: block; }
        .tag { display: inline-block; padding: 0.3rem 0.8rem; border-radius: var(--radius-full); font-size: 0.8rem; font-weight: 500; background: rgba(14,165,233,0.1); color: var(--secondary); margin-right: 0.5rem; margin-bottom: 0.5rem; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header"><a href="index.php" class="logo" style="text-decoration: none;"><i class="fa-solid fa-chart-pie text-secondary"></i> Digit Advisory</a></div>
            <div class="sidebar-menu">
                <a href="front-expert-dashboard.php" class="menu-item"><i class="fa-solid fa-house"></i> Vue d'ensemble</a>
                <a href="front-expert-profil.php" class="menu-item active"><i class="fa-solid fa-user"></i> Mon Profil Expert</a>
                <a href="front-expert-portfolio.php" class="menu-item"><i class="fa-solid fa-folder-open"></i> Portfolio & CV</a>
                <a href="front-expert-offres.php" class="menu-item"><i class="fa-solid fa-briefcase"></i> Explorer les Offres</a>
                <a href="front-expert-candidatures.php" class="menu-item"><i class="fa-solid fa-file-contract"></i> Mes Candidatures</a>
                <a href="front-expert-messagerie.php" class="menu-item"><i class="fa-solid fa-comments"></i> Messagerie</a>
            </div>
            <div class="user-profile-widget">
                <div class="user-avatar"><?php echo $avatarText; ?></div>
                <div><h4 style="font-size: 0.95rem; margin-bottom: 0.2rem;"><?php echo $displayName; ?></h4><span style="font-size: 0.8rem; color: var(--gray);">Consultant Expert</span></div>
                <a href="login.php#register" style="margin-left: auto; color: var(--danger);"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
            </div>
        </aside>

        <main class="main-content">
            <div class="top-navbar">
                <h2 style="margin: 0; font-size: 1.5rem;">Mon Profil Expert</h2>
            </div>
            <section class="fade-in-up">
                <?php if (isset($_GET['updated'])): ?>
                    <div style="background-color: #d1fae5; color: #065f46; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: center; border: 1px solid #10b981; font-weight: 500;">
                        Profil mis à jour avec succès.
                    </div>
                <?php endif; ?>
                <div class="card hover-zoom">
                    <h3 style="margin-bottom: 1.5rem;"><i class="fa-solid fa-id-card text-secondary"></i> Informations Personnelles</h3>
                    <form action="../traitement/updateProfileUtilisateurTraitement.php" method="POST" onsubmit="return validateExpertProfileForm();" novalidate>
                        <input type="hidden" name="id_user" value="<?= $user['id_user'] ?>">
                        <input type="hidden" name="role" value="expert">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                            <div class="form-group">
                                <label>Nom</label>
                                <input type="text" name="nom" class="form-control" value="<?= $nom ?>">
                                <span class="error-text" id="nom-error"></span>
                            </div>
                            <div class="form-group">
                                <label>Prénom</label>
                                <input type="text" name="prenom" class="form-control" value="<?= $prenom ?>">
                                <span class="error-text" id="prenom-error"></span>
                            </div>
                            <div class="form-group">
                                <label>Email professionnel</label>
                                <input type="email" name="email" class="form-control" value="<?= $expertEmail ?>">
                                <span class="error-text" id="email-error"></span>
                            </div>
                            <div class="form-group">
                                <label>Domaine d'expertise</label>
                                <input type="text" name="domaine" class="form-control" value="<?= $domaine ?>">
                                <span class="error-text" id="domaine-error"></span>
                            </div>
                            <div class="form-group">
                                <label>Niveau d'expérience</label>
                                <input type="text" name="niveau_experience" class="form-control" value="<?= $niveauExperience ?>">
                                <span class="error-text" id="niveau_experience-error"></span>
                            </div>
                            <div class="form-group">
                                <label>Tarif journalier (€)</label>
                                <input type="number" name="tarif_journalier" class="form-control" value="<?= $tarifJournalier ?>">
                                <span class="error-text" id="tarif_journalier-error"></span>
                            </div>
                            <div class="form-group" style="grid-column: span 2;">
                                <label>Modifier le mot de passe</label>
                                <input type="password" name="password" class="form-control" placeholder="Laisser vide pour conserver le mot de passe actuel">
                                <span class="error-text" id="password-error"></span>
                            </div>
                        </div>
                        <div style="display: flex; gap: 1rem; margin-top: 1.5rem; align-items: center;">
                            <button type="submit" class="btn btn-primary pulse-glow" style="border-radius: 25px;"><i class="fa-solid fa-pen"></i> Modifier le profil</button>
                            <a href="setup_face_id.php" class="btn" style="color: #0ea5e9; border: 1px solid #0ea5e9; background: white; text-decoration: none; border-radius: 25px; display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; font-weight: 600;"><i class="fa-solid fa-face-smile"></i> Configurer Face ID</a>
                        </div>
                    </form>
                </div>

                <div class="card hover-zoom">
                    <h3 style="margin-bottom: 1rem;"><i class="fa-solid fa-trash text-danger"></i> Supprimer le compte</h3>
                    <p style="color: var(--gray); margin-bottom: 1rem;">Cette action supprimera définitivement votre compte expert.</p>
                    <form action="../traitement/deleteProfileUtilisateurTraitement.php" method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer votre compte ?');">
                        <input type="hidden" name="id_user" value="<?= $user['id_user'] ?>">
                        <button type="submit" class="btn btn-outline" style="color: var(--danger); border-color: var(--danger);"><i class="fa-solid fa-trash"></i> Supprimer le compte</button>
                    </form>
                </div>
            </section>
        </main>
    </div>
    <script>
        function clearExpertErrors() {
            document.querySelectorAll('.error-text').forEach(el => el.textContent = '');
            document.querySelectorAll('.form-control').forEach(el => el.classList.remove('error'));
        }

        function showExpertError(input, message) {
            input.classList.add('error');
            const errorEl = document.getElementById(input.name + '-error');
            if (errorEl) {
                errorEl.textContent = message;
            }
        }

        function validateExpertProfileForm() {
            clearExpertErrors();
            const form = document.querySelector('form[action="../traitement/updateProfileUtilisateurTraitement.php"]');
            let valid = true;

            const fields = [
                { name: 'nom', label: 'Nom' },
                { name: 'prenom', label: 'Prénom' },
                { name: 'email', label: 'Email professionnel', type: 'email' },
                { name: 'domaine', label: "Domaine d'expertise" },
                { name: 'niveau_experience', label: "Niveau d'expérience" },
                { name: 'tarif_journalier', label: 'Tarif journalier', type: 'number' }
            ];

            fields.forEach(field => {
                const input = form.elements[field.name];
                if (!input) {
                    return;
                }
                const value = input.value.trim();

                if (value === '') {
                    showExpertError(input, `${field.label} est requis.`);
                    valid = false;
                    return;
                }

                if (field.type === 'email') {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(value)) {
                        showExpertError(input, 'Email professionnel invalide.');
                        valid = false;
                    }
                }

                if (field.type === 'number') {
                    const number = parseFloat(value);
                    if (isNaN(number) || number <= 0) {
                        showExpertError(input, 'Tarif journalier doit être un nombre positif.');
                        valid = false;
                    }
                }
            });

            if (!valid) {
                const firstError = form.querySelector('.form-control.error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }

            return valid;
        }
    </script>
</body>
</html>

