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
                <a href="login.php#register" style="margin-left: auto; color: var(--danger);"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
            </div>
        </aside>

        <main class="main-content">
            <div class="top-navbar">
                <h2 style="margin: 0; font-size: 1.5rem;">Certifications ISO Recommandées</h2>
            </div>
            <section class="fade-in-up">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
                    <div class="card hover-zoom interactive-card" style="border-left: 4px solid var(--primary);">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                            <h3><i class="fa-solid fa-shield-halved text-primary"></i> ISO 27001</h3>
                            <span class="badge success">Fortement Recommandé</span>
                        </div>
                        <p style="font-size: 0.95rem; color: var(--gray); margin-bottom: 1rem;">Management de la sécurité de l'information. Protection des données sensibles et gestion des risques IT.</p>
                        <h4 style="font-size: 0.9rem; margin-bottom: 0.5rem;">Pourquoi c'est adapté à votre profil :</h4>
                        <ul style="font-size: 0.85rem; color: var(--gray); list-style-type: disc; margin-left: 1.5rem; margin-bottom: 1rem;">
                            <li>Score Quiz Cybersécurité: <strong style="color: var(--primary);">Élevé</strong></li>
                            <li>Secteur IT avec manipulation de données sensibles</li>
                            <li>Croissance digitale forte identifiée</li>
                        </ul>
                        <h4 style="font-size: 0.9rem; margin-bottom: 0.5rem;">Avantages :</h4>
                        <ul style="font-size: 0.85rem; color: var(--gray); list-style-type: '✅ '; margin-left: 1rem;">
                            <li>Renforce la confiance des clients</li>
                            <li>Réduit les risques de failles</li>
                            <li>Conformité réglementaire (RGPD)</li>
                        </ul>
                    </div>

                    <div class="card hover-zoom interactive-card" style="border-left: 4px solid var(--secondary);">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                            <h3><i class="fa-solid fa-chart-line text-secondary"></i> ISO 9001</h3>
                            <span class="badge primary">Suggéré</span>
                        </div>
                        <p style="font-size: 0.95rem; color: var(--gray); margin-bottom: 1rem;">Système de management de la qualité. Amélioration continue des processus internes.</p>
                        <h4 style="font-size: 0.9rem; margin-bottom: 0.5rem;">Pourquoi c'est adapté à votre profil :</h4>
                        <ul style="font-size: 0.85rem; color: var(--gray); list-style-type: disc; margin-left: 1.5rem; margin-bottom: 1rem;">
                            <li>Optimisation des processus internes demandée</li>
                            <li>Volonté de structurer la croissance</li>
                        </ul>
                        <h4 style="font-size: 0.9rem; margin-bottom: 0.5rem;">Avantages :</h4>
                        <ul style="font-size: 0.85rem; color: var(--gray); list-style-type: '✅ '; margin-left: 1rem;">
                            <li>Efficacité opérationnelle accrue</li>
                            <li>Satisfaction client améliorée</li>
                        </ul>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>

