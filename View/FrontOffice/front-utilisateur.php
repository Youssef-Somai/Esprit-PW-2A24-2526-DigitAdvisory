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

if (strtolower($user['role'] ?? '') === 'expert') {
    header('Location: front-expert-profil.php');
    exit;
}

$displayName = htmlspecialchars($user['nom_entreprise'] ?: ($user['prenom'] ? $user['prenom'] . ' ' . $user['nom'] : $user['email']));
$companyEmail = htmlspecialchars($user['email']);
$companyName = htmlspecialchars($user['nom_entreprise'] ?? '');
$sector = htmlspecialchars($user['secteur_activite'] ?? '');
$address = htmlspecialchars($user['adresse'] ?? '');
$telephone = htmlspecialchars($user['telephone'] ?? '');
$avatarText = strtoupper(substr(trim($user['nom_entreprise'] ?: $user['email']), 0, 2));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Entreprise | Mon Profil</title>
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
        .menu-item:hover, .menu-item.active { background: rgba(37, 99, 235, 0.05); color: var(--primary); }
        .menu-item.active { border-left-color: var(--primary); }
        .menu-item i { width: 20px; text-align: center; font-size: 1.1rem; }
        .user-profile-widget { padding: 1rem 1.5rem; border-top: 1px solid var(--gray-light); display: flex; align-items: center; gap: 1rem; background: white; }
        .user-avatar { width: 40px; height: 40px; border-radius: 50%; background: var(--primary); color: white; display: flex; justify-content: center; align-items: center; font-weight: 600; }
        .main-content { flex: 1; margin-left: 280px; padding: 2rem; }
        .top-navbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; background: white; padding: 1rem 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); }
        .card { background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-sm); margin-bottom: 2rem; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--dark); }
        .form-control { width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--gray-light); border-radius: var(--radius); font-family: var(--font-main); font-size: 1rem; transition: var(--transition); outline: none; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header"><a href="index.php" class="logo" style="text-decoration: none;"><i class="fa-solid fa-chart-pie text-primary"></i> Digit Advisory</a></div>
            <div class="sidebar-menu">
                <a href="front-entreprise-dashboard.php" class="menu-item"><i class="fa-solid fa-house"></i> Vue d'ensemble</a>
                <a href="front-utilisateur.php" class="menu-item active"><i class="fa-solid fa-building"></i> Profil Entreprise</a>
                <a href="front-quiz.php" class="menu-item"><i class="fa-solid fa-list-check"></i> Questionnaire</a>
                <a href="front-portfolio.php" class="menu-item"><i class="fa-solid fa-folder-open"></i> Mon Portfolio</a>
                <a href="front-offres.php" class="menu-item"><i class="fa-solid fa-briefcase"></i> Mes Offres de Mission</a>
                <a href="front-certification.php" class="menu-item"><i class="fa-solid fa-award"></i> Certifications ISO</a>
                <a href="front-messagerie.php" class="menu-item"><i class="fa-solid fa-comments"></i> Messagerie</a>
            </div>
            <div class="user-profile-widget">
                <div class="user-avatar"><?php echo $avatarText; ?></div>
                <div><h4 style="font-size: 0.95rem; margin-bottom: 0.2rem;"><?php echo $displayName; ?></h4><span style="font-size: 0.8rem; color: var(--gray);">Compte Entreprise</span></div>
                <a href="login.php#register" style="margin-left: auto; color: var(--danger);"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
            </div>
        </aside>

        <main class="main-content">
            <div class="top-navbar">
                <h2 style="margin: 0; font-size: 1.5rem;">Profil de l'Entreprise</h2>
            </div>
            <section class="fade-in-up">
                <?php if (isset($_GET['updated'])): ?>
                    <div style="background-color: #d1fae5; color: #065f46; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: center; border: 1px solid #10b981; font-weight: 500;">
                        Profil mis à jour avec succès.
                    </div>
                <?php endif; ?>
                <div class="card hover-zoom">
                    <h3 style="margin-bottom: 1.5rem;"><i class="fa-solid fa-building text-primary"></i> Informations de l'Entreprise</h3>
                    <form action="../traitement/updateProfileUtilisateurTraitement.php" method="POST">
                        <input type="hidden" name="id_user" value="<?= $user['id_user'] ?>">
                        <input type="hidden" name="role" value="entreprise">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                            <div class="form-group">
                                <label>Raison Sociale</label>
                                <input type="text" name="nom_entreprise" class="form-control" value="<?= $companyName ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Email de contact</label>
                                <input type="email" name="email" class="form-control" value="<?= $companyEmail ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Secteur d'Activité</label>
                                <input type="text" name="secteur_activite" class="form-control" value="<?= $sector ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Téléphone</label>
                                <input type="text" name="telephone" class="form-control" value="<?= $telephone ?>">
                            </div>
                            <div class="form-group">
                                <label>Adresse</label>
                                <input type="text" name="adresse" class="form-control" value="<?= $address ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Modifier le mot de passe</label>
                                <input type="password" name="password" class="form-control" placeholder="Laisser vide pour conserver le mot de passe actuel">
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
                    <p style="color: var(--gray); margin-bottom: 1rem;">Cette action supprimera définitivement votre compte entreprise.</p>
                    <form action="../traitement/deleteProfileUtilisateurTraitement.php" method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer votre compte ?');">
                        <input type="hidden" name="id_user" value="<?= $user['id_user'] ?>">
                        <button type="submit" class="btn btn-outline" style="color: var(--danger); border-color: var(--danger);"><i class="fa-solid fa-trash"></i> Supprimer le compte</button>
                    </form>
                </div>
            </section>
        </main>
    </div>
</body>
</html>

