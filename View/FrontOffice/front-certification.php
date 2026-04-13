<?php
require_once __DIR__ . '/../../Controller/CertificatController.php';

$controller   = new CertificatController();
$certificats  = $controller->listCertificats();

// Couleurs et icônes alternées pour les cartes
$styles = [
    ['border' => 'var(--primary)',   'icon' => 'fa-shield-halved', 'iconClass' => 'text-primary'],
    ['border' => 'var(--secondary)', 'icon' => 'fa-chart-line',    'iconClass' => 'text-secondary'],
    ['border' => 'var(--success)',   'icon' => 'fa-leaf',          'iconClass' => 'text-accent'],
    ['border' => 'var(--accent)',    'icon' => 'fa-hard-hat',      'iconClass' => 'text-primary'],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Entreprise | Certifications ISO</title>
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
        .badge { padding: 0.25rem 0.75rem; border-radius: var(--radius-full); font-size: 0.85rem; font-weight: 500; display: inline-block;}
        .badge.success { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .badge.primary { background: rgba(37, 99, 235, 0.1); color: var(--primary); }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--gray);
        }
        .empty-state i { font-size: 3rem; margin-bottom: 1rem; color: var(--gray-light); }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header"><a href="index.php" class="logo" style="text-decoration: none;"><i class="fa-solid fa-chart-pie text-primary"></i> Digit Advisory</a></div>
            <div class="sidebar-menu">
                <a href="front-entreprise-dashboard.php" class="menu-item"><i class="fa-solid fa-house"></i> Vue d'ensemble</a>
                <a href="front-utilisateur.php" class="menu-item"><i class="fa-solid fa-building"></i> Profil Entreprise</a>
                <a href="front-quiz.php" class="menu-item"><i class="fa-solid fa-list-check"></i> Questionnaire</a>
                <a href="front-portfolio.php" class="menu-item"><i class="fa-solid fa-folder-open"></i> Mon Portfolio</a>
                <a href="front-offres.php" class="menu-item"><i class="fa-solid fa-briefcase"></i> Mes Offres de Mission</a>
                <a href="front-certification.php" class="menu-item active"><i class="fa-solid fa-award"></i> Certifications ISO</a>
                <a href="front-messagerie.php" class="menu-item"><i class="fa-solid fa-comments"></i> Messagerie</a>
            </div>
            <div class="user-profile-widget">
                <div class="user-avatar">TC</div>
                <div><h4 style="font-size: 0.95rem; margin-bottom: 0.2rem;">TechCorp SAS</h4><span style="font-size: 0.8rem; color: var(--gray);">Compte Entreprise</span></div>
                <a href="login.php" style="margin-left: auto; color: var(--danger);"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
            </div>
        </aside>

        <main class="main-content">
            <div class="top-navbar">
                <h2 style="margin: 0; font-size: 1.5rem;">Certifications ISO Recommandées</h2>
                <span class="badge primary" style="font-size: 0.95rem;"><?= count($certificats) ?> certification(s) disponible(s)</span>
            </div>

            <section class="fade-in-up">
                <?php if (empty($certificats)): ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-award"></i>
                        <h3>Aucune certification disponible</h3>
                        <p>Les certifications ISO seront affichées ici dès qu'elles seront configurées par l'administrateur.</p>
                    </div>
                <?php else: ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
                    <?php foreach ($certificats as $index => $cert):
                        $style = $styles[$index % count($styles)];
                    ?>
                    <div class="card hover-zoom interactive-card" style="border-left: 4px solid <?= $style['border'] ?>;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                            <h3><i class="fa-solid <?= $style['icon'] ?> <?= $style['iconClass'] ?>"></i> <?= htmlspecialchars($cert->getNorme()) ?></h3>
                            <span class="badge success">Recommandé</span>
                        </div>
                        <h4 style="font-size: 1.05rem; margin-bottom: 0.5rem;"><?= htmlspecialchars($cert->getTitre()) ?></h4>
                        <p style="font-size: 0.95rem; color: var(--gray); margin-bottom: 1rem;">
                            <?= htmlspecialchars($cert->getDescription() ?? 'Aucune description disponible.') ?>
                        </p>
                        <?php if ($cert->getOrganisme()): ?>
                        <p style="font-size: 0.85rem; color: var(--gray);">
                            <i class="fa-solid fa-building" style="margin-right: 0.3rem;"></i> Organisme : <strong><?= htmlspecialchars($cert->getOrganisme()) ?></strong>
                        </p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>
