<?php
require_once '../../Controller/PortfolioController.php';
require_once '../../Controller/ElementPortfolioController.php';

$portfolioC = new PortfolioController();
$elementC = new ElementPortfolioController();

// Suppression
if (isset($_GET['action'])) {
    if ($_GET['action'] == 'deleteElement' && isset($_GET['id'])) {
        $elementC->deleteElement($_GET['id']);
        header('Location: back-portfolio.php');
        exit();
    }
    if ($_GET['action'] == 'deletePortfolio' && isset($_GET['id'])) {
        $portfolioC->deletePortfolio($_GET['id']);
        header('Location: back-portfolio.php');
        exit();
    }
}

$portfoliosStmt = $portfolioC->listPortfolios();
$portfolios = [];
while($row = $portfoliosStmt->fetch()) {
    $portfolios[] = $row;
}
$elements = $elementC->listAllElements();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Back Office | Gestion Portfolios</title>
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
        .badge.primary { background: rgba(37, 99, 235, 0.1); color: var(--primary); }
        .btn-sm { padding: 0.4rem 0.8rem; font-size: 0.85rem; border: none; cursor:pointer; text-decoration: none; border-radius: 4px; display: inline-block; }
        .btn-outline { background: white; border: 1px solid var(--gray-light); }
        .btn-danger { color: white; background: var(--danger); border: none; }
    </style>
</head>
<body class="admin-theme">
    <div class="dashboard-container">
        <aside class="sidebar admin-sidebar slide-in-right">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fa-solid fa-user-shield text-accent"></i> Admin Panel
                </div>
            </div>
            <div class="sidebar-menu">
                <a href="back-utilisateur.php" class="menu-item"><i class="fa-solid fa-users"></i> Gestion Utilisateurs</a>
                <a href="back-quiz.php" class="menu-item"><i class="fa-solid fa-list-check"></i> Gestion Quiz</a>
                <a href="back-portfolio.php" class="menu-item active"><i class="fa-solid fa-folder-open"></i> Gestion Portfolios</a>
                <a href="back-offres.php" class="menu-item"><i class="fa-solid fa-briefcase"></i> Gestion Offres</a>
                <a href="back-certification.php" class="menu-item"><i class="fa-solid fa-award"></i> Gestion Certifications</a>
                <a href="back-messagerie.php" class="menu-item"><i class="fa-solid fa-comments"></i> Gestion Messagerie</a>
            </div>
            <div class="user-profile-widget">
                <div class="user-avatar">AD</div>
                <div><h4 style="font-size: 0.95rem; margin-bottom: 0.2rem; color: white;">Admin Système</h4><span style="font-size: 0.8rem; color: var(--gray-light);">Admin</span></div>
            </div>
                <a href="../../View/FrontOffice/login.php" style="margin-left: auto; color: var(--danger);"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
        </aside>

        <main class="main-content">
            <div class="top-navbar">
                <h2 style="margin: 0; font-size: 1.5rem;">Administration - Rôle Superviseur</h2>
                <span class="badge warning" style="font-size: 1rem; background: #fef08a; color: #a16207; padding: 0.5rem 1rem;"><i class="fa-solid fa-lock"></i> Espace Sécurisé Admin</span>
            </div>

            <section class="fade-in-up">
                <div style="display: flex; justify-content: space-between; align-items: center;" class="mb-2">
                    <h2>Gestion Globale des Portfolios (<?= count($portfolios) ?>)</h2>
                </div>

                <div class="card admin-card hover-zoom" style="margin-bottom: 3rem;">
                    <h3 style="margin-bottom: 1rem;">Portfolios (Conteneurs)</h3>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Titre du Portfolio</th>
                                <th>Description</th>
                                <th>Créé le</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($portfolios as $p): ?>
                            <tr>
                                <td><?= $p['id_portfolio'] ?></td>
                                <td><strong><?= htmlspecialchars($p['titre_portfolio']) ?></strong></td>
                                <td><?= htmlspecialchars(substr($p['description_portfolio'], 0, 50)) ?>...</td>
                                <td><?= date('d/m/Y', strtotime($p['date_creation'])) ?></td>
                                <td>
                                    <a href="?action=deletePortfolio&id=<?= $p['id_portfolio'] ?>" class="btn-sm btn-danger" onclick="return confirm('Supprimer ce portfolio et tous ses éléments ?');"><i class="fa-solid fa-ban"></i> Supprimer</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($portfolios)): ?>
                                <tr><td colspan="5" style="text-align:center; padding: 2rem;">Aucun portfolio existant.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="card admin-card hover-zoom">
                    <h3 style="margin-bottom: 1rem;">Base de données des compétences / Projets (<?= count($elements) ?>)</h3>
                    <table class="data-table mt-1">
                        <thead>
                            <tr>
                                <th>ID Elément</th>
                                <th>Portfolio Parent</th>
                                <th>Type</th>
                                <th>Titre</th>
                                <th>Niveau / Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($elements as $el): ?>
                            <tr>
                                <td><?= $el['id_element'] ?></td>
                                <td><?= htmlspecialchars($el['titre_portfolio']) ?></td>
                                <td><span class="badge <?= $el['type_element'] == 'projet' ? 'primary' : 'success' ?>"><?= ucfirst($el['type_element']) ?></span></td>
                                <td><?= htmlspecialchars($el['titre']) ?></td>
                                <td>
                                    <?php if($el['type_element'] == 'projet') echo htmlspecialchars($el['statut']); else echo htmlspecialchars($el['niveau']); ?>
                                </td>
                                <td>
                                    <a href="?action=deleteElement&id=<?= $el['id_element'] ?>" class="btn-sm btn-danger" onclick="return confirm('Supprimer cet élément ?');"><i class="fa-solid fa-ban"></i> Supprimer</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($elements)): ?>
                                <tr><td colspan="6" style="text-align:center; padding: 2rem;">Aucun élément existant.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
