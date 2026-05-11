<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Expert | Mes Candidatures</title>
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
        .card { background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-sm); margin-bottom: 1.5rem; }
        .badge { padding: 0.25rem 0.75rem; border-radius: var(--radius-full); font-size: 0.85rem; font-weight: 500; display: inline-block;}
        .badge.success { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .badge.warning { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
        .badge.danger { background: rgba(239, 68, 68, 0.1); color: var(--danger); }
        .badge.primary { background: rgba(37, 99, 235, 0.1); color: var(--primary); }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header"><a href="index.php" class="logo" style="text-decoration: none;"><i class="fa-solid fa-chart-pie text-secondary"></i> Digit Advisory</a></div>
            <div class="sidebar-menu">
                <a href="front-expert-dashboard.php" class="menu-item"><i class="fa-solid fa-house"></i> Vue d'ensemble</a>
                <a href="front-expert-profil.php" class="menu-item"><i class="fa-solid fa-user"></i> Mon Profil Expert</a>
                <a href="front-expert-portfolio.php" class="menu-item"><i class="fa-solid fa-folder-open"></i> Portfolio & CV</a>
                <a href="front-expert-offres.php" class="menu-item"><i class="fa-solid fa-briefcase"></i> Explorer les Offres</a>
                <a href="front-expert-candidatures.php" class="menu-item active"><i class="fa-solid fa-file-contract"></i> Mes Candidatures</a>
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
                <h2 style="margin: 0; font-size: 1.5rem;">Mes Candidatures</h2>
                <div style="display:flex; gap:0.5rem;">
                    <span class="badge success">Acceptées: 2</span>
                    <span class="badge warning">En attente: 3</span>
                    <span class="badge danger">Refusées: 1</span>
                </div>
            </div>

            <section class="fade-in-up">
                <!-- Candidature 1 - Acceptée -->
                <div class="card hover-zoom interactive-card" style="border-left: 4px solid var(--success);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 0.5rem;">
                                <h3 style="font-size: 1.1rem;">Audit RGPD Complet</h3>
                                <span class="badge success"><i class="fa-solid fa-check"></i> Acceptée</span>
                            </div>
                            <p style="color: var(--gray); font-size: 0.9rem; margin-bottom: 0.75rem;">Mise en conformité RGPD de l'infrastructure cloud. Durée: 2 mois.</p>
                            <div style="display: flex; gap: 1.5rem; font-size: 0.85rem; color: var(--gray);">
                                <span><i class="fa-solid fa-building"></i> TechCorp SAS</span>
                                <span><i class="fa-solid fa-calendar"></i> Candidature envoyée le 02/04/2026</span>
                                <span><i class="fa-solid fa-wallet"></i> 600€/J</span>
                            </div>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                            <a href="front-expert-messagerie.php" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem;"><i class="fa-solid fa-comments"></i> Messagerie</a>
                        </div>
                    </div>
                </div>

                <!-- Candidature 2 - En attente -->
                <div class="card hover-zoom interactive-card" style="border-left: 4px solid var(--warning);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 0.5rem;">
                                <h3 style="font-size: 1.1rem;">Conseil Sécurité - ISO 27001</h3>
                                <span class="badge warning"><i class="fa-solid fa-clock"></i> En attente</span>
                            </div>
                            <p style="color: var(--gray); font-size: 0.9rem; margin-bottom: 0.75rem;">Accompagnement PME pour l'obtention ISO 27001. Durée estimée: 3 mois.</p>
                            <div style="display: flex; gap: 1.5rem; font-size: 0.85rem; color: var(--gray);">
                                <span><i class="fa-solid fa-building"></i> ABC Startups</span>
                                <span><i class="fa-solid fa-calendar"></i> Candidature envoyée le 06/04/2026</span>
                                <span><i class="fa-solid fa-wallet"></i> Sur devis</span>
                            </div>
                        </div>
                        <div>
                            <button class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.85rem; color:var(--danger); border-color:var(--danger);"><i class="fa-solid fa-xmark"></i> Retirer</button>
                        </div>
                    </div>
                </div>

                <!-- Candidature 3 - Refusée -->
                <div class="card" style="border-left: 4px solid var(--danger); opacity: 0.7;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 0.5rem;">
                                <h3 style="font-size: 1.1rem;">Optimisation Réseau Interne</h3>
                                <span class="badge danger"><i class="fa-solid fa-xmark"></i> Refusée</span>
                            </div>
                            <p style="color: var(--gray); font-size: 0.9rem; margin-bottom: 0.75rem;">Audit et restructuration du réseau local de l'entreprise.</p>
                            <div style="display: flex; gap: 1.5rem; font-size: 0.85rem; color: var(--gray);">
                                <span><i class="fa-solid fa-building"></i> NetSecure SARL</span>
                                <span><i class="fa-solid fa-calendar"></i> Candidature envoyée le 28/03/2026</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>

