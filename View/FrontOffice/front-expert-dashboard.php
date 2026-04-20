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
if (!$user || strtolower($user['role'] ?? '') !== 'expert') {
    header('Location: login.php');
    exit;
}

$displayName = htmlspecialchars(trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? '')) ?: $user['email']);
$avatarText = strtoupper(substr(trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? '')) ?: $user['email'], 0, 2));
$expertEmail = htmlspecialchars($user['email'] ?? '');
$domaine = htmlspecialchars($user['domaine'] ?? '');
$niveauExperience = htmlspecialchars($user['niveau_experience'] ?? '');
$tarifJournalier = htmlspecialchars($user['tarif_journalier'] ?? '');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Expert | Dashboard</title>
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
        .badge { padding: 0.25rem 0.75rem; border-radius: var(--radius-full); font-size: 0.85rem; font-weight: 500; display: inline-block; }
        .badge.primary { background: rgba(37, 99, 235, 0.1); color: var(--primary); }
        .badge.warning { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
        .badge.success { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .profile-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; }
        .profile-card { padding: 1.5rem; border-radius: var(--radius-lg); background: white; box-shadow: var(--shadow-sm); }
        .profile-card h3 { margin-top: 0; }
        .profile-actions { display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 1rem; }
        .btn-secondary { background: #f8fafc; border: 1px solid #d1d5db; color: #111827; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar slide-in-right" style="animation-duration: 0.4s;">
            <div class="sidebar-header"><a href="index.php" class="logo" style="text-decoration: none;"><i class="fa-solid fa-chart-pie text-secondary"></i> Digit Advisory</a></div>
            <div class="sidebar-menu">
                <a href="front-expert-dashboard.php" class="menu-item active"><i class="fa-solid fa-house"></i> Vue d'ensemble</a>
                <a href="front-expert-profil.php" class="menu-item"><i class="fa-solid fa-user"></i> Mon Profil Expert</a>
                <a href="front-expert-portfolio.php" class="menu-item"><i class="fa-solid fa-folder-open"></i> Portfolio & CV</a>
                <a href="front-expert-offres.php" class="menu-item"><i class="fa-solid fa-briefcase"></i> Explorer les Offres</a>
                <a href="front-expert-candidatures.php" class="menu-item"><i class="fa-solid fa-file-contract"></i> Mes Candidatures</a>
                <a href="front-expert-messagerie.php" class="menu-item"><i class="fa-solid fa-comments"></i> Messagerie</a>
            </div>
            <div class="user-profile-widget">
                <div class="user-avatar"><?= $avatarText ?></div>
                <div><h4 style="font-size: 0.95rem; margin-bottom: 0.2rem; color: var(--gray);"><?= $displayName ?></h4><span style="font-size: 0.8rem; color: var(--gray);">Consultant Expert</span></div>
                <a href="login.php#register" style="margin-left: auto; color: var(--danger);"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
            </div>
        </aside>

        <main class="main-content">
            <div class="top-navbar fade-in-up">
                <div>
                    <h2 style="margin: 0; font-size: 1.8rem; color: var(--dark);">Bonjour, <?= $displayName ?> <span style="font-size:1.5rem;">🎯</span></h2>
                    <p style="color:var(--gray); font-size:0.9rem;">Votre espace expert est prêt. Vous pouvez modifier votre profil ou supprimer votre compte.</p>
                </div>
            </div>

            <section class="fade-in-up delay-1">
                <div class="profile-grid">
                    <div class="profile-card">
                        <h3>Profil Expert</h3>
                        <p><strong>Nom :</strong> <?= htmlspecialchars(trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? '')) ?: 'Non précisé') ?></p>
                        <p><strong>Email :</strong> <?= $expertEmail ?></p>
                        <p><strong>Domaine :</strong> <?= $domaine ?: 'Non précisé' ?></p>
                        <p><strong>Niveau d'expérience :</strong> <?= $niveauExperience ?: 'Non précisé' ?></p>
                        <p><strong>Tarif journalier :</strong> <?= $tarifJournalier ? htmlspecialchars($tarifJournalier) . ' €' : 'Non précisé' ?></p>
                        <div class="profile-actions">
                            <a href="front-expert-profil.php" class="btn btn-primary"><i class="fa-solid fa-pen"></i> Modifier le profil</a>
                            <form action="../traitement/deleteProfileUtilisateurTraitement.php" method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer votre compte ?');" style="margin:0;">
                                <input type="hidden" name="id_user" value="<?= $user['id_user'] ?>">
                                <button type="submit" class="btn btn-secondary" style="color: var(--danger); border-color: var(--danger);"><i class="fa-solid fa-trash"></i> Supprimer le compte</button>
                            </form>
                        </div>
                    </div>
                    <div class="profile-card">
                        <h3>Résumé des activités</h3>
                        <div class="stat-grid">
                            <div class="stat-card hover-zoom interactive-card">
                                <div class="stat-icon" style="background: rgba(14,165,233,0.1); color: var(--secondary);"><i class="fa-solid fa-paper-plane"></i></div>
                                <div><h3 style="font-size: 1.5rem; margin:0;">7</h3><p style="color: var(--gray); font-size: 0.9rem; margin:0;">Candidatures envoyées</p></div>
                            </div>
                            <div class="stat-card hover-zoom interactive-card">
                                <div class="stat-icon" style="background: rgba(16,185,129,0.1); color: var(--success);"><i class="fa-solid fa-handshake"></i></div>
                                <div><h3 style="font-size: 1.5rem; margin:0;">3</h3><p style="color: var(--gray); font-size: 0.9rem; margin:0;">Missions en cours</p></div>
                            </div>
                            <div class="stat-card hover-zoom interactive-card">
                                <div class="stat-icon" style="background: rgba(245,158,11,0.1); color: var(--warning);"><i class="fa-solid fa-clock"></i></div>
                                <div><h3 style="font-size: 1.5rem; margin:0;">2</h3><p style="color: var(--gray); font-size: 0.9rem; margin:0;">En attente de réponse</p></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card interactive-card hover-zoom" style="display: flex; justify-content: space-between; align-items: center; border-left: 4px solid var(--success); background: linear-gradient(90deg, white, #ecfdf5);">
                    <div>
                        <h3 style="margin-bottom: 0.5rem; color: var(--dark);"><i class="fa-solid fa-circle-check text-success"></i> Nouvelle réponse</h3>
                        <p style="color: var(--gray); font-size: 0.95rem;">Votre candidature pour <strong>"Audit RGPD Complet"</strong> a été acceptée. Consultez votre messagerie pour les détails.</p>
                    </div>
                    <a href="front-expert-messagerie.php" class="btn btn-outline" style="border-color: var(--success); color: var(--success);">Accéder à la messagerie</a>
                </div>

                <div class="card">
                    <h3 style="margin-bottom: 1rem;">Dernières offres correspondant à votre profil</h3>
                    <table class="data-table">
                        <thead><tr><th>Offre</th><th>Entreprise</th><th>Budget</th><th>Action</th></tr></thead>
                        <tbody>
                            <tr>
                                <td><strong>Conseil ISO 27001</strong></td>
                                <td>ABC Startups</td>
                                <td>Sur devis</td>
                                <td><a href="front-expert-offres.php" class="btn btn-outline" style="padding:0.4rem 0.8rem; font-size:0.85rem;">Voir l'offre</a></td>
                            </tr>
                            <tr>
                                <td><strong>Audit Cloud AWS</strong></td>
                                <td>DataFlow Inc.</td>
                                <td>~800€/J</td>
                                <td><a href="front-expert-offres.php" class="btn btn-outline" style="padding:0.4rem 0.8rem; font-size:0.85rem;">Voir l'offre</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>
</html>

