<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Entreprise | Offres de Mission</title>
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
        .card { background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-sm); margin-bottom: 1.5rem; }
        .badge { padding: 0.25rem 0.75rem; border-radius: var(--radius-full); font-size: 0.85rem; font-weight: 500; display: inline-block;}
        .badge.primary { background: rgba(37, 99, 235, 0.1); color: var(--primary); }
        .badge.success { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .badge.warning { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
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
                <a href="front-offres.php" class="menu-item active"><i class="fa-solid fa-briefcase"></i> Mes Offres de Mission</a>
                <a href="front-certification.php" class="menu-item"><i class="fa-solid fa-award"></i> Certifications ISO</a>
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
                <h2 style="margin: 0; font-size: 1.5rem;">Gestion des Offres de Mission</h2>
                <button class="btn btn-primary pulse-glow"><i class="fa-solid fa-plus"></i> Créer une Offre</button>
            </div>
            <section class="fade-in-up">
                <!-- Offre 1 -->
                <div class="card hover-zoom interactive-card" style="border-left: 4px solid var(--primary);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 0.5rem;">
                                <h3 style="font-size: 1.1rem;">Audit RGPD Complet</h3>
                                <span class="badge success">3 Candidatures</span>
                            </div>
                            <p style="font-size: 0.9rem; color: var(--gray); margin-bottom: 0.75rem;">Mise en conformité RGPD de l'infrastructure cloud. Durée estimée: 2 mois.</p>
                            <div style="display: flex; gap: 1.5rem; font-size: 0.85rem; color: var(--gray);">
                                <span><i class="fa-solid fa-location-dot"></i> Distanciel</span>
                                <span><i class="fa-solid fa-wallet"></i> TJM: 600€</span>
                                <span><i class="fa-solid fa-calendar"></i> Publiée il y a 2 jours</span>
                            </div>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                            <button class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem;"><i class="fa-solid fa-users"></i> Voir Candidats</button>
                            <button class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.85rem;"><i class="fa-solid fa-pen"></i> Modifier</button>
                        </div>
                    </div>
                </div>

                <!-- Offre 2 -->
                <div class="card hover-zoom interactive-card" style="border-left: 4px solid var(--warning);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 0.5rem;">
                                <h3 style="font-size: 1.1rem;">Conseil Architecture Cloud</h3>
                                <span class="badge warning">0 Candidatures</span>
                            </div>
                            <p style="font-size: 0.9rem; color: var(--gray); margin-bottom: 0.75rem;">Restructuration de l'architecture micro-services vers le cloud AWS.</p>
                            <div style="display: flex; gap: 1.5rem; font-size: 0.85rem; color: var(--gray);">
                                <span><i class="fa-solid fa-location-dot"></i> Hybride (Paris)</span>
                                <span><i class="fa-solid fa-wallet"></i> TJM: 800€</span>
                                <span><i class="fa-solid fa-calendar"></i> Publiée aujourd'hui</span>
                            </div>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                            <button class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.85rem;"><i class="fa-solid fa-pen"></i> Modifier</button>
                            <button class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.85rem; color: var(--danger); border-color: var(--danger);"><i class="fa-solid fa-trash"></i> Supprimer</button>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>

