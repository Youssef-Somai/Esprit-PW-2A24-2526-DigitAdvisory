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
                <div class="user-avatar">TC</div>
                <div><h4 style="font-size: 0.95rem; margin-bottom: 0.2rem;">TechCorp SAS</h4><span style="font-size: 0.8rem; color: var(--gray);">Compte Entreprise</span></div>
                <a href="login.php" style="margin-left: auto; color: var(--danger);"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
            </div>
        </aside>

        <main class="main-content">
            <div class="top-navbar">
                <h2 style="margin: 0; font-size: 1.5rem;">Profil de l'Entreprise</h2>
                <button class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Sauvegarder</button>
            </div>
            <section class="fade-in-up">
                <div class="card hover-zoom">
                    <h3 style="margin-bottom: 1.5rem;"><i class="fa-solid fa-building text-primary"></i> Informations de l'Entreprise</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div class="form-group">
                            <label>Raison Sociale</label>
                            <input type="text" class="form-control" value="TechCorp SAS">
                        </div>
                        <div class="form-group">
                            <label>Email de contact</label>
                            <input type="email" class="form-control" value="contact@techcorp.com">
                        </div>
                        <div class="form-group">
                            <label>Secteur d'Activité</label>
                            <select class="form-control">
                                <option selected>Technologies de l'Information</option>
                                <option>Finance & Banque</option>
                                <option>Santé</option>
                                <option>Industrie</option>
                                <option>Commerce</option>
                                <option>Autre</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Taille de l'entreprise</label>
                            <select class="form-control">
                                <option>1 - 10 employés</option>
                                <option selected>11 - 50 employés</option>
                                <option>51 - 200 employés</option>
                                <option>200+ employés</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Numéro SIRET</label>
                            <input type="text" class="form-control" value="123 456 789 00012">
                        </div>
                        <div class="form-group">
                            <label>Adresse</label>
                            <input type="text" class="form-control" value="45 Rue de la Tech, 75008 Paris">
                        </div>
                    </div>
                </div>

                <div class="card hover-zoom">
                    <h3 style="margin-bottom: 1rem;"><i class="fa-solid fa-file-lines text-primary"></i> Description de l'Entreprise</h3>
                    <div class="form-group">
                        <label>Présentation</label>
                        <textarea class="form-control" rows="4" style="resize: vertical;">TechCorp SAS est une entreprise spécialisée dans le développement de solutions Cloud innovantes. Nous recherchons régulièrement des experts pour nous accompagner dans nos démarches de certification et d'audit de sécurité.</textarea>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button class="btn btn-primary pulse-glow"><i class="fa-solid fa-floppy-disk"></i> Mettre à jour le profil</button>
                    <button class="btn btn-outline" style="color:var(--danger); border-color:var(--danger);"><i class="fa-solid fa-trash"></i> Désactiver le compte</button>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
