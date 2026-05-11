<?php
require_once __DIR__ . '/../../Controller/CertificatController.php';
require_once __DIR__ . '/../../Controller/CritereController.php';

$controller = new CertificatController();
$certificatsRaw = $controller->listCertificats();
$certificats = [];
foreach ($certificatsRaw as $cert) {
    if ($cert->getStatut() === 'Actif') {
        $certificats[] = $cert;
    }
}

// Pour chaque certification, on récupère ses critères détaillés
$chart_diffs = ['Facile' => 0, 'Moyen' => 0, 'Difficile' => 0];

foreach ($certificats as $cert) {
    $cert->detailed_criteres = $controller->getDetailedCriteresByCertificat($cert->getId());
    foreach ($cert->detailed_criteres as $c) {
        $d = $c->getDifficulte();
        if (isset($chart_diffs[$d])) $chart_diffs[$d]++;
    }
}

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
    <title>Espace Entreprise | Certifications ISO & Évaluation</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script src="https://unpkg.com/html-docx-js/dist/html-docx.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
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
        .main-content { flex: 1; margin-left: 280px; padding: 2rem; padding-bottom: 500px; }
        
        .top-navbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; background: white; padding: 1rem 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); }
        
        /* ─── CARD STYLING ─── */
        .card { background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-sm); margin-bottom: 1.5rem; overflow: hidden; transition: box-shadow 0.3s ease; }
        .card:hover { box-shadow: var(--shadow-md); }
        .badge { padding: 0.3rem 0.75rem; border-radius: var(--radius-full); font-size: 0.8rem; font-weight: 600; display: inline-block;}
        .badge.success { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .badge.primary { background: rgba(37, 99, 235, 0.1); color: var(--primary); }
        .badge.info { background: rgba(14, 165, 233, 0.1); color: #0ea5e9; }
        
        /* ─── ACCORDION & EVALUATION STYLING ─── */
        .btn-eval {
            width: 100%; border: 1px solid var(--gray-light); background: #f8fafc; color: var(--dark);
            padding: 0.75rem; border-radius: var(--radius); font-weight: 600; cursor: pointer; transition: all 0.2s ease;
            display: flex; justify-content: space-between; align-items: center; font-family: var(--font-main);
        }
        .btn-eval:hover { background: #f1f5f9; border-color: var(--primary); color: var(--primary); }
        .btn-eval i { transition: transform 0.3s ease; }
        .btn-eval.open i { transform: rotate(180deg); color: var(--primary); }

        .eval-container {
            max-height: 0; overflow: hidden; transition: max-height 0.4s ease-out, padding 0.4s ease;
            background: #f8fafc; border-radius: 0 0 var(--radius) var(--radius);
        }
        .eval-container.expanded { max-height: 10000px; padding: 1.5rem; border-top: 1px solid var(--gray-light); }

        .progress-wrapper { margin-bottom: 1.5rem; }
        .progress-header { display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.9rem; }
        .progress-bar-bg { width: 100%; height: 12px; background: #e2e8f0; border-radius: 10px; overflow: hidden; }
        .progress-fill { height: 100%; background: var(--primary); width: 0%; transition: width 0.5s ease-out, background-color 0.5s ease; }
        
        .critere-item {
            display: flex; align-items: flex-start; padding: 1rem 0; border-bottom: 1px solid #e2e8f0;
        }
        .critere-item:last-child { border-bottom: none; padding-bottom: 0; }
        .critere-item input[type="checkbox"] { margin-top: 0.3rem; margin-right: 1.25rem; transform: scale(1.3); cursor: pointer; accent-color: var(--primary); }
        .critere-details { flex: 1; display: flex; flex-direction: column; gap: 0.4rem; }
        .critere-title { font-weight: 600; color: var(--dark); font-size: 1rem; display: flex; align-items: center; gap: 0.75rem;}
        .critere-cat-badge { font-size: 0.7rem; padding: 0.15rem 0.5rem; border-radius: var(--radius); background: #e2e8f0; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; }
        .critere-desc { font-size: 0.9rem; color: var(--gray); line-height: 1.5; }
        .critere-preuve { font-size: 0.85rem; color: var(--dark); background: rgba(37, 99, 235, 0.05); border-left: 3px solid var(--primary); padding: 0.5rem 0.75rem; border-radius: 0 var(--radius) var(--radius) 0; }
        
        .critere-score { font-size: 0.9rem; color: var(--primary); font-weight: 700; white-space: nowrap; margin-left: 1.5rem; margin-top: 0.2rem; }

        .empty-state { text-align: center; padding: 4rem 2rem; color: var(--gray); }
        .empty-state i { font-size: 3rem; margin-bottom: 1rem; color: var(--gray-light); }

                .quiz-btn {
            background: rgba(255,255,255,0.05); color: #f8fafc; border: 1px solid rgba(255,255,255,0.15);
            padding: 1rem 1.4rem; border-radius: 12px; cursor: pointer; font-family: var(--font-main);
            transition: all 0.3s cubic-bezier(0.25,0.8,0.25,1); display: flex; align-items: center; gap: 0.75rem; font-size: 0.95rem;
            position: relative; overflow: hidden;
        }
        .quiz-btn::before {
            content: ''; position: absolute; inset: 0; background: linear-gradient(135deg, rgba(37,99,235,0.3), rgba(99,102,241,0.2));
            opacity: 0; transition: opacity 0.3s ease;
        }
        .quiz-btn:hover { border-color: var(--primary); transform: translateY(-3px); box-shadow: 0 8px 25px rgba(37,99,235,0.2); }
        .quiz-btn:hover::before { opacity: 1; }
        .quiz-btn.selected {
            background: linear-gradient(135deg, rgba(37,99,235,0.3), rgba(99,102,241,0.15)); border-color: var(--primary);
            box-shadow: 0 0 15px rgba(37,99,235,0.3); transform: scale(1.03);
        }
        .quiz-btn.selected::after {
            content: '\f00c'; font-family: 'Font Awesome 6 Free'; font-weight: 900;
            position: absolute; top: 8px; right: 10px; font-size: 0.7rem; color: #10b981;
            background: rgba(16,185,129,0.2); width: 20px; height: 20px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
        }
        .quiz-btn i { position: relative; z-index: 1; font-size: 1.1rem; }
        .quiz-btn span { position: relative; z-index: 1; }

        /* ─── STAT CARDS ─── */
        @keyframes countUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes pulseGlow { 0%,100% { box-shadow: 0 0 0 rgba(37,99,235,0); } 50% { box-shadow: 0 0 20px rgba(37,99,235,0.15); } }
        @keyframes slideInUp { from { opacity:0; transform:translateY(30px); } to { opacity:1; transform:translateY(0); } }
        .stat-card {
            background: white; border-radius: 16px; padding: 1.5rem; text-align: center;
            border: 1px solid #e2e8f0; position: relative; overflow: hidden;
            animation: slideInUp 0.6s ease both; transition: all 0.3s ease;
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 12px 30px rgba(0,0,0,0.1); }
        .stat-card:nth-child(2) { animation-delay: 0.1s; }
        .stat-card:nth-child(3) { animation-delay: 0.2s; }
        .stat-card:nth-child(4) { animation-delay: 0.3s; }
        .stat-icon {
            width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; margin: 0 auto 1rem; animation: pulseGlow 3s infinite;
        }
        .stat-value { font-size: 2.2rem; font-weight: 800; font-family: 'Poppins', sans-serif; margin-bottom: 0.25rem; }
        .stat-label { font-size: 0.85rem; color: var(--gray); font-weight: 500; }
        .stat-card::after {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
        }

        /* ─── CHATBOT ENTREPRISE CSS ─── */
        .chatbot-bubble {
            position: fixed; bottom: 2rem; right: 2rem; width: 60px; height: 60px;
            background: linear-gradient(135deg, var(--primary) 0%, #1d4ed8 100%);
            border-radius: 50%; box-shadow: 0 10px 25px rgba(37, 99, 235, 0.5);
            display: flex; justify-content: center; align-items: center;
            color: white; font-size: 1.8rem; cursor: pointer; z-index: 1001;
            transition: transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        .chatbot-bubble:hover { transform: scale(1.1); }
        
        .chatbot-container {
            position: fixed; bottom: 6rem; right: 2rem; width: 350px; background: white;
            border-radius: 1rem; box-shadow: 0 15px 50px rgba(0,0,0,0.2); z-index: 1000;
            display: flex; flex-direction: column; overflow: hidden; transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            transform-origin: bottom right; height: 480px; max-height: calc(100vh - 120px);
            opacity: 1; transform: scale(1);
        }
        .chatbot-container.collapsed { 
            opacity: 0; transform: scale(0.5); pointer-events: none;
        }
        .chatbot-header {
            background: linear-gradient(135deg, var(--primary) 0%, #1d4ed8 100%); color: white; padding: 1rem 1.25rem; display: flex; justify-content: space-between;
            align-items: center; cursor: pointer; user-select: none; min-height: 60px;
        }
        .chatbot-header:hover { opacity: 0.95; }
        .chatbot-avatar { width: 36px; height: 36px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
        .chatbot-body {
            flex: 1; padding: 1rem; overflow-y: auto; display: flex; flex-direction: column; gap: 0.75rem;
            background: #f8fafc; scroll-behavior: smooth;
        }
        .chat-message { max-width: 85%; padding: 0.75rem 1rem; border-radius: 1rem; font-size: 0.9rem; line-height: 1.5; animation: fadeIn 0.3s; word-wrap: break-word; }
        .bot-message { background: white; border: 1px solid var(--gray-light); border-bottom-left-radius: 0.25rem; align-self: flex-start; color: var(--dark); box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .user-message { background: linear-gradient(135deg, var(--primary), #1d4ed8); color: white; border-bottom-right-radius: 0.25rem; align-self: flex-end; }
        .chatbot-footer {
            padding: 0.75rem 1rem; border-top: 1px solid var(--gray-light); display: flex; gap: 0.5rem; background: white;
        }
        .chatbot-footer input {
            flex: 1; border: 2px solid var(--gray-light); border-radius: 2rem; padding: 0.6rem 1rem;
            outline: none; transition: border-color 0.2s; font-family: var(--font-main); font-size: 0.9rem;
        }
        .chatbot-footer input:focus { border-color: var(--primary); }
        .chatbot-footer input:disabled { background: #f1f5f9; cursor: wait; }
        .chatbot-footer button {
            background: var(--primary); color: white; border: none; width: 40px; height: 40px; border-radius: 50%;
            display: flex; justify-content: center; align-items: center; cursor: pointer; transition: all 0.2s;
            flex-shrink: 0;
        }
        .chatbot-footer button:hover { background: #1d4ed8; transform: scale(1.05); }
        .typing-indicator { display: flex; gap: 0.3rem; padding: 0.25rem 0; align-items: center; }
        .typing-dot { width: 7px; height: 7px; background: var(--gray); border-radius: 50%; animation: typing 1.4s infinite ease-in-out both; }
        .typing-dot:nth-child(1) { animation-delay: -0.32s; }
        .typing-dot:nth-child(2) { animation-delay: -0.16s; }
        @keyframes typing { 0%, 80%, 100% { transform: scale(0); } 40% { transform: scale(1); } }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        /* ─── QUICK REPLIES ─── */
        .quick-replies { display: flex; flex-wrap: wrap; gap: 0.4rem; padding: 0.5rem 1rem 0.75rem; background: white; border-top: 1px solid var(--gray-light); }
        .quick-reply-btn { background: #f1f5f9; border: 1px solid #e2e8f0; color: var(--dark); border-radius: 20px; padding: 0.3rem 0.8rem; font-size: 0.78rem; cursor: pointer; transition: all 0.2s; font-family: var(--font-main); white-space: nowrap; }
        .quick-reply-btn:hover { background: var(--primary); color: white; border-color: var(--primary); }

        /* ─── POST-RESULTS PANEL ─── */
        @keyframes popIn { from { opacity:0; transform: scale(0.85) translateY(20px); } to { opacity:1; transform: scale(1) translateY(0); } }
        .next-step-card {
            background: white; border-radius: 20px; padding: 2rem 1.5rem; text-align: center;
            border: 2px solid #e2e8f0; cursor: pointer; transition: all 0.35s cubic-bezier(0.25,0.8,0.25,1);
            animation: popIn 0.5s ease both; position: relative; overflow: hidden;
        }
        .next-step-card:nth-child(2) { animation-delay: 0.1s; }
        .next-step-card:nth-child(3) { animation-delay: 0.2s; }
        .next-step-card::before { content: ''; position: absolute; inset: 0; opacity: 0; transition: opacity 0.3s ease; }
        .next-step-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0,0,0,0.12); border-color: transparent; }
        .next-step-card:hover::before { opacity: 1; }
        .next-step-card.card-audit::before { background: linear-gradient(135deg, rgba(37,99,235,0.06), rgba(99,102,241,0.04)); }
        .next-step-card.card-eval::before  { background: linear-gradient(135deg, rgba(16,185,129,0.06), rgba(52,211,153,0.04)); }
        .next-step-card.card-chat::before  { background: linear-gradient(135deg, rgba(245,158,11,0.06), rgba(251,191,36,0.04)); }
        .step-icon { width: 72px; height: 72px; border-radius: 20px; margin: 0 auto 1.25rem; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; }
        .quick-wins-list { list-style: none; padding: 0; margin: 0; }
        .quick-wins-list li { display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.75rem 0; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; color: #334155; }
        .quick-wins-list li:last-child { border-bottom: none; }
        .quick-wins-list .win-num { width: 24px; height: 24px; border-radius: 50%; background: linear-gradient(135deg, #10b981, #059669); color: white; font-size: 0.7rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .countdown-bar { height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden; margin-top: 0.5rem; }
        .countdown-fill { height: 100%; background: linear-gradient(90deg, var(--primary), #6366f1); border-radius: 4px; animation: fillBar 2s ease-out both; }
        @keyframes fillBar { from { width: 0%; } }
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
                <h2 style="margin: 0; font-size: 1.5rem;" id="page-title">Espace Certification</h2>
                <div id="top-actions">
                    <span class="badge primary" style="font-size: 0.9rem;"><i class="fa-solid fa-rocket"></i> <?= count($certificats) ?> certification(s)</span>
                </div>
            </div>

            <!-- ─── VUE 1 : ACCUEIL (LANDING) ─── -->
            <div id="view-landing" class="view-section fade-in-up">
                <div style="text-align: center; margin-bottom: 3rem; margin-top: 2rem;">
                    <h1 style="font-size: 2.5rem; color: var(--dark); margin-bottom: 1rem;">Bienvenue dans votre espace d'évaluation</h1>
                    <p style="color: var(--gray); font-size: 1.1rem; max-width: 600px; margin: 0 auto;">Comment souhaitez-vous explorer notre catalogue de certifications ISO aujourd'hui ?</p>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; max-width: 900px; margin: 0 auto;">
                    <div class="card hover-zoom" style="cursor: pointer; text-align: center; padding: 3rem 2rem; border-top: 5px solid var(--gray-light);" onclick="showView('manual')">
                        <div style="font-size: 4rem; color: var(--gray); margin-bottom: 1.5rem;"><i class="fa-solid fa-book-open"></i></div>
                        <h3 style="font-size: 1.5rem; margin-bottom: 1rem; color: var(--dark);">Catalogue Complet</h3>
                        <p style="color: var(--gray); font-size: 0.95rem;">Vous savez déjà ce que vous cherchez ? Parcourez manuellement l'intégralité de nos normes ISO.</p>
                        <button class="btn btn-outline" style="margin-top: 1.5rem; width: 100%;">Explorer le catalogue</button>
                    </div>

                    <div class="card hover-zoom" style="cursor: pointer; text-align: center; padding: 3rem 2rem; border-top: 5px solid var(--accent); background: linear-gradient(to bottom, white, #f8fafc);" onclick="showView('quiz')">
                        <div style="font-size: 4rem; color: var(--accent); margin-bottom: 1.5rem;"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
                        <h3 style="font-size: 1.5rem; margin-bottom: 1rem; color: var(--dark);">Diagnostic IA</h3>
                        <p style="color: var(--gray); font-size: 0.95rem;">Laissez notre Intelligence Artificielle identifier les certifications idéales pour votre entreprise.</p>
                        <button class="btn btn-primary" style="margin-top: 1.5rem; width: 100%;">Lancer le diagnostic</button>
                    </div>
                </div>

                <!-- Bandeau "Derniers résultats" si des résultats sont en cache -->
                <div id="cached-results-banner" style="display: none; margin-top: 2rem; max-width: 900px; margin-left: auto; margin-right: auto;">
                    <div class="card" style="background: linear-gradient(135deg, #0f172a, #1e293b); color: white; border-left: 4px solid var(--accent); display: flex; align-items: center; justify-content: space-between; gap: 1.5rem;">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <i class="fa-solid fa-clock-rotate-left" style="font-size: 1.5rem; color: var(--accent);"></i>
                            <div>
                                <strong style="font-size: 1rem;">Vos derniers résultats sont disponibles</strong>
                                <p style="color: #94a3b8; margin: 0.2rem 0 0; font-size: 0.85rem;" id="cached-results-date"></p>
                            </div>
                        </div>
                        <div style="display: flex; gap: 0.75rem;">
                            <button class="btn btn-primary" style="white-space: nowrap;" onclick="restoreCachedResults()"><i class="fa-solid fa-download" style="margin-right: 0.5rem;"></i>Télécharger le rapport</button>
                            <button class="btn btn-outline" style="border-color: rgba(255,255,255,0.2); color: white; white-space: nowrap;" onclick="clearCachedResults()"><i class="fa-solid fa-trash" style="margin-right: 0.3rem;"></i>Effacer</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ─── VUE 2 : RECHERCHE MANUELLE ─── -->
            <div id="view-manual" class="view-section" style="display: none;">
                <button class="btn btn-outline" style="margin-bottom: 1.5rem;" onclick="showView('landing')"><i class="fa-solid fa-arrow-left"></i> Retour</button>
                <div style="background: white; padding: 1.5rem; border-radius: var(--radius-lg); margin-bottom: 2rem; box-shadow: var(--shadow-sm); border-left: 4px solid var(--primary);">
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <i class="fa-solid fa-magnifying-glass" style="color: var(--gray); font-size: 1.2rem;"></i>
                        <input type="text" id="manual-search" placeholder="Rechercher une norme (ex: ISO 27001)..." style="flex: 1; border: none; outline: none; font-size: 1.1rem; font-family: var(--font-main);" onkeyup="filterManual()">
                    </div>
                </div>
                <div id="manual-cert-list"></div>
            </div>

            <!-- ─── VUE 3 : DIAGNOSTIC IA (QUIZ) ─── -->
            <div id="view-quiz" class="view-section" style="display: none;">
                <button class="btn btn-outline" style="margin-bottom: 1.5rem;" onclick="showView('landing')"><i class="fa-solid fa-arrow-left"></i> Quitter le diagnostic</button>
                <div id="quiz-resume-banner" style="display: none; background: rgba(37,99,235,0.1); border-left: 4px solid var(--primary); padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem;">
                    <i class="fa-solid fa-floppy-disk text-primary"></i> Vos réponses précédentes ont été restaurées !
                </div>
                <!-- ─── MATCH-MAKING QUIZ ─── -->
            <div class="card quiz-card" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%); color: white; margin-bottom: 2rem; border: 1px solid rgba(255,255,255,0.08); border-top: 4px solid var(--accent); position: relative; overflow: hidden;">
                <!-- Decorative bg -->
                <div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: radial-gradient(circle, rgba(245,158,11,0.08) 0%, transparent 70%); border-radius: 50%;"></div>
                <div style="position: absolute; bottom: -30px; left: -30px; width: 150px; height: 150px; background: radial-gradient(circle, rgba(37,99,235,0.06) 0%, transparent 70%); border-radius: 50%;"></div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; position: relative;">
                    <div>
                        <h3 style="margin: 0 0 0.25rem; color: white; font-size: 1.3rem; font-family: 'Poppins', sans-serif;"><i class="fa-solid fa-wand-magic-sparkles text-accent"></i> Diagnostic Personnalisé</h3>
                        <p style="margin: 0; color: #94a3b8; font-size: 0.8rem;" id="quiz-phase-title">Phase 1 : Profil & Contexte de l'Entreprise</p>
                    </div>
                    <div style="font-size: 0.8rem; background: rgba(255,255,255,0.1); padding: 0.4rem 1rem; border-radius: 20px; backdrop-filter: blur(4px);" id="quiz-step-indicator">Étape 1 / 8</div>
                </div>
                
                <!-- Progress dots -->
                <div style="display: flex; gap: 6px; margin-bottom: 2rem; position: relative;" id="quiz-dots">
                    <div class="q-dot active" style="flex: 1; height: 5px; border-radius: 3px; background: var(--accent); transition: background 0.4s ease;"></div>
                    <div class="q-dot" style="flex: 1; height: 5px; border-radius: 3px; background: rgba(255,255,255,0.1); transition: background 0.4s ease;"></div>
                    <div class="q-dot" style="flex: 1; height: 5px; border-radius: 3px; background: rgba(255,255,255,0.1); transition: background 0.4s ease;"></div>
                    <div class="q-dot" style="flex: 1; height: 5px; border-radius: 3px; background: rgba(255,255,255,0.1); transition: background 0.4s ease;"></div>
                    <div class="q-dot" style="flex: 1; height: 5px; border-radius: 3px; background: rgba(255,255,255,0.1); transition: background 0.4s ease;"></div>
                    <div class="q-dot" style="flex: 1; height: 5px; border-radius: 3px; background: rgba(255,255,255,0.1); transition: background 0.4s ease;"></div>
                    <div class="q-dot" style="flex: 1; height: 5px; border-radius: 3px; background: rgba(255,255,255,0.1); transition: background 0.4s ease;"></div>
                    <div class="q-dot" style="flex: 1; height: 5px; border-radius: 3px; background: rgba(255,255,255,0.1); transition: background 0.4s ease;"></div>
                </div>

                <div class="quiz-steps-container" style="position: relative; min-height: 220px;">
                    
                    <!-- Étape 1 : Secteur -->
                    <div class="quiz-step" id="q-step-1" style="position: absolute; width: 100%; transition: all 0.5s cubic-bezier(0.25,0.8,0.25,1); opacity: 1; transform: translateX(0); z-index: 2;">
                        <h4 style="font-size: 1.15rem; margin-bottom: 0.5rem; color: #f8fafc; font-weight: 600;">Dans quel secteur d'activité évolue votre entreprise ?</h4>
                        <p style="color: #64748b; font-size: 0.8rem; margin-bottom: 1.5rem;">Cela nous aide à cibler les normes ISO de votre industrie.</p>
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem;" id="q1-options">
                            <button class="quiz-btn" onclick="selectQuizOption(1, 'Informatique / Tech', this)"><i class="fa-solid fa-laptop-code"></i> <span>Tech / IT</span></button>
                            <button class="quiz-btn" onclick="selectQuizOption(1, 'Santé / Médical', this)"><i class="fa-solid fa-heart-pulse"></i> <span>Santé</span></button>
                            <button class="quiz-btn" onclick="selectQuizOption(1, 'Industrie / Production', this)"><i class="fa-solid fa-industry"></i> <span>Industrie</span></button>
                            <button class="quiz-btn" onclick="selectQuizOption(1, 'Finance / Banque', this)"><i class="fa-solid fa-coins"></i> <span>Finance</span></button>
                            <button class="quiz-btn" onclick="selectQuizOption(1, 'Services / Conseil', this)"><i class="fa-solid fa-users-gear"></i> <span>Services</span></button>
                            <button class="quiz-btn" onclick="selectQuizOption(1, 'Environnement / Énergie', this)"><i class="fa-solid fa-leaf"></i> <span>Environnement</span></button>
                        </div>
                    </div>

                    <!-- Étape 2 : Taille -->
                    <div class="quiz-step" id="q-step-2" style="position: absolute; width: 100%; transition: all 0.5s cubic-bezier(0.25,0.8,0.25,1); opacity: 0; transform: translateX(60px); pointer-events: none; z-index: 1;">
                        <h4 style="font-size: 1.15rem; margin-bottom: 0.5rem; color: #f8fafc; font-weight: 600;">Quelle est la taille de votre structure ?</h4>
                        <p style="color: #64748b; font-size: 0.8rem; margin-bottom: 1.5rem;">Les exigences varient selon la complexité organisationnelle.</p>
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem;" id="q2-options">
                            <button class="quiz-btn" onclick="selectQuizOption(2, 'Startup / TPE (< 10)', this)" style="flex-direction: column; text-align: center; padding: 1.2rem;"><i class="fa-solid fa-seedling" style="font-size: 1.5rem; margin-bottom: 0.5rem;"></i> <span>Startup / TPE</span><small style="color: #64748b; font-size: 0.7rem;">Moins de 10</small></button>
                            <button class="quiz-btn" onclick="selectQuizOption(2, 'PME (10 à 250)', this)" style="flex-direction: column; text-align: center; padding: 1.2rem;"><i class="fa-solid fa-building-user" style="font-size: 1.5rem; margin-bottom: 0.5rem;"></i> <span>PME</span><small style="color: #64748b; font-size: 0.7rem;">10 à 250</small></button>
                            <button class="quiz-btn" onclick="selectQuizOption(2, 'ETI / Grand groupe (+250)', this)" style="flex-direction: column; text-align: center; padding: 1.2rem;"><i class="fa-solid fa-city" style="font-size: 1.5rem; margin-bottom: 0.5rem;"></i> <span>Grande entreprise</span><small style="color: #64748b; font-size: 0.7rem;">Plus de 250</small></button>
                        </div>
                    </div>

                    <!-- Étape 3 : Clientèle -->
                    <div class="quiz-step" id="q-step-3" style="position: absolute; width: 100%; transition: all 0.5s cubic-bezier(0.25,0.8,0.25,1); opacity: 0; transform: translateX(60px); pointer-events: none; z-index: 1;">
                        <h4 style="font-size: 1.15rem; margin-bottom: 0.5rem; color: #f8fafc; font-weight: 600;">Quel est votre type de clientèle principal ?</h4>
                        <p style="color: #64748b; font-size: 0.8rem; margin-bottom: 1.5rem;">Cela influence fortement les normes de qualité et sécurité attendues.</p>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem;" id="q3-options">
                            <button class="quiz-btn" onclick="selectQuizOption(3, 'B2B (Entreprises)', this)"><i class="fa-solid fa-building"></i> <span>B2B (Entreprises)</span></button>
                            <button class="quiz-btn" onclick="selectQuizOption(3, 'B2C (Particuliers)', this)"><i class="fa-solid fa-users"></i> <span>B2C (Particuliers)</span></button>
                            <button class="quiz-btn" onclick="selectQuizOption(3, 'Mixte (B2B & B2C)', this)"><i class="fa-solid fa-arrows-turn-to-dots"></i> <span>Mixte</span></button>
                            <button class="quiz-btn" onclick="selectQuizOption(3, 'Secteur Public', this)"><i class="fa-solid fa-landmark"></i> <span>Secteur Public</span></button>
                        </div>
                    </div>

                    <!-- Étape 4 : Certifications existantes -->
                    <div class="quiz-step" id="q-step-4" style="position: absolute; width: 100%; transition: all 0.5s cubic-bezier(0.25,0.8,0.25,1); opacity: 0; transform: translateX(60px); pointer-events: none; z-index: 1;">
                        <h4 style="font-size: 1.15rem; margin-bottom: 0.5rem; color: #f8fafc; font-weight: 600;">Avez-vous déjà des certifications en cours ?</h4>
                        <p style="color: #64748b; font-size: 0.8rem; margin-bottom: 1.5rem;">Cela nous permet d'éviter les doublons et de suggérer des compléments.</p>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem;" id="q4-options">
                            <button class="quiz-btn" onclick="selectQuizOption(4, 'Aucune certification', this)"><i class="fa-solid fa-circle-xmark"></i> <span>Aucune certification</span></button>
                            <button class="quiz-btn" onclick="selectQuizOption(4, 'ISO 9001 deja obtenue', this)"><i class="fa-solid fa-medal"></i> <span>ISO 9001 (Qualité)</span></button>
                            <button class="quiz-btn" onclick="selectQuizOption(4, 'ISO 27001 deja obtenue', this)"><i class="fa-solid fa-shield-halved"></i> <span>ISO 27001 (Sécurité)</span></button>
                            <button class="quiz-btn" onclick="selectQuizOption(4, 'Autre certification', this)"><i class="fa-solid fa-certificate"></i> <span>Autre certification</span></button>
                        </div>
                    </div>

                    <!-- Étape 5 : Priorités -->
                    <div class="quiz-step" id="q-step-5" style="position: absolute; width: 100%; transition: all 0.5s cubic-bezier(0.25,0.8,0.25,1); opacity: 0; transform: translateX(60px); pointer-events: none; z-index: 1;">
                        <h4 style="font-size: 1.15rem; margin-bottom: 0.5rem; color: #f8fafc; font-weight: 600;">Quel est votre enjeu prioritaire aujourd'hui ?</h4>
                        <p style="color: #64748b; font-size: 0.8rem; margin-bottom: 1.5rem;">Sélectionnez le domaine que vous souhaitez renforcer en premier.</p>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem;" id="q5-options">
                            <button class="quiz-btn" onclick="selectQuizOption(5, 'Sécurité de l information', this)"><i class="fa-solid fa-shield-halved"></i> <span>Cybersécurité / Données</span></button>
                            <button class="quiz-btn" onclick="selectQuizOption(5, 'Qualité des produits/services', this)"><i class="fa-solid fa-star"></i> <span>Qualité des produits/services</span></button>
                            <button class="quiz-btn" onclick="selectQuizOption(5, 'Impact environnemental', this)"><i class="fa-solid fa-earth-europe"></i> <span>Impact environnemental</span></button>
                            <button class="quiz-btn" onclick="selectQuizOption(5, 'Santé et sécurité au travail', this)"><i class="fa-solid fa-helmet-safety"></i> <span>Santé et sécurité au travail</span></button>
                        </div>
                    </div>

                    <!-- Étape 6 : Lacunes -->
                    <div class="quiz-step" id="q-step-6" style="position: absolute; width: 100%; transition: all 0.5s cubic-bezier(0.25,0.8,0.25,1); opacity: 0; transform: translateX(60px); pointer-events: none; z-index: 1;">
                        <h4 style="font-size: 1.15rem; margin-bottom: 0.5rem; color: #f8fafc; font-weight: 600;">Quelles sont vos principales lacunes actuelles ?</h4>
                        <p style="color: #64748b; font-size: 0.8rem; margin-bottom: 1.5rem;">Identifiez vos points faibles pour des recommandations plus précises.</p>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem;" id="q6-options">
                            <button class="quiz-btn" onclick="selectQuizOption(6, 'Pas de documentation formelle', this)"><i class="fa-solid fa-file-circle-xmark"></i> <span>Manque de documentation</span></button>
                            <button class="quiz-btn" onclick="selectQuizOption(6, 'Processus non standardises', this)"><i class="fa-solid fa-shuffle"></i> <span>Processus non standardisés</span></button>
                            <button class="quiz-btn" onclick="selectQuizOption(6, 'Formation insuffisante des equipes', this)"><i class="fa-solid fa-graduation-cap"></i> <span>Formation insuffisante</span></button>
                            <button class="quiz-btn" onclick="selectQuizOption(6, 'Pas d audit interne', this)"><i class="fa-solid fa-clipboard-question"></i> <span>Pas d'audit interne</span></button>
                        </div>
                    </div>

                    <!-- Étape 7 : Maturité -->
                    <div class="quiz-step" id="q-step-7" style="position: absolute; width: 100%; transition: all 0.5s cubic-bezier(0.25,0.8,0.25,1); opacity: 0; transform: translateX(60px); pointer-events: none; z-index: 1;">
                        <h4 style="font-size: 1.15rem; margin-bottom: 0.5rem; color: #f8fafc; font-weight: 600;">Comment évaluez-vous votre maturité numérique ?</h4>
                        <p style="color: #64748b; font-size: 0.8rem; margin-bottom: 1.5rem;">Nous adapterons la difficulté et le rythme de la roadmap.</p>
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem;" id="q7-options">
                            <button class="quiz-btn" onclick="selectQuizOption(7, 'Débutant (Processus manuels)', this)" style="flex-direction: column; text-align: center; padding: 1.2rem;"><i class="fa-solid fa-battery-quarter" style="font-size: 1.5rem; margin-bottom: 0.5rem;"></i> <span>Débutant</span></button>
                            <button class="quiz-btn" onclick="selectQuizOption(7, 'Intermédiaire (Outils en place)', this)" style="flex-direction: column; text-align: center; padding: 1.2rem;"><i class="fa-solid fa-battery-half" style="font-size: 1.5rem; margin-bottom: 0.5rem;"></i> <span>Intermédiaire</span></button>
                            <button class="quiz-btn" onclick="selectQuizOption(7, 'Avancé (Très digitalisé)', this)" style="flex-direction: column; text-align: center; padding: 1.2rem;"><i class="fa-solid fa-battery-full" style="font-size: 1.5rem; margin-bottom: 0.5rem;"></i> <span>Avancé</span></button>
                        </div>
                    </div>

                    <!-- Étape 8 : Objectif libre -->
                    <div class="quiz-step" id="q-step-8" style="position: absolute; width: 100%; transition: all 0.5s cubic-bezier(0.25,0.8,0.25,1); opacity: 0; transform: translateX(60px); pointer-events: none; z-index: 1;">
                        <h4 style="font-size: 1.15rem; margin-bottom: 0.5rem; color: #f8fafc; font-weight: 600;">Décrivez votre objectif en une phrase</h4>
                        <p style="color: #64748b; font-size: 0.8rem; margin-bottom: 1.5rem;">Plus vous êtes précis, plus l'IA sera pertinente dans ses recommandations.</p>
                        <div style="display: flex; gap: 0.5rem;">
                            <input type="text" id="quiz-goal-input" placeholder="Ex: Rassurer nos clients sur la sécurité de leurs données..." style="flex: 1; padding: 1rem 1.2rem; border-radius: 12px; border: 1px solid rgba(255,255,255,0.15); background: rgba(0,0,0,0.25); color: white; outline: none; font-family: var(--font-main); font-size: 0.95rem; transition: border-color 0.3s;">
                            <button class="btn btn-primary" onclick="finishQuiz()" id="btn-quiz-finish" style="padding: 0 2rem; border-radius: 12px; font-weight: 600;"><i class="fa-solid fa-rocket" style="margin-right: 0.5rem;"></i> Analyser</button>
                        </div>
                    </div>

                    <!-- Chargement -->
                    <div class="quiz-step" id="q-step-loading" style="position: absolute; width: 100%; transition: all 0.5s cubic-bezier(0.25,0.8,0.25,1); opacity: 0; transform: scale(0.9); pointer-events: none; text-align: center; padding-top: 1.5rem; z-index: 1;">
                        <div style="font-size: 3.5rem; color: var(--accent); margin-bottom: 1.5rem; position: relative; display: inline-block;">
                            <i class="fa-solid fa-brain fa-beat-fade"></i>
                            <div style="position: absolute; top: -5px; right: -15px; width: 20px; height: 20px; background: var(--accent); border-radius: 50%; animation: pulseGlow 1.5s infinite;"></div>
                        </div>
                        <h4 style="font-size: 1.3rem; margin-bottom: 0.5rem; color: white; font-family: 'Poppins', sans-serif;">Analyse IA en cours...</h4>
                        <p style="color: #94a3b8; font-size: 0.9rem; max-width: 400px; margin: 0 auto;">Notre IA croise vos réponses avec notre base de normes ISO pour créer un diagnostic sur mesure.</p>
                    </div>

                </div>
            </div>

            <!-- ─── VUE 4 : RÉSULTATS IA (CONSULTING SAAS) ─── -->
            <div id="view-results" class="view-section" style="display: none;">
                <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
                    <button class="btn btn-outline" onclick="showView('landing')"><i class="fa-solid fa-arrow-left"></i> Nouveau Diagnostic</button>
                    <button class="btn btn-primary" onclick="showView('post-results')" style="margin-left: auto;"><i class="fa-solid fa-rocket"></i> Prochaines Étapes <i class="fa-solid fa-arrow-right"></i></button>
                </div>

                <div style="background: white; padding: 2rem; border-radius: var(--radius-lg); margin-bottom: 2rem; box-shadow: var(--shadow-sm); border-top: 6px solid var(--primary); text-align: center;">
                    <h2 style="color: var(--dark); margin-bottom: 0.5rem; font-size: 1.8rem;"><i class="fa-solid fa-file-contract text-primary"></i> Rapport de Diagnostic & Consulting</h2>
                    <p style="color: var(--gray); font-size: 1rem;">Généré sur mesure par l'Intelligence Artificielle de DigitAdvisory.</p>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; margin-bottom: 2rem;">
                    <!-- Colonne de gauche : Score et Risque -->
                    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <div class="card" style="text-align: center; padding: 2.5rem 1.5rem;">
                            <h3 style="color: var(--gray); font-size: 1rem; margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 1px;">Score de Maturité</h3>
                            <div style="position: relative; width: 150px; height: 150px; margin: 0 auto;">
                                <svg viewBox="0 0 36 36" style="width: 100%; height: 100%; transform: rotate(-90deg);">
                                    <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#e2e8f0" stroke-width="3.5" />
                                    <path id="maturity-circle" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="var(--primary)" stroke-width="3.5" stroke-dasharray="0, 100" style="transition: stroke-dasharray 1.5s ease-out;" />
                                </svg>
                                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 2.5rem; font-weight: 700; color: var(--dark);">
                                    <span id="maturity-score-val">0</span><span style="font-size: 1rem; color: var(--gray);">/100</span>
                                </div>
                            </div>
                        </div>

                        <div class="card" style="text-align: center;">
                            <h3 style="color: var(--gray); font-size: 1rem; margin-bottom: 0.5rem; text-transform: uppercase;">Niveau de Risque Global</h3>
                            <div id="risk-level-badge" style="display: inline-block; padding: 0.5rem 1.5rem; border-radius: 50px; font-weight: 700; font-size: 1.2rem; margin-top: 0.5rem;">
                                Évaluation en cours...
                            </div>
                        </div>
                        
                        <div class="card">
                            <h3 style="color: var(--dark); font-size: 1.1rem; margin-bottom: 1rem;"><i class="fa-solid fa-layer-group text-accent"></i> Frameworks Recommandés</h3>
                            <div id="frameworks-list" style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                <!-- Frameworks injectés ici -->
                            </div>
                        </div>
                    </div>

                    <!-- Colonne de droite : Failles et Roadmap -->
                    <div style="display: flex; flex-direction: column; gap: 2rem;">
                        <div class="card" style="border-left: 4px solid var(--danger);">
                            <h3 style="color: var(--dark); font-size: 1.2rem; margin-bottom: 1.5rem;"><i class="fa-solid fa-triangle-exclamation text-danger"></i> Vulnérabilités Critiques (Gap Analysis)</h3>
                            <ul id="vulnerabilities-list" style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 1rem;">
                                <!-- Vulnérabilités injectées ici -->
                            </ul>
                        </div>

                        <div class="card" style="border-left: 4px solid var(--success);">
                            <h3 style="color: var(--dark); font-size: 1.2rem; margin-bottom: 2rem;"><i class="fa-solid fa-route text-success"></i> Plan d'Action Stratégique (Roadmap)</h3>
                            <div class="roadmap-timeline" id="roadmap-container" style="position: relative; padding-left: 2rem;">
                                <!-- Ligne verticale CSS gérée en style inline ou externe -->
                                <style>
                                    .roadmap-timeline::before { content: ''; position: absolute; top: 0; bottom: 0; left: 6px; width: 4px; background: #e2e8f0; border-radius: 2px; }
                                    .roadmap-step { position: relative; margin-bottom: 1.5rem; }
                                    .roadmap-step:last-child { margin-bottom: 0; }
                                    .roadmap-step::before { content: ''; position: absolute; left: -2rem; top: 0.25rem; width: 16px; height: 16px; background: white; border: 4px solid var(--success); border-radius: 50%; z-index: 1; }
                                </style>
                                <!-- Étapes injectées ici -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ─── VUE 5 : POST-RESULTS — Prochaines Étapes Créatives ─── -->
            <div id="view-post-results" class="view-section" style="display: none;">
                <button class="btn btn-outline" style="margin-bottom: 1.5rem;" onclick="showView('results')"><i class="fa-solid fa-arrow-left"></i> Retour au rapport</button>

                <!-- Header animé -->
                <div style="text-align: center; margin-bottom: 2.5rem;">
                    <div style="font-size: 3.5rem; margin-bottom: 1rem;">🚀</div>
                    <h2 style="font-size: 1.8rem; color: var(--dark); margin-bottom: 0.5rem;">Et maintenant, que faites-vous ?</h2>
                    <p style="color: var(--gray); max-width: 550px; margin: 0 auto; font-size: 0.95rem;">Votre diagnostic est prêt. Choisissez votre prochaine action et transformez vos résultats en actions concrètes.</p>
                </div>

                <!-- 3 cartes Next-Step -->
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 2.5rem;">
                    <!-- Carte 1 : Demander un audit -->
                    <div class="next-step-card card-audit" onclick="requestAuditFromResults()">
                        <div class="step-icon" style="background: linear-gradient(135deg, rgba(37,99,235,0.12), rgba(99,102,241,0.08));"><i class="fa-solid fa-file-signature text-primary" style="font-size: 1.6rem;"></i></div>
                        <h3 style="font-size: 1.1rem; color: var(--dark); margin-bottom: 0.75rem;">Demander un Audit Officiel</h3>
                        <p style="color: var(--gray); font-size: 0.85rem; line-height: 1.5; margin-bottom: 1.25rem;">Transmettez votre dossier à un auditeur certifié et démarrez votre parcours de certification.</p>
                        <span class="badge primary" style="font-size: 0.8rem;"><i class="fa-solid fa-clock"></i> Réponse sous 48h</span>
                    </div>

                    <!-- Carte 2 : Auto-évaluer une norme -->
                    <div class="next-step-card card-eval" onclick="showView('manual')">
                        <div class="step-icon" style="background: linear-gradient(135deg, rgba(16,185,129,0.12), rgba(52,211,153,0.08));"><i class="fa-solid fa-chart-pie" style="color: var(--success); font-size: 1.6rem;"></i></div>
                        <h3 style="font-size: 1.1rem; color: var(--dark); margin-bottom: 0.75rem;">Auto-Évaluer une Norme</h3>
                        <p style="color: var(--gray); font-size: 0.85rem; line-height: 1.5; margin-bottom: 1.25rem;">Explorez le catalogue complet et cochez les critères que vous maîtrisez déjà pour mesurer votre score réel.</p>
                        <span class="badge success" style="font-size: 0.8rem;"><i class="fa-solid fa-bolt"></i> Résultat immédiat</span>
                    </div>

                    <!-- Carte 3 : Parler à DigitBot -->
                    <div class="next-step-card card-chat" onclick="openChatbotWithDiagContext()">
                        <div class="step-icon" style="background: linear-gradient(135deg, rgba(245,158,11,0.12), rgba(251,191,36,0.08));"><i class="fa-solid fa-robot" style="color: var(--accent); font-size: 1.6rem;"></i></div>
                        <h3 style="font-size: 1.1rem; color: var(--dark); margin-bottom: 0.75rem;">Demander à DigitBot</h3>
                        <p style="color: var(--gray); font-size: 0.85rem; line-height: 1.5; margin-bottom: 1.25rem;">Posez vos questions à notre IA qui connaît déjà votre diagnostic et vous guidera étape par étape.</p>
                        <span class="badge" style="background: rgba(245,158,11,0.1); color: var(--accent); font-size: 0.8rem;"><i class="fa-solid fa-wand-magic-sparkles"></i> IA Contextuelle</span>
                    </div>
                </div>

                <!-- Quick Wins + Timeline -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                    <!-- Quick Wins -->
                    <div class="card" style="border-top: 4px solid var(--success);">
                        <h3 style="font-size: 1.1rem; color: var(--dark); margin-bottom: 1.25rem;"><i class="fa-solid fa-bolt text-success"></i> Vos 3 Actions Immédiates</h3>
                        <p style="color: var(--gray); font-size: 0.85rem; margin-bottom: 1rem;">Réalisables en moins de 30 jours pour démarrer dès maintenant :</p>
                        <ul class="quick-wins-list" id="quick-wins-list">
                            <li><div class="win-num">—</div><span style="color: var(--gray);">Analyse IA en cours...</span></li>
                        </ul>
                    </div>

                    <!-- Timeline estimée -->
                    <div class="card" style="border-top: 4px solid var(--primary);">
                        <h3 style="font-size: 1.1rem; color: var(--dark); margin-bottom: 1.25rem;"><i class="fa-solid fa-calendar-check text-primary"></i> Votre Calendrier Estimé</h3>
                        <div style="display: flex; flex-direction: column; gap: 1rem;" id="timeline-steps">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 36px; height: 36px; background: rgba(37,99,235,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><i class="fa-solid fa-flag-checkered text-primary" style="font-size: 0.8rem;"></i></div>
                                <div><div style="font-weight: 600; font-size: 0.9rem;">Aujourd'hui — Lancement</div><div style="font-size: 0.8rem; color: var(--gray);">Démarrage du plan d'action</div></div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 36px; height: 36px; background: rgba(245,158,11,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><i class="fa-solid fa-gears" style="color: var(--accent); font-size: 0.8rem;"></i></div>
                                <div><div style="font-weight: 600; font-size: 0.9rem;">Mois 1-2 — Mise en conformité</div><div style="font-size: 0.8rem; color: var(--gray);">Correction des vulnérabilités critiques</div></div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 36px; height: 36px; background: rgba(16,185,129,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><i class="fa-solid fa-award" style="color: var(--success); font-size: 0.8rem;"></i></div>
                                <div>
                                    <div style="font-weight: 600; font-size: 0.9rem;">Certification — Estimé <span id="timeline-months" style="color: var(--primary);">?</span> mois</div>
                                    <div style="font-size: 0.8rem; color: var(--gray);">Audit final et obtention</div>
                                    <div class="countdown-bar" style="margin-top: 0.4rem;"><div class="countdown-fill" id="timeline-bar" style="width: 0%;"></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Partager / Refaire -->
                <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
                    <button class="btn btn-outline" onclick="shareDiagnostic()"><i class="fa-solid fa-share-nodes"></i> Partager mon diagnostic</button>
                    <button class="btn btn-outline" onclick="showView('quiz'); quizAnswers = {secteur:'',taille:'',clientele:'',existant:'',priorite:'',lacune:'',maturite:'',objectif:''}; goToStep(1);"><i class="fa-solid fa-rotate"></i> Refaire le diagnostic</button>
                    <button class="btn btn-primary" onclick="requestAuditFromResults()"><i class="fa-solid fa-paper-plane"></i> Demander un audit</button>
                </div>
            </div>

            <div style="background: white; padding: 1.5rem; border-radius: var(--radius-lg); margin-bottom: 2rem; box-shadow: var(--shadow-sm); border-left: 4px solid var(--accent);">
                <h3 style="margin-bottom: 0.5rem; color: var(--dark); font-size: 1.1rem;"><i class="fa-solid fa-lightbulb text-accent"></i> Nouvel outil d'évaluation interactif !</h3>
                <p style="color: var(--gray); font-size: 0.9rem; line-height: 1.5;">
                    Sélectionnez une certification ci-dessous pour découvrir ses <strong>critères d'éligibilité</strong>. Vous pouvez cocher les critères que vous maîtrisez déjà afin de calculer en temps réel votre score de préparation à l'audit ISO.
                </p>
            </div>

            <div id="global-cert-store" style="display: none;">
                <?php if (empty($certificats)): ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-award"></i>
                        <h3>Aucune certification disponible</h3>
                        <p>Les certifications ISO seront affichées ici dès qu'elles seront configurées.</p>
                    </div>
                <?php else: ?>
                <div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem;">
                    <?php foreach ($certificats as $index => $cert):
                        $style = $styles[$index % count($styles)];
                        $criteres = $cert->detailed_criteres;
                        $totalPoints = 0;
                        foreach($criteres as $c) { $totalPoints += $c->getPoidsSpecifique(); }
                    ?>
                    <div class="card interactive-card" style="border-left: 4px solid <?= $style['border'] ?>;" data-cert-id="<?= $cert->getId() ?>">
                        
                        <!-- ─── CARD HEADER ─── -->
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                            <div>
                                <h3><i class="fa-solid <?= $style['icon'] ?> <?= $style['iconClass'] ?>"></i> <?= htmlspecialchars($cert->getNorme()) ?> <span style="font-size: 0.8rem; color: var(--gray); font-weight: normal;">(v.<?= htmlspecialchars($cert->getVersion()) ?>)</span></h3>
                                <h4 style="font-size: 1.05rem; margin-top: 0.5rem; color: var(--dark);"><?= htmlspecialchars($cert->getTitre()) ?></h4>
                            </div>
                            <div style="text-align: right;">
                                <?php if (!empty($criteres)): ?>
                                    <span class="badge info" style="margin-bottom: 0.5rem;"><i class="fa-solid fa-list-check"></i> <?= count($criteres) ?> Critères</span><br>
                                <?php endif; ?>
                                <span class="badge" style="background: rgba(245,158,11,0.1); color: var(--accent); margin-bottom: 0.5rem;"><i class="fa-solid fa-clock"></i> Validité : <?= htmlspecialchars($cert->getDureeValidite()) ?> mois</span><br>
                                <?php if ($cert->getOrganisme()): ?>
                                    <span class="badge" style="background: #e2e8f0; color: #475569;"><i class="fa-solid fa-building"></i> <?= htmlspecialchars($cert->getOrganisme()) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p style="font-size: 0.95rem; color: var(--gray); margin-bottom: 1.5rem;">
                            <?= htmlspecialchars($cert->getDescription() ?? 'Aucune description disponible.') ?>
                        </p>

                        <!-- ─── ÉVALUATION BUTTON ─── -->
                        <?php if (!empty($criteres)): ?>
                            <button class="btn-eval" onclick="toggleAccordion('acc-<?= $cert->getId() ?>', this)">
                                <span><i class="fa-solid fa-chart-pie" style="margin-right: 0.5rem;"></i> Lancer l'auto-évaluation</span>
                                <i class="fa-solid fa-chevron-down"></i>
                            </button>

                            <!-- ─── ACCORDION CONTENT ─── -->
                            <div class="eval-container" id="acc-<?= $cert->getId() ?>" data-total="<?= $totalPoints ?>">
                                
                                <div class="progress-wrapper">
                                    <div class="progress-header">
                                        <span>Score de préparation estimé</span>
                                        <span id="score-text-<?= $cert->getId() ?>" style="color: var(--primary);">0%</span>
                                    </div>
                                    <div class="progress-bar-bg">
                                        <div class="progress-fill" id="progress-<?= $cert->getId() ?>"></div>
                                    </div>
                                    <small id="message-<?= $cert->getId() ?>" style="color: var(--gray); font-style: italic; display: block; margin-top: 0.5rem;">Cochez les critères pour voir votre score.</small>
                                </div>

                                <h4 style="margin-bottom: 1rem; font-size: 0.95rem; color: var(--dark);">Checklist des Exigences ISO :</h4>
                                <div class="criteres-list">
                                    <?php foreach ($criteres as $critere): ?>
                                    <label class="critere-item">
                                        <input type="checkbox" onchange="calculateScore(<?= $cert->getId() ?>)" value="<?= $critere->getPoidsSpecifique() ?>" class="chk-<?= $cert->getId() ?>" data-title="<?= htmlspecialchars($critere->getNom()) ?>">
                                        
                                        <div class="critere-details">
                                            <div class="critere-title">
                                                <?= htmlspecialchars($critere->getNom()) ?>
                                                <span class="critere-cat-badge"><?= htmlspecialchars($critere->getCategorie()) ?></span>
                                                <?php if($critere->getEstObligatoire()): ?>
                                                    <span class="badge" style="background:#fecdd3; color:#e11d48; margin-left: auto; font-size: 0.7rem;"><i class="fa-solid fa-circle-exclamation"></i> Requis</span>
                                                <?php endif; ?>
                                                <?php
                                                    $diff = $critere->getDifficulte();
                                                    $diffColor = $diff === 'Facile' ? 'var(--success)' : ($diff === 'Moyen' ? 'var(--accent)' : 'var(--danger)');
                                                ?>
                                                <span class="badge" style="border: 1px solid <?= $diffColor ?>; color: <?= $diffColor ?>; background: transparent; font-size:0.7rem;"><i class="fa-solid fa-signal" style="margin-right:2px;"></i> <?= $diff ?></span>
                                            </div>
                                            <div class="critere-desc">
                                                <?= htmlspecialchars($critere->getDescription() ?? '') ?>
                                            </div>
                                            
                                            <?php if ($critere->getMoyenPreuve()): ?>
                                            <div class="critere-preuve">
                                                <i class="fa-solid fa-file-circle-check" style="margin-right: 0.3rem; color: var(--primary);"></i>
                                                <strong>Preuve Attendue :</strong> <?= htmlspecialchars($critere->getMoyenPreuve()) ?>
                                                <?php if($critere->getDocumentTemplate()): ?>
                                                    <br><a href="<?= htmlspecialchars($critere->getDocumentTemplate()) ?>" target="_blank" style="font-size:0.8rem; color:var(--primary); font-weight:600; text-decoration:none; display:inline-block; margin-top:0.3rem;"><i class="fa-solid fa-download"></i> Télécharger Modèle</a>
                                                <?php endif; ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="critere-score">
                                            +<?= $critere->getPoidsSpecifique() ?> pts
                                        </div>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                                <div style="margin-top: 1.5rem; text-align: right;">
                                    <button class="btn btn-primary hover-zoom" onclick="generateRoadmap(<?= $cert->getId() ?>, '<?= addslashes(htmlspecialchars($cert->getNorme() . ' - ' . $cert->getTitre())) ?>')">
                                        <i class="fa-solid fa-file-word" style="margin-right: 0.5rem;"></i> Générer ma Roadmap IA
                                    </button>
                                </div>
                            </div>
                        <?php else: ?>
                            <div style="background: #f8fafc; padding: 1rem; border-radius: var(--radius); text-align: center; color: var(--gray); font-size: 0.9rem;">
                                <i class="fa-solid fa-circle-info" style="margin-right: 0.5rem;"></i> Aucun critère d'évaluation n'a encore été lié à cette norme.
                            </div>
                        <?php endif; ?>

                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- ─── CHATBOT ENTREPRISE UI ─── -->
    <div class="chatbot-bubble" onclick="toggleChatbot()" id="chatbot-bubble">
        <i class="fa-solid fa-robot"></i>
    </div>

    <div class="chatbot-container" id="chatbot-container">
        <div class="chatbot-header" onclick="toggleChatbot()">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div class="chatbot-avatar"><i class="fa-solid fa-robot"></i></div>
                <div>
                    <h4 style="margin:0; font-size:0.95rem;">DigitBot Certif</h4>
                    <span style="font-size:0.75rem; color:rgba(255,255,255,0.8); display:flex; align-items:center; gap:0.25rem;"><span style="width:6px; height:6px; background:#10b981; border-radius:50%; display:inline-block;"></span> Assistant Certification</span>
                </div>
            </div>
            <i class="fa-solid fa-chevron-up" id="chatbot-toggle-icon"></i>
        </div>
        <div class="chatbot-body" id="chatbot-body">
            <div class="chat-message bot-message">
                Bonjour ! 👋 Je suis <strong>DigitBot</strong>, votre assistant IA. Je réponds à <em>toutes</em> vos questions : certifications ISO, conseils business, rédaction, calculs et bien plus !
            </div>
        </div>
        <!-- Quick-reply suggestions -->
        <div class="quick-replies" id="quick-replies-bar">
            <button class="quick-reply-btn" onclick="sendQuickReply('Quelle certification me convient ?')">💡 Quelle certification ?</button>
            <button class="quick-reply-btn" onclick="sendQuickReply('Comment préparer un audit ISO ?')">📋 Préparer un audit</button>
            <button class="quick-reply-btn" onclick="sendQuickReply('Explique-moi ISO 27001')">🔒 ISO 27001</button>
            <button class="quick-reply-btn" onclick="sendQuickReply('Quels sont mes quick wins ?')">⚡ Quick wins</button>
        </div>
        <div class="chatbot-footer">
            <button id="btn-speaker" onclick="toggleVoice()" title="Activer/Désactiver la voix"><i class="fa-solid fa-volume-xmark"></i></button>
            <input type="text" id="chatbot-input" placeholder="Posez n'importe quelle question..." onkeypress="handleChatKeyPress(event)">
            <button id="btn-mic" onclick="startSpeechRecognition()" title="Parler"><i class="fa-solid fa-microphone"></i></button>
            <button onclick="sendMessage()"><i class="fa-solid fa-paper-plane"></i></button>
        </div>
    </div>

    <!-- Script de logique d'évaluation dynamique et de chart -->
    <script>


        
        // ─── SPA ROUTER LOGIC ───
        function showView(viewName) {
            document.querySelectorAll('.view-section').forEach(el => el.style.display = 'none');
            document.getElementById('view-' + viewName).style.display = 'block';

            if(viewName === 'manual') {
                const store = document.getElementById('global-cert-store');
                const manualList = document.getElementById('manual-cert-list');
                const cards = Array.from(store.querySelectorAll('.interactive-card'));
                cards.forEach(card => {
                    card.style.display = 'block'; // Reset display
                    manualList.appendChild(card);
                });
            }
            
            if(viewName === 'quiz') {
                // Check if we have saved answers
                const saved = localStorage.getItem('digitadvisory_quiz');
                if(saved) {
                    document.getElementById('quiz-resume-banner').style.display = 'block';
                    try {
                        const parsed = JSON.parse(saved);
                        quizAnswers = { ...quizAnswers, ...parsed };
                        document.getElementById('quiz-goal-input').value = parsed.objectif || '';
                        // Auto jump to last step if they have prior answers
                        goToStep(8);
                    } catch(e) {}
                }
            }
        }

        function filterManual() {
            const input = document.getElementById('manual-search').value.toLowerCase();
            const list = document.getElementById('manual-cert-list');
            const cards = list.querySelectorAll('.interactive-card');
            
            cards.forEach(card => {
                const text = card.innerText.toLowerCase();
                if(text.includes(input)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function toggleAccordion(accId, btnEl) {
            const acc = document.getElementById(accId);
            if (acc.classList.contains('expanded')) {
                acc.classList.remove('expanded');
                btnEl.classList.remove('open');
            } else {
                acc.classList.add('expanded');
                btnEl.classList.add('open');
            }
        }

        function calculateScore(certId) {
            const container = document.getElementById('acc-' + certId);
            const totalPoints = parseInt(container.getAttribute('data-total'));
            const checkboxes = container.querySelectorAll('.chk-' + certId);
            
            let currentPoints = 0;
            checkboxes.forEach(cb => {
                if(cb.checked) {
                    currentPoints += parseInt(cb.value);
                }
            });

            // Calculate percentage
            let percentage = 0;
            if (totalPoints > 0) {
                percentage = Math.round((currentPoints / totalPoints) * 100);
            }

            // Update UI Elements
            const progressBar = document.getElementById('progress-' + certId);
            const scoreText = document.getElementById('score-text-' + certId);
            const messageEl = document.getElementById('message-' + certId);

            progressBar.style.width = percentage + '%';
            scoreText.innerText = percentage + '%';

            // Change colors based on progress
            if (percentage < 40) {
                progressBar.style.backgroundColor = 'var(--danger)';
                scoreText.style.color = 'var(--danger)';
                messageEl.innerText = "Il vous reste beaucoup de travail pour être certifié.";
            } else if (percentage < 80) {
                progressBar.style.backgroundColor = 'var(--accent)'; // Yellow/Orange
                scoreText.style.color = 'var(--accent)';
                messageEl.innerText = "Vous êtes sur la bonne voie, continuez !";
            } else if (percentage < 100) {
                progressBar.style.backgroundColor = 'var(--primary)';
                scoreText.style.color = 'var(--primary)';
                messageEl.innerText = "Presque prêt pour l'audit final !";
            } else {
                progressBar.style.backgroundColor = 'var(--success)';
                scoreText.style.color = 'var(--success)';
                messageEl.innerText = "Félicitations, vous répondez à toutes les exigences !";
                if (percentage === 100 && totalPoints > 0) {
                    confetti({
                        particleCount: 150,
                        spread: 90,
                        origin: { y: 0.6 }
                    });
                    // Proposer la demande d'audit après un court délai
                    setTimeout(() => {
                        Swal.fire({
                            title: '🎉 Félicitations !',
                            html: 'Vous remplissez <strong>100%</strong> des critères pour cette norme !<br><br>Souhaitez-vous soumettre une <strong>demande d\'audit officiel</strong> ?',
                            icon: 'success',
                            showCancelButton: true,
                            confirmButtonText: '<i class="fa-solid fa-file-signature"></i> Demander un audit',
                            cancelButtonText: 'Plus tard',
                            confirmButtonColor: '#10b981'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Demande envoyée !',
                                    html: 'Votre demande d\'audit a été transmise à notre équipe.<br>Vous serez contacté sous <strong>48h</strong>.',
                                    timer: 4000,
                                    showConfirmButton: false
                                });
                            }
                        });
                    }, 1500);
                }
            }
        }

        // ─── CHATBOT ENTREPRISE LOGIC ───
        const chatbotContainer = document.getElementById('chatbot-container');
        const chatbotBody = document.getElementById('chatbot-body');
        const chatbotInput = document.getElementById('chatbot-input');
        
        function toggleChatbot() {
            chatbotContainer.classList.toggle('collapsed');
            const bubble = document.getElementById('chatbot-bubble');
            if(chatbotContainer.classList.contains('collapsed')) {
                bubble.innerHTML = '<i class="fa-solid fa-robot"></i>';
            } else {
                bubble.innerHTML = '<i class="fa-solid fa-times"></i>';
                chatbotInput.focus();
            }
        }
        
        let voiceEnabled = false;
        
        function toggleVoice() {
            voiceEnabled = !voiceEnabled;
            const btn = document.getElementById('btn-speaker');
            if(voiceEnabled) {
                btn.innerHTML = '<i class="fa-solid fa-volume-high"></i>';
                btn.style.background = 'var(--success)';
            } else {
                btn.innerHTML = '<i class="fa-solid fa-volume-xmark"></i>';
                btn.style.background = '';
                window.speechSynthesis.cancel();
            }
        }

        function speakResponse(text) {
            if(!voiceEnabled) return;
            const cleanText = text.replace(/<[^>]*>?/gm, '');
            const utterance = new SpeechSynthesisUtterance(cleanText);
            utterance.lang = 'fr-FR';
            utterance.rate = 1.0;
            window.speechSynthesis.speak(utterance);
        }

        function startSpeechRecognition() {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            if(!SpeechRecognition) {
                Swal.fire('Erreur', 'Votre navigateur ne supporte pas la reconnaissance vocale.', 'error');
                return;
            }
            
            const recognition = new SpeechRecognition();
            recognition.lang = 'fr-FR';
            recognition.interimResults = false;
            recognition.maxAlternatives = 1;
            
            const btnMic = document.getElementById('btn-mic');
            btnMic.style.background = 'var(--danger)';
            btnMic.style.color = 'white';
            
            recognition.start();
            
            recognition.onresult = function(event) {
                const speechResult = event.results[0][0].transcript;
                document.getElementById('chatbot-input').value = speechResult;
                sendMessage();
            };
            
            recognition.onspeechend = function() {
                recognition.stop();
                btnMic.style.background = '';
                btnMic.style.color = '';
            };
            
            recognition.onerror = function(event) {
                btnMic.style.background = '';
                btnMic.style.color = '';
            };
        }
        

        async function generateRoadmap(certId, certTitre) {
            const container = document.getElementById('acc-' + certId);
            const checkboxes = container.querySelectorAll('.chk-' + certId);
            const missingCriteres = [];
            
            checkboxes.forEach(cb => {
                if(!cb.checked) {
                    missingCriteres.push(cb.getAttribute('data-title'));
                }
            });

            if (missingCriteres.length === 0) {
                Swal.fire('Parfait', 'Vous avez déjà validé tous les critères !', 'success');
                return;
            }

            Swal.fire({
                title: 'Génération en cours...',
                html: 'L\'IA analyse vos lacunes et prépare le plan d\'action...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const formData = new FormData();
            formData.append('action', 'ajax_generate_roadmap');
            formData.append('cert_titre', certTitre);
            formData.append('missing_criteres', JSON.stringify(missingCriteres));

            try {
                const response = await fetch('../../Controller/GenerateTemplateController.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.success) {
                    Swal.close();
                    
                    Swal.fire({
                        title: 'Votre Roadmap IA est prête !',
                        html: '<div style="text-align: left; max-height: 55vh; overflow-y: auto; background: white; padding: 1.5rem; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 0.95rem; color: #334155; line-height: 1.6; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);">' + data.html + '</div>',
                        width: '800px',
                        showCancelButton: true,
                        confirmButtonText: '<i class="fa-solid fa-download"></i> Télécharger en Word',
                        cancelButtonText: 'Fermer',
                        confirmButtonColor: '#2563eb'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const htmlDoc = "<!DOCTYPE html><html><head><meta charset=\"utf-8\"><title>Roadmap</title><style>body{font-family:Arial,sans-serif;line-height:1.6;color:#333;margin:2rem;}h1{color:#1d4ed8;border-bottom:2px solid #1d4ed8;padding-bottom:10px;}h2{color:#2563eb;margin-top:20px;}ul{margin-bottom:15px;}li{margin-bottom:8px;}strong{color:#1e293b;}</style></head><body>" + data.html + "</body></html>";
                            const docx = htmlDocx.asBlob(htmlDoc);
                            saveAs(docx, "Roadmap-" + certTitre.replace(/\s+/g, '-') + ".docx");
                            
                            Swal.fire({
                                icon: 'success',
                                title: 'Téléchargement réussi',
                                text: 'Votre Roadmap a été enregistrée sur votre appareil.',
                                timer: 3000,
                                showConfirmButton: false
                            });
                        }
                    });
                    
                } else {
                    Swal.fire('Erreur', data.message || 'Erreur inconnue', 'error');
                }
            } catch (err) {
                Swal.fire('Erreur', 'Impossible de joindre le serveur.', 'error');
            }
        }

        const TOTAL_STEPS = 8;
        let quizAnswers = { secteur: '', taille: '', clientele: '', existant: '', priorite: '', lacune: '', maturite: '', objectif: '' };
        let resultsChartInstance = null;

        function goToStep(stepNumber) {
            document.querySelectorAll('.quiz-step').forEach(step => {
                step.style.opacity = '0';
                step.style.transform = 'translateX(60px)';
                step.style.pointerEvents = 'none';
                step.style.zIndex = '1';
            });
            const target = document.getElementById('q-step-' + stepNumber);
            if(target) {
                target.style.opacity = '1';
                target.style.transform = 'translateX(0)';
                target.style.pointerEvents = 'auto';
                target.style.zIndex = '2';
            }
            document.getElementById('quiz-step-indicator').innerText = 'Étape ' + stepNumber + ' / ' + TOTAL_STEPS;
            
            const phaseTitle = document.getElementById('quiz-phase-title');
            if (phaseTitle) {
                if (stepNumber <= 4) {
                    phaseTitle.innerText = "Phase 1 : Profil & Contexte de l'Entreprise";
                } else if (stepNumber <= 8) {
                    phaseTitle.innerText = "Phase 2 : Évaluation des Besoins & Enjeux";
                }
            }

            // Update dot progress
            const dots = document.querySelectorAll('#quiz-dots .q-dot');
            dots.forEach((dot, i) => {
                dot.style.background = i < stepNumber ? 'var(--accent)' : 'rgba(255,255,255,0.1)';
            });
        }

        function selectQuizOption(stepNumber, answer, btnEl) {
            // Visual selected state
            if(btnEl) {
                const parent = btnEl.closest('[id^="q"][id$="-options"]') || btnEl.parentElement;
                parent.querySelectorAll('.quiz-btn').forEach(b => b.classList.remove('selected'));
                btnEl.classList.add('selected');
            }
            if(stepNumber === 1) quizAnswers.secteur = answer;
            if(stepNumber === 2) quizAnswers.taille = answer;
            if(stepNumber === 3) quizAnswers.clientele = answer;
            if(stepNumber === 4) quizAnswers.existant = answer;
            if(stepNumber === 5) quizAnswers.priorite = answer;
            if(stepNumber === 6) quizAnswers.lacune = answer;
            if(stepNumber === 7) quizAnswers.maturite = answer;

            // Auto-advance after a small delay for visual feedback
            setTimeout(() => goToStep(stepNumber + 1), 350);
        }

        function animateCounter(elementId, targetValue, suffix, duration) {
            const el = document.getElementById(elementId);
            const start = 0;
            const startTime = performance.now();
            function update(now) {
                const elapsed = now - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3); // ease-out cubic
                const current = Math.round(start + (targetValue - start) * eased);
                el.textContent = current + (suffix || '');
                if(progress < 1) requestAnimationFrame(update);
            }
            requestAnimationFrame(update);
        }

        function populateResultStats(matchCount, totalCerts) {
            // Pertinence score
            const pertinence = Math.min(95, Math.round((matchCount / Math.max(totalCerts, 1)) * 100) + 60);
            animateCounter('stat-match', pertinence, '%', 1200);
            // Time estimate
            const timeEst = quizAnswers.taille.includes('250') ? 12 : (quizAnswers.taille.includes('10') ? 4 : 8);
            animateCounter('stat-time', timeEst, '', 800);
            // Certs found
            animateCounter('stat-certs', matchCount, '', 600);
            // Risk level
            const riskMap = { 'Pas de documentation formelle': 'Élevé', 'Processus non standardises': 'Moyen', 'Formation insuffisante des equipes': 'Moyen', 'Pas d audit interne': 'Élevé' };
            document.getElementById('stat-risk').textContent = riskMap[quizAnswers.lacune] || 'Faible';
            
            // Tags
            const tagsEl = document.getElementById('ai-tags');
            tagsEl.innerHTML = '';
            const tags = [quizAnswers.secteur, quizAnswers.taille, quizAnswers.priorite].filter(Boolean);
            tags.forEach(tag => {
                const span = document.createElement('span');
                span.style.cssText = 'background: rgba(245,158,11,0.15); color: var(--accent); padding: 0.3rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600;';
                span.textContent = tag;
                tagsEl.appendChild(span);
            });

            // Chart
            const ctx = document.getElementById('resultsChart');
            if(resultsChartInstance) resultsChartInstance.destroy();
            resultsChartInstance = new Chart(ctx, {
                type: 'polarArea',
                data: {
                    labels: ['Documentation', 'Formation', 'Processus', 'Audit Interne', 'Conformité'],
                    datasets: [{
                        data: [
                            quizAnswers.lacune.includes('documentation') ? 30 : 80,
                            quizAnswers.lacune.includes('Formation') ? 25 : 75,
                            quizAnswers.lacune.includes('Processus') ? 35 : 85,
                            quizAnswers.lacune.includes('audit') ? 20 : 70,
                            pertinence
                        ],
                        backgroundColor: ['rgba(37,99,235,0.6)','rgba(245,158,11,0.6)','rgba(16,185,129,0.6)','rgba(239,68,68,0.5)','rgba(99,102,241,0.5)'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12, font: { size: 11 } } } },
                    scales: { r: { ticks: { display: false }, grid: { color: 'rgba(0,0,0,0.05)' } } },
                    animation: { animateScale: true, animateRotate: true, duration: 1500 }
                }
            });
        }

        async function finishQuiz() {
            const input = document.getElementById('quiz-goal-input').value.trim();
            if (!input) {
                Swal.fire('Information', 'Veuillez décrire brièvement votre objectif.', 'warning');
                return;
            }
            quizAnswers.objectif = input;

            // Show loading animation
            document.querySelectorAll('.quiz-step').forEach(step => {
                step.style.opacity = '0';
                step.style.transform = 'scale(0.9)';
                step.style.pointerEvents = 'none';
            });
            const loadingStep = document.getElementById('q-step-loading');
            loadingStep.style.opacity = '1';
            loadingStep.style.transform = 'scale(1)';
            document.getElementById('quiz-step-indicator').innerText = 'Analyse IA...';
            document.querySelectorAll('#quiz-dots .q-dot').forEach(d => d.style.background = '#10b981');

            const aiPrompt = "Nous sommes une entreprise du secteur " + quizAnswers.secteur + ", taille " + quizAnswers.taille + ", avec une clientèle " + quizAnswers.clientele + ". Certifications existantes : " + quizAnswers.existant + ". Notre maturité numérique actuelle est : " + quizAnswers.maturite + ". Notre priorité est : " + quizAnswers.priorite + ". Nos lacunes : " + quizAnswers.lacune + ". Notre objectif principal est : " + quizAnswers.objectif;

            try {
                const response = await fetch('../../Controller/ChatbotController.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: aiPrompt, recommendation_mode: true })
                });
                const data = await response.json();
                
                if (data.reply) {
                    if (data.reply.startsWith('⚠️')) {
                        Swal.fire('Erreur API', data.reply.substring(2), 'error');
                        goToStep(1);
                        return;
                    }
                    try {
                        let jsonStr = data.reply.trim();
                        
                        // Extraction robuste du JSON
                        const startIdx = jsonStr.indexOf('{');
                        const endIdx = jsonStr.lastIndexOf('}');
                        
                        if (startIdx !== -1 && endIdx !== -1 && endIdx >= startIdx) {
                            jsonStr = jsonStr.substring(startIdx, endIdx + 1);
                        } else {
                            throw new Error("Aucun objet JSON trouvé dans la réponse");
                        }
                        
                        // Nettoyage intelligent : si OpenAI a mis des vrais sauts de ligne dans les chaînes, 
                        // cela casse JSON.parse. Mais on ne peut pas remplacer TOUS les \n car ça casse l'indentation.
                        // On utilise plutôt JSON.parse directement, l'API OpenAI avec json_object garantit un JSON valide.
                        
                        const recs = JSON.parse(jsonStr);
                        
                        // Sauvegarde des réponses ET des résultats IA
                        localStorage.setItem('digitadvisory_quiz', JSON.stringify(quizAnswers));
                        localStorage.setItem('digitadvisory_results', JSON.stringify({
                            recs: recs,
                            timestamp: Date.now()
                        }));
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Analyse terminée !',
                            html: 'Votre rapport de diagnostic est prêt.<br><span style="font-size:0.9rem; color:#64748b;">Score : <strong>' + (recs.maturity_score||'?') + '/100</strong> — Risque : <strong>' + (recs.risk_level||'?') + '</strong></span>',
                            confirmButtonText: '<i class="fa-solid fa-download"></i> Télécharger le rapport',
                            confirmButtonColor: '#2563eb'
                        }).then(() => {
                            downloadDiagnosticReport(recs);
                            document.getElementById('quiz-goal-input').value = '';
                            goToStep(1);
                            showView('landing');
                        });
                    } catch(e) {
                        console.error("Erreur JSON:", e, "Données reçues:", data.reply);
                        // Display full response for debugging
                        Swal.fire({
                            title: 'Erreur de Décryptage',
                            html: '<p style="margin-bottom:10px;">La réponse de l\'IA a pu être récupérée mais le navigateur n\'arrive pas à la lire. Voici la réponse brute :</p><textarea style="width:100%; height:150px; font-family:monospace; font-size:12px; padding:10px; border:1px solid #ccc;">' + (typeof jsonStr !== 'undefined' ? jsonStr : data.reply) + '</textarea>',
                            icon: 'error'
                        });
                        goToStep(1);
                    }
                }
            } catch(e) {
                console.error(e);
                Swal.fire('Erreur', 'Problème de communication avec l\'IA.', 'error');
                goToStep(1);
            }
        }

        function handleChatKeyPress(e) { if(e.key === 'Enter') sendMessage(); }
        
        // ─── CHATBOT CONVERSATION MEMORY ───
        let conversationHistory = [];
        let currentDiagContext = null; // résultats du diagnostic injectés dans le chatbot

        // Récupérer le contexte diagnostic depuis localStorage si disponible
        function loadDiagContextFromStorage() {
            try {
                const stored = localStorage.getItem('digitadvisory_results');
                if (stored) {
                    const parsed = JSON.parse(stored);
                    if (parsed.recs) currentDiagContext = parsed.recs;
                }
            } catch(e) {}
        }

        async function sendMessage(customText) {
            const text = customText || chatbotInput.value.trim();
            if (!text) return;

            // Masquer les quick replies après le 1er message
            const qBar = document.getElementById('quick-replies-bar');
            if (qBar) qBar.style.display = 'none';

            let contextStr = "L'utilisateur est sur la page d'auto-évaluation.\n";
            try {
                document.querySelectorAll('.eval-container.expanded').forEach(container => {
                    const certId = container.id.replace('acc-', '');
                    const scoreText = document.getElementById('score-text-' + certId)?.innerText || '?';
                    contextStr += '- Norme ID ' + certId + ', score actuel : ' + scoreText + '.\n';
                });
            } catch(e) {}

            // Toujours ajouter le message à l'historique AVANT l'envoi
            conversationHistory.push({ role: 'user', content: text });

            addMessage(text, 'user');
            chatbotInput.value = '';
            chatbotInput.disabled = true;

            const typingId = showTypingIndicator();

            try {
                // Historique : max 10 messages (5 échanges)
                const historyToSend = conversationHistory.slice(-10);

                const payload = {
                    message: text,
                    clientContext: contextStr,
                    conversationHistory: historyToSend
                };

                // Injecter le diagnostic si disponible
                if (currentDiagContext) {
                    payload.diagnosticContext = currentDiagContext;
                }

                const response = await fetch('../../Controller/ChatbotController.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();
                document.getElementById(typingId)?.remove();

                if (data.reply) {
                    conversationHistory.push({ role: 'assistant', content: data.reply });
                    addMessage(data.reply, 'bot');
                    speakResponse(data.reply);
                    // ─── Gestion des actions UI déclenchées par le bot ───
                    if (data.action && data.action.type === 'navigate') {
                        setTimeout(() => showView(data.action.target), 800);
                    }
                } else {
                    addMessage('❌ Impossible de lire la réponse.', 'bot');
                }
            } catch (error) {
                document.getElementById(typingId)?.remove();
                addMessage('❌ Erreur de connexion au serveur.', 'bot');
            }

            chatbotInput.disabled = false;
            chatbotInput.focus();
        }

        // Envoyer une suggestion de réponse rapide
        function sendQuickReply(text) {
            chatbotInput.value = text;
            sendMessage();
            if (!chatbotContainer.classList.contains('collapsed')) return;
            toggleChatbot();
        }

        // Ouvrir le chatbot avec le contexte du diagnostic pré-chargé
        function openChatbotWithDiagContext() {
            if (chatbotContainer.classList.contains('collapsed')) toggleChatbot();
            const cached = localStorage.getItem('digitadvisory_results');
            if (cached) {
                try {
                    const d = JSON.parse(cached);
                    if (d.recs) {
                        currentDiagContext = d.recs;
                        const score = d.recs.maturity_score || '?';
                        const risk  = d.recs.risk_level || '?';
                        addMessage('<strong>🎯 Contexte chargé !</strong><br>Je connais votre diagnostic : score <strong>' + score + '/100</strong>, risque <strong>' + risk + '</strong>. Que voulez-vous approfondir ?', 'bot');
                    }
                } catch(e) {}
            }
        }
        
        function addMessage(text, sender) {
            const msgDiv = document.createElement('div');
            msgDiv.className = `chat-message ${sender}-message`;
            msgDiv.innerHTML = text;
            chatbotBody.appendChild(msgDiv);
            chatbotBody.scrollTop = chatbotBody.scrollHeight;
        }
        
        function showTypingIndicator() {
            const id = 'typing-' + Date.now();
            const div = document.createElement('div');
            div.id = id; div.className = 'chat-message bot-message';
            div.innerHTML = `<div class="typing-indicator"><div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div></div>`;
            chatbotBody.appendChild(div);
            chatbotBody.scrollTop = chatbotBody.scrollHeight;
            return id;
        }
        
        // ─── CACHED RESULTS RESTORE LOGIC ───
        function checkCachedResults() {
            const cached = localStorage.getItem('digitadvisory_results');
            if(cached) {
                try {
                    const data = JSON.parse(cached);
                    if (data.recs) {
                        const banner = document.getElementById('cached-results-banner');
                        banner.style.display = 'block';
                        const dateEl = document.getElementById('cached-results-date');
                        if(data.timestamp) {
                            const d = new Date(data.timestamp);
                            dateEl.textContent = 'Diagnostic du ' + d.toLocaleDateString('fr-FR') + ' à ' + d.toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'});
                        }
                    }
                } catch(e) {}
            }
        }
        
        function restoreCachedResults() {
            const cached = localStorage.getItem('digitadvisory_results');
            if(!cached) return;
            try {
                const data = JSON.parse(cached);
                if(data.recs) {
                    downloadDiagnosticReport(data.recs);
                }

                
                // Restore quiz answers for tags
                const savedQuiz = localStorage.getItem('digitadvisory_quiz');
                if(savedQuiz) {
                    try { quizAnswers = JSON.parse(savedQuiz); } catch(e) {}
                }
            } catch(e) {
                console.error('Erreur restauration:', e);
            }
        }
        
        function downloadDiagnosticReport(recs) {
            let html = `
                <div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
                    <h1 style="color: #1d4ed8; border-bottom: 2px solid #1d4ed8; padding-bottom: 10px;">Rapport de Diagnostic & Consulting DigitAdvisory</h1>
                    
                    <h2 style="color: #2563eb;">Score de Maturité : ${recs.maturity_score || '?'} / 100</h2>
                    <p><strong>Niveau de Risque Global :</strong> ${recs.risk_level || 'Non évalué'}</p>
                    
                    <h2 style="color: #2563eb;">Frameworks Recommandés</h2>
                    <ul>
                        ${(recs.frameworks_suggested || []).map(f => '<li>' + f + '</li>').join('')}
                    </ul>
                    
                    <h2 style="color: #dc2626;">Vulnérabilités Critiques (Gap Analysis)</h2>
                    <ul>
                        ${(recs.top_vulnerabilities || []).map(v => '<li>' + v + '</li>').join('')}
                    </ul>
                    
                    <h2 style="color: #16a34a;">Plan d'Action Stratégique (Roadmap) - Estimé : ${recs.estimated_months || '?'} mois</h2>
                    <ul>
                        ${(recs.roadmap || []).map(r => '<li><strong>Étape ' + r.step + ' : ' + r.title + '</strong><br>' + r.description + '</li>').join('')}
                    </ul>
                    
                    <h2 style="color: #2563eb;">Actions Immédiates (Quick Wins)</h2>
                    <ul>
                        ${(recs.quick_wins || []).map(q => '<li>' + q + '</li>').join('')}
                    </ul>
                </div>
            `;
            const htmlDoc = "<!DOCTYPE html><html><head><meta charset=\"utf-8\"><title>Diagnostic</title></head><body>" + html + "</body></html>";
            const docx = htmlDocx.asBlob(htmlDoc);
            saveAs(docx, "Diagnostic-DigitAdvisory.docx");
            
            Swal.fire({
                icon: 'success',
                title: 'Téléchargement en cours',
                text: 'Votre rapport de diagnostic a été généré.',
                timer: 3000,
                showConfirmButton: false
            });
        }
        
        function clearCachedResults() {
            localStorage.removeItem('digitadvisory_results');
            localStorage.removeItem('digitadvisory_quiz');
            document.getElementById('cached-results-banner').style.display = 'none';
            Swal.fire({ icon: 'info', title: 'Historique effacé', timer: 1500, showConfirmButton: false });
        }
        
        function populateResultStats(recs) {
            // 1. Maturity Score
            const score = recs.maturity_score || 0;
            animateCounter('maturity-score-val', score, '', 1500);
            setTimeout(() => {
                const circle = document.getElementById('maturity-circle');
                if(circle) circle.style.strokeDasharray = `${score}, 100`;
            }, 100);

            // 2. Risk Level
            const riskLevel = recs.risk_level || 'Modéré';
            const riskEl = document.getElementById('risk-level-badge');
            if(riskEl) {
                riskEl.innerText = riskLevel;
                if(riskLevel.toLowerCase().includes('critique') || riskLevel.toLowerCase().includes('élevé')) {
                    riskEl.style.background = 'rgba(239, 68, 68, 0.1)';
                    riskEl.style.color = 'var(--danger)';
                } else if(riskLevel.toLowerCase().includes('faible')) {
                    riskEl.style.background = 'rgba(16, 185, 129, 0.1)';
                    riskEl.style.color = 'var(--success)';
                } else {
                    riskEl.style.background = 'rgba(245, 158, 11, 0.1)';
                    riskEl.style.color = 'var(--accent)';
                }
            }

            // 3. Frameworks
            const frameworksList = document.getElementById('frameworks-list');
            if(frameworksList) {
                frameworksList.innerHTML = '';
                const frameworks = recs.frameworks_suggested || [];
                frameworks.forEach(fw => {
                    const span = document.createElement('span');
                    span.className = 'badge';
                    span.style.background = 'rgba(37, 99, 235, 0.1)';
                    span.style.color = 'var(--primary)';
                    span.style.fontSize = '0.9rem';
                    span.innerText = fw;
                    frameworksList.appendChild(span);
                });
            }

            // 4. Vulnerabilities
            const vulnList = document.getElementById('vulnerabilities-list');
            if(vulnList) {
                vulnList.innerHTML = '';
                const vulns = recs.top_vulnerabilities || [];
                vulns.forEach(v => {
                    const li = document.createElement('li');
                    li.style.display = 'flex';
                    li.style.alignItems = 'flex-start';
                    li.style.gap = '0.5rem';
                    li.style.color = '#334155';
                    li.innerHTML = `<i class="fa-solid fa-xmark text-danger" style="margin-top:0.25rem;"></i> <span>${v}</span>`;
                    vulnList.appendChild(li);
                });
            }

            // 5. Roadmap
            const roadmapContainer = document.getElementById('roadmap-container');
            if(roadmapContainer) {
                roadmapContainer.innerHTML = '';
                const roadmap = recs.roadmap || [];
                roadmap.forEach(step => {
                    const div = document.createElement('div');
                    div.className = 'roadmap-step';
                    div.innerHTML = `
                        <h4 style="color: var(--dark); margin-bottom: 0.25rem; font-size: 1.05rem;">Étape ${step.step} : ${step.title}</h4>
                        <p style="color: var(--gray); font-size: 0.9rem; line-height: 1.5; margin: 0;">${step.description}</p>
                    `;
                    roadmapContainer.appendChild(div);
                });
            }

            // 6. Peupler la vue post-résultats & mettre à jour le contexte chatbot
            if (typeof populatePostResults === 'function') populatePostResults(recs);
            currentDiagContext = recs;
        }

        // ─── POST-RESULTS PANEL LOGIC ───
        function populatePostResults(recs) {
            // Quick Wins
            const qwList = document.getElementById('quick-wins-list');
            if (qwList && recs.quick_wins && recs.quick_wins.length > 0) {
                qwList.innerHTML = '';
                recs.quick_wins.forEach((win, i) => {
                    const li = document.createElement('li');
                    li.innerHTML = '<div class="win-num">' + (i+1) + '</div><span>' + win + '</span>';
                    qwList.appendChild(li);
                });
            }
            // Timeline
            const months = recs.estimated_months || '?';
            const monthEl = document.getElementById('timeline-months');
            if (monthEl) monthEl.textContent = months;
            const bar = document.getElementById('timeline-bar');
            if (bar) {
                const pct = Math.min(100, Math.round((Math.min(months, 18) / 18) * 100));
                bar.style.width = pct + '%';
            }
        }

        function requestAuditFromResults() {
            Swal.fire({
                title: '📋 Demande d\'audit officiel',
                html: '<p style="margin-bottom:1rem; color:#475569;">Notre équipe d\'auditeurs vous contactera sous 48h pour planifier votre audit.</p>' +
                      '<input id="swal-email" class="swal2-input" placeholder="Votre email professionnel" type="email">' +
                      '<textarea id="swal-note" class="swal2-textarea" placeholder="Message optionnel (taille entreprise, urgence...)" style="height:80px;"></textarea>',
                showCancelButton: true,
                confirmButtonText: '<i class="fa-solid fa-paper-plane"></i> Envoyer la demande',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#2563eb',
                preConfirm: () => {
                    const email = document.getElementById('swal-email').value;
                    if (!email || !email.includes('@')) {
                        Swal.showValidationMessage('Veuillez entrer un email valide.');
                        return false;
                    }
                    return email;
                }
            }).then(result => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: '✅ Demande envoyée !',
                        html: 'Votre demande a été transmise à notre équipe.<br>Vous serez contacté à <strong>' + result.value + '</strong> sous <strong>48h</strong>.',
                        timer: 5000, showConfirmButton: false
                    });
                }
            });
        }

        function shareDiagnostic() {
            const cached = localStorage.getItem('digitadvisory_results');
            if (!cached) { Swal.fire('Aucun diagnostic', 'Effectuez d\'abord un diagnostic IA.', 'info'); return; }
            try {
                const d = JSON.parse(cached);
                const score = d.recs?.maturity_score || '?';
                const risk  = d.recs?.risk_level || '?';
                const frameworks = (d.recs?.frameworks_suggested || []).join(', ');
                const shareText = '🎯 Mon diagnostic DigitAdvisory :\n' +
                    '• Score de maturité : ' + score + '/100\n' +
                    '• Niveau de risque : ' + risk + '\n' +
                    '• Frameworks recommandés : ' + frameworks + '\n\n' +
                    'Obtenez votre diagnostic gratuit sur DigitAdvisory !';
                if (navigator.share) {
                    navigator.share({ title: 'Mon Diagnostic ISO — DigitAdvisory', text: shareText });
                } else {
                    navigator.clipboard.writeText(shareText).then(() => {
                        Swal.fire({ icon: 'success', title: 'Copié !', text: 'Le résumé a été copié dans votre presse-papiers.', timer: 2500, showConfirmButton: false });
                    });
                }
            } catch(e) { Swal.fire('Erreur', 'Impossible de partager le diagnostic.', 'error'); }
        }

        // ─── INITIALISATION ───
        // Vérifier les résultats en cache au chargement
        checkCachedResults();

        // Charger le contexte diagnostic depuis localStorage
        loadDiagContextFromStorage();

        // Afficher la vue par défaut
        showView('landing');

        // Start collapsed
        chatbotContainer.classList.add('collapsed');
    </script>
</body>
</html>













