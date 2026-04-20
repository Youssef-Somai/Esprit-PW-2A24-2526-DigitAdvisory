<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Entreprise | Messagerie</title>
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
        .chat-container { display: flex; height: 550px; border: 1px solid var(--gray-light); border-radius: var(--radius-lg); overflow: hidden; background: white; }
        .chat-sidebar { width: 280px; border-right: 1px solid var(--gray-light); background: white; overflow-y: auto; }
        .chat-list-item { padding: 1rem; border-bottom: 1px solid var(--gray-light); display: flex; gap: 1rem; cursor: pointer; transition: var(--transition); align-items: center; }
        .chat-list-item:hover, .chat-list-item.active { background: #eff6ff; }
        .chat-main { flex: 1; display: flex; flex-direction: column; background: #f8fafc; }
        .chat-header { padding: 1rem; background: white; border-bottom: 1px solid var(--gray-light); display: flex; justify-content: space-between; align-items: center; }
        .chat-messages { flex: 1; padding: 1.5rem; overflow-y: auto; display: flex; flex-direction: column; gap: 1rem; }
        .message { max-width: 70%; padding: 0.75rem 1rem; border-radius: var(--radius-lg); font-size: 0.95rem; }
        .message.received { background: white; align-self: flex-start; box-shadow: var(--shadow-sm); border-bottom-left-radius: 0; }
        .message.sent { background: var(--primary); color: white; align-self: flex-end; box-shadow: var(--shadow-sm); border-bottom-right-radius: 0; }
        .chat-input { padding: 1rem; background: white; border-top: 1px solid var(--gray-light); display: flex; gap: 0.75rem; align-items: center; }
        .chat-input input { flex: 1; padding: 0.75rem 1rem; border: 1px solid var(--gray-light); border-radius: var(--radius-full); outline: none; font-family: var(--font-main); }
        .chat-input input:focus { border-color: var(--primary); }
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
                <a href="front-certification.php" class="menu-item"><i class="fa-solid fa-award"></i> Certifications ISO</a>
                <a href="front-messagerie.php" class="menu-item active"><i class="fa-solid fa-comments"></i> Messagerie</a>
            </div>
            <div class="user-profile-widget">
                <div class="user-avatar">TC</div>
                <div><h4 style="font-size: 0.95rem; margin-bottom: 0.2rem;">TechCorp SAS</h4><span style="font-size: 0.8rem; color: var(--gray);">Compte Entreprise</span></div>
                <a href="login.php#register" style="margin-left: auto; color: var(--danger);"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
            </div>
        </aside>

        <main class="main-content">
            <div class="top-navbar">
                <h2 style="margin: 0; font-size: 1.5rem;">Messagerie</h2>
            </div>
            <section class="fade-in-up">
                <div class="chat-container">
                    <div class="chat-sidebar">
                        <div class="chat-list-item active">
                            <div style="width:40px; height:40px; min-width:40px; border-radius:50%; background:var(--secondary); color:white; display:flex; justify-content:center; align-items:center; font-weight:600;">AM</div>
                            <div>
                                <h4 style="font-size: 0.9rem;">Alice Martin</h4>
                                <p style="font-size: 0.8rem; color: var(--gray);">Voici le livrable V1 d...</p>
                            </div>
                        </div>
                        <div class="chat-list-item">
                            <div style="width:40px; height:40px; min-width:40px; border-radius:50%; background:var(--accent); color:white; display:flex; justify-content:center; align-items:center; font-weight:600;">JD</div>
                            <div>
                                <h4 style="font-size: 0.9rem;">Jean Dupont</h4>
                                <p style="font-size: 0.8rem; color: var(--gray);">Concernant la mission...</p>
                            </div>
                        </div>
                    </div>
                    <div class="chat-main">
                        <div class="chat-header">
                            <div style="display:flex; align-items:center; gap:1rem;">
                                <div style="width:35px; height:35px; border-radius:50%; background:var(--secondary); color:white; display:flex; justify-content:center; align-items:center; font-weight:600; font-size:0.8rem;">AM</div>
                                <div>
                                    <h4 style="font-size: 0.95rem;">Alice Martin</h4>
                                    <span style="font-size: 0.75rem; color: var(--success);"><i class="fa-solid fa-circle" style="font-size:0.5rem;"></i> En ligne</span>
                                </div>
                            </div>
                            <span style="font-size: 0.8rem; color: var(--gray);">Mission: Audit RGPD</span>
                        </div>
                        <div class="chat-messages">
                            <div class="message received">Bonjour, je viens de vous envoyer les documents pour le début de la mission.</div>
                            <div class="message sent">Super, merci Alice ! Je regarde ça tout de suite.</div>
                            <div class="message received">
                                Voici le livrable V1 du cahier des charges. Pouvez-vous vérifier ?<br>
                                <a href="#" style="color: #93c5fd; font-size:0.85rem; display:inline-block; margin-top:5px;"><i class="fa-solid fa-file-pdf"></i> Livrable_V1.pdf</a>
                            </div>
                        </div>
                        <div class="chat-input">
                            <button class="btn btn-outline" style="border:none; padding:0.5rem;"><i class="fa-solid fa-paperclip"></i></button>
                            <input type="text" placeholder="Écrire un message...">
                            <button class="btn btn-primary" style="padding: 0.6rem 1rem; border-radius: 50%;"><i class="fa-solid fa-paper-plane"></i></button>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>

