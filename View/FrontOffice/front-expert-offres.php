<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Expert | Offres & Missions</title>
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
        .badge { padding: 0.25rem 0.75rem; border-radius: var(--radius-full); font-size: 0.85rem; font-weight: 500; display: inline-block;}
        .badge.primary { background: rgba(37, 99, 235, 0.1); color: var(--primary); }
        .badge.warning { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar Expert -->
        <aside class="sidebar">
            <div class="sidebar-header"><a href="index.php" class="logo" style="text-decoration: none;"><i class="fa-solid fa-chart-pie text-secondary"></i> Digit Advisory</a></div>
            <div class="sidebar-menu">
                <a href="front-expert-dashboard.php" class="menu-item"><i class="fa-solid fa-house"></i> Vue d'ensemble</a>
                <a href="front-expert-profil.php" class="menu-item"><i class="fa-solid fa-user"></i> Mon Profil Expert</a>
                <a href="front-expert-portfolio.php" class="menu-item"><i class="fa-solid fa-folder-open"></i> Portfolio & CV</a>
                <a href="front-expert-offres.php" class="menu-item active"><i class="fa-solid fa-briefcase"></i> Explorer les Offres</a>
                <a href="front-expert-candidatures.php" class="menu-item"><i class="fa-solid fa-file-contract"></i> Mes Candidatures</a>
                <a href="front-expert-messagerie.php" class="menu-item"><i class="fa-solid fa-comments"></i> Messagerie</a>
            </div>
            <div class="user-profile-widget">
                <div class="user-avatar">AL</div>
                <div><h4 style="font-size: 0.95rem; margin-bottom: 0.2rem;">Alice Martin</h4><span style="font-size: 0.8rem; color: var(--gray);">Consultant Senior</span></div>
                <a href="login.php#register" style="margin-left: auto; color: var(--danger);"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
            </div>
        </aside>

        <main class="main-content">
            <div class="top-navbar">
                <h2 style="margin: 0; font-size: 1.5rem;">Espace Expert</h2>
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <div class="search-bar" style="background:#f1f5f9; padding: 0.5rem 1rem; border-radius: 20px; display:flex; align-items:center;">
                        <i class="fa-solid fa-magnifying-glass text-gray"></i>
                        <input type="text" placeholder="Chercher une mission..." style="border:none; margin-left:10px; background:transparent; outline:none;">
                    </div>
                </div>
            </div>

            <section class="fade-in-up">
                <div style="display: flex; justify-content: space-between; align-items: center;" class="mb-2 slide-in-right">
                    <h2>Les dernières missions postées</h2>
                    <p style="color: var(--gray);"><i class="fa-solid fa-filter"></i> Filtres pertinents</p>
                </div>

                <div style="display: grid; gap: 1.5rem;">
                    
                    <!-- Offer Card 1 -->
                    <div class="card hover-zoom interactive-card" style="border-left: 4px solid var(--primary); display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="display:flex; align-items:center; gap: 10px; margin-bottom: 0.5rem;">
                                <h3 style="color: var(--dark); font-size: 1.2rem;">Audit RGPD / Conformité Cloud</h3>
                                <span class="badge warning">Urgent</span>
                            </div>
                            <p style="font-size: 0.95rem; color: var(--gray); margin-bottom:1rem; max-width:600px;">
                                L'entreprise TechCorp cherche un expert sénior pour auditer ses bases de données et assurer la mise en conformité RGPD de son infrastructure cloud avant certification.
                            </p>
                            <div style="display: flex; gap: 1rem; font-size: 0.85rem; color: var(--gray);">
                                <span><i class="fa-solid fa-building"></i> TechCorp SAS</span>
                                <span><i class="fa-solid fa-location-dot"></i> 100% Télétravail</span>
                                <span style="font-weight: 600; color: var(--dark);"><i class="fa-solid fa-wallet text-secondary"></i> Budget: ~600€/J</span>
                            </div>
                        </div>
                        <div style="display: flex; flex-direction: column; gap:10px; align-items:flex-end;">
                            <span style="font-size: 0.8rem; color: var(--gray);">Publié hier</span>
                            <button class="btn btn-primary pulse-glow"><i class="fa-solid fa-paper-plane"></i> Candidater</button>
                        </div>
                    </div>

                    <!-- Offer Card 2 -->
                    <div class="card hover-zoom interactive-card" style="border-left: 4px solid var(--secondary); display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="display:flex; align-items:center; gap: 10px; margin-bottom: 0.5rem;">
                                <h3 style="color: var(--dark); font-size: 1.2rem;">Conseil en Sécurité - ISO 27001</h3>
                            </div>
                            <p style="font-size: 0.95rem; color: var(--gray); margin-bottom:1rem; max-width:600px;">
                                Accompagnement d'une PME dans toutes les étapes d'audit pour l'obtention de la certification ISO 27001. Mission estimée à 3 mois.
                            </p>
                            <div style="display: flex; gap: 1rem; font-size: 0.85rem; color: var(--gray);">
                                <span><i class="fa-solid fa-building"></i> ABC Startups</span>
                                <span><i class="fa-solid fa-location-dot"></i> Hybride (Paris)</span>
                                <span style="font-weight: 600; color: var(--dark);"><i class="fa-solid fa-wallet text-secondary"></i> Budget: Sur devis</span>
                            </div>
                        </div>
                        <div style="display: flex; flex-direction: column; gap:10px; align-items:flex-end;">
                            <span style="font-size: 0.8rem; color: var(--gray);">Il y a 3 jours</span>
                            <button class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Candidater</button>
                        </div>
                    </div>

                </div>
            </section>
        </main>
    </div>
</body>
</html>

