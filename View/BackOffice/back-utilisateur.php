<?php
session_start();

if (!isset($_SESSION['user']['id_user']) || strtolower($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: ../FrontOffice/login.php');
    exit;
}

require_once __DIR__ . '/../../Controller/utilisateur_controller.php';
$controller = new UtilisateurController();
$users = $controller->listeUsers();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Back Office | Gestion Utilisateurs</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .sidebar { background: var(--dark); color: white; }
        .sidebar .menu-item { color: var(--gray-light); }
        .sidebar .menu-item:hover, .sidebar .menu-item.active { background: rgba(255,255,255,0.1); color: white; border-left-color: var(--accent); }
        .sidebar-header { border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header .logo { color: white; }
        .user-profile-widget { background: rgba(0,0,0,0.2); border-top: 1px solid rgba(255,255,255,0.1); }
        .dashboard-container { display: flex; min-height: 100vh; }
        .sidebar { width: 280px; display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 100; transition: var(--transition); }
        .sidebar-header { padding: 1.5rem; display: flex; align-items: center; }
        .sidebar-menu { padding: 1rem 0; flex: 1; overflow-y: auto; }
        .menu-item { padding: 0.75rem 1.5rem; display: flex; align-items: center; gap: 1rem; font-weight: 500; cursor: pointer; transition: var(--transition); border-left: 3px solid transparent; text-decoration: none; }
        .menu-item i { width: 20px; text-align: center; font-size: 1.1rem; }
        .user-profile-widget { padding: 1rem 1.5rem; display: flex; align-items: center; gap: 1rem; }
        .user-avatar { width: 40px; height: 40px; border-radius: 50%; background: var(--accent); color: white; display: flex; justify-content: center; align-items: center; font-weight: 600; }
        .main-content { flex: 1; margin-left: 280px; padding: 2rem; background: #f1f5f9; min-height: 100vh; }
        .top-navbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; background: white; padding: 1rem 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); }
        .card { background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-sm); margin-bottom: 2rem; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--gray-light); }
        .data-table th { color: var(--gray); font-weight: 500; }
        .badge { padding: 0.25rem 0.75rem; border-radius: var(--radius-full); font-size: 0.85rem; font-weight: 500; display: inline-block;}
        .badge.success { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .badge.warning { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
        .badge.primary { background: rgba(37, 99, 235, 0.1); color: var(--primary); }
        .btn-sm { padding: 0.4rem 0.8rem; font-size: 0.85rem; }
    </style>
</head>
<body class="admin-theme">
    <div class="dashboard-container">
        <!-- Sidebar ADMIN -->
        <aside class="sidebar admin-sidebar slide-in-right">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fa-solid fa-user-shield text-accent"></i>
                    Admin Panel
                </div>
            </div>
            
            <div class="sidebar-menu">
                <a href="back-utilisateur.php" class="menu-item active"><i class="fa-solid fa-users"></i> Gestion Utilisateurs</a>
                <a href="back-quiz.php" class="menu-item"><i class="fa-solid fa-list-check"></i> Gestion Quiz</a>
                <a href="back-portfolio.php" class="menu-item"><i class="fa-solid fa-folder-open"></i> Gestion Portfolios</a>
                <a href="back-offres.php" class="menu-item"><i class="fa-solid fa-briefcase"></i> Gestion Offres</a>
                <a href="back-certification.php" class="menu-item"><i class="fa-solid fa-award"></i> Gestion Certifications</a>
                <a href="back-messagerie.php" class="menu-item"><i class="fa-solid fa-comments"></i> Gestion Messagerie</a>
            </div>

            <div class="user-profile-widget">
                <div class="user-avatar">AD</div>
                <div>
                    <h4 style="font-size: 0.95rem; margin-bottom: 0.2rem; color: white;">Admin Système</h4>
                    <span style="font-size: 0.8rem; color: var(--gray-light);">Admin</span>
                </div>
                <a href="../../View/FrontOffice/login.php" style="margin-left: auto; color: var(--danger);"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
            </div>
        </aside>

        <main class="main-content">
            <div class="top-navbar">
                <h2 style="margin: 0; font-size: 1.5rem;">Administration - Rôle Superviseur</h2>
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <span class="badge warning" style="font-size: 1rem;"><i class="fa-solid fa-lock"></i> Espace Sécurisé Admin</span>
                </div>
            </div>

            <!-- MODULE: Utilisateur (Admin) -->
            <section class="fade-in-up">
                <div style="display: flex; justify-content: space-between; align-items: center;" class="mb-2">
                    <h2>Gestion des Utilisateurs</h2>
                    <button class="btn btn-primary"><i class="fa-solid fa-plus"></i> Ajouter Utilisateur</button>
                </div>

                <div class="card admin-card hover-zoom">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                        <input type="text" placeholder="Rechercher..." style="padding: 0.5rem; border: 1px solid var(--gray-light); border-radius: var(--radius); width: 250px;">
                        <select style="padding: 0.5rem; border: 1px solid var(--gray-light); border-radius: var(--radius);">
                            <option>Tous les rôles</option>
                            <option>Entreprise</option>
                            <option>Expert</option>
                        </select>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom / Raison Sociale</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Domaine / Secteur</th>
                                <th>Adresse</th>
                                <th>Téléphone / Tarif</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td>#U<?php echo htmlspecialchars($u['id_user']); ?></td>
                                    <td><?php echo $u['role'] === 'expert' ? htmlspecialchars(trim(($u['nom'] ?? '') . ' ' . ($u['prenom'] ?? ''))) : htmlspecialchars($u['nom_entreprise'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td>
                                        <?php if (strtolower($u['role']) === 'expert'): ?>
                                            <span class="badge warning" style="background:rgba(14,165,233,0.1); color:var(--secondary);">Expert</span>
                                        <?php elseif (strtolower($u['role']) === 'entreprise'): ?>
                                            <span class="badge primary">Entreprise</span>
                                        <?php else: ?>
                                            <span class="badge success"><?php echo htmlspecialchars(ucfirst($u['role'])); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars(strtolower($u['role']) === 'expert' ? ($u['domaine'] ?? '') : ($u['secteur_activite'] ?? '')); ?></td>
                                    <td><?php echo htmlspecialchars($u['adresse'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars(strtolower($u['role']) === 'expert' ? ($u['tarif_journalier'] ?? '') : ($u['telephone'] ?? '')); ?></td>
                                    <td>
                                        <?php if (strtolower($u['statut_compte']) === 'actif'): ?>
                                            <span class="badge success">Actif</span>
                                        <?php elseif (strtolower($u['statut_compte']) === 'en attente'): ?>
                                            <span class="badge warning">En attente</span>
                                        <?php else: ?>
                                            <span class="badge primary"><?php echo htmlspecialchars(ucfirst($u['statut_compte'])); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="readUtilisateur.php?id_user=<?php echo urlencode($u['id_user']); ?>" class="btn btn-outline btn-sm"><i class="fa-solid fa-eye"></i></a>
                                        <a href="updateUtilisateur.php?id_user=<?php echo urlencode($u['id_user']); ?>" class="btn btn-outline btn-sm"><i class="fa-solid fa-pen"></i></a>
                                        <form action="../traitement/deleteUtilisateurTraitement.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="id_user" value="<?php echo htmlspecialchars($u['id_user']); ?>">
                                            <button type="submit" class="btn btn-outline btn-sm" style="color:var(--danger); border-color:var(--danger);" onclick="return confirm('Supprimer cet utilisateur ?');"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
