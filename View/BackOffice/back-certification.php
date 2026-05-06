<?php
require_once __DIR__ . '/../../Controller/CertificatController.php';
require_once __DIR__ . '/../../Controller/CritereController.php';

$certifController = new CertificatController();
$critereController = new CritereController();


// ─── SUCCESS MESSAGES ───
$successMsg = '';
$errorMsg = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'add_certif') $successMsg = "Certification ajoutée avec succès !";
    if ($_GET['success'] === 'delete_certif') $successMsg = "Certification supprimée !";
    if ($_GET['success'] === 'update_certif') $successMsg = "Certification modifiée !";
    
    if ($_GET['success'] === 'add_critere') $successMsg = "Critère ajouté avec succès !";
    if ($_GET['success'] === 'delete_critere') $successMsg = "Critère supprimé !";
    if ($_GET['success'] === 'update_critere') $successMsg = "Critère modifié !";

    if ($_GET['success'] === 'sync_criteres') $successMsg = "Critères liés avec succès !";
    if ($_GET['success'] === 'generate_template') $successMsg = "Modèle généré par IA et enregistré !";
}

if (isset($_GET['error'])) {
    $errorMsg = trim((string) $_GET['error']);
}

// ─── READ all ───
$certificats = $certifController->listCertificats();
$criteres = $critereController->listCriteres();

// ─── STATISTIQUES AVANCÉES ───
$stats_actifs = count(array_filter($certificats, fn($c) => $c->getStatut() === 'Actif'));
$stats_obligatoires = count(array_filter($criteres, fn($c) => $c->getEstObligatoire() == 1 || $c->getEstObligatoire() === true));

// Données pour les graphiques (Charts)
$chart_statuts = ['Actif' => 0, 'En révision' => 0, 'Obsolète' => 0];
foreach ($certificats as $c) {
    if (isset($chart_statuts[$c->getStatut()])) $chart_statuts[$c->getStatut()]++;
}

$chart_categories = [];
foreach ($criteres as $c) {
    $cat = $c->getCategorie();
    if (!isset($chart_categories[$cat])) $chart_categories[$cat] = 0;
    $chart_categories[$cat]++;
}

// MAP for Many-to-Many logic
// Pour chaque objet certificat, on va attacher via un attribut dynamique les IDs des critères qui lui sont liés
foreach ($certificats as $cert) {
    $cert->linked_criteres = $certifController->getCriteresByCertificat($cert->getId());
}

// Default Tab
$activeTab = $_GET['tab'] ?? 'certifs';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Back Office | Gestion Certifications ISO</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <script src="https://unpkg.com/html-docx-js/dist/html-docx.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .data-table th { color: var(--gray); font-weight: 500; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.05em; }
        .data-table tbody tr { transition: var(--transition); }
        .data-table tbody tr:hover { background: rgba(37, 99, 235, 0.03); }
        .badge { padding: 0.25rem 0.75rem; border-radius: var(--radius-full); font-size: 0.85rem; font-weight: 500; display: inline-block;}
        .badge.primary { background: rgba(37, 99, 235, 0.1); color: var(--primary); }
        .badge.success { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .badge.info { background: rgba(14, 165, 233, 0.1); color: var(--primary); }
        .btn-sm { padding: 0.4rem 0.8rem; font-size: 0.85rem; }

        /* ─── TABS ─── */
        .tabs-header {
            display: flex; gap: 1rem; margin-bottom: 1.5rem;
            border-bottom: 2px solid var(--gray-light); padding-bottom: 0px;
        }
        .tab-btn {
            background: none; border: none; padding: 0.8rem 1.5rem; font-size: 1rem; font-weight: 600; color: var(--gray);
            cursor: pointer; position: relative; transition: var(--transition); margin-bottom: -2px;
        }
        .tab-btn i { margin-right: 0.5rem; }
        .tab-btn:hover { color: var(--primary); }
        .tab-btn.active { color: var(--primary); border-bottom: 3px solid var(--primary); }
        
        .tab-content { display: none; animation: fadeIn 0.3s ease; }
        .tab-content.active { display: block; }

        /* ─── Modal Overlay ─── */
        .modal-overlay {
            display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 2000;
            justify-content: center; align-items: center; animation: fadeIn 0.25s ease;
        }
        .modal-overlay.active { display: flex; }

        .modal {
            background: white; border-radius: var(--radius-lg); padding: 2.5rem;
            width: 100%; max-width: 560px; box-shadow: 0 25px 50px rgba(0,0,0,0.25);
            position: relative; animation: slideUp 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .modal h3 { font-size: 1.4rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; }
        .modal h3 i { color: var(--primary); }
        .modal-close {
            position: absolute; top: 1rem; right: 1rem; background: none; border: none; font-size: 1.3rem;
            color: var(--gray); cursor: pointer; transition: var(--transition); width: 36px; height: 36px;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
        }
        .modal-close:hover { background: var(--gray-light); color: var(--dark); }

        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; margin-bottom: 0.4rem; font-weight: 600; font-size: 0.9rem; color: var(--dark); }
        .form-group input[type="text"], .form-group input[type="number"], .form-group textarea, .form-group select {
            width: 100%; padding: 0.7rem 1rem; border: 2px solid var(--gray-light); border-radius: var(--radius);
            font-family: var(--font-main); font-size: 0.95rem; transition: var(--transition); background: #f8fafc;
        }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
            outline: none; border-color: var(--primary); background: white; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .form-group textarea { resize: vertical; min-height: 80px; }

        /* Custom Checkbox Grid for Syncing */
        .checkbox-grid {
            display: grid; grid-template-columns: 1fr; gap: 0.8rem;
            max-height: 300px; overflow-y: auto; padding-right: 1rem; margin-top: 1rem;
        }
        .checkbox-item {
            display: flex; align-items: center; padding: 0.75rem 1rem;
            border: 1px solid var(--gray-light); border-radius: var(--radius);
            transition: var(--transition); cursor: pointer;
        }
        .checkbox-item:hover { background: #f8fafc; border-color: var(--primary); }
        .checkbox-item input[type="checkbox"] { margin-right: 1rem; transform: scale(1.2); }
        .checkbox-item span { font-weight: 500; font-size: 0.95rem; }
        .checkbox-item small { display: block; color: var(--gray); font-size: 0.8rem; margin-left: auto; }

        .form-actions { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; }
        .btn-cancel { background: var(--gray-light); color: var(--dark); }
        .btn-cancel:hover { background: #cbd5e1; }
        .btn-danger-outline { color: var(--danger); border-color: var(--danger); }
        .btn-danger-outline:hover { background: var(--danger); color: white; }

        .empty-state { text-align: center; padding: 3rem 1rem; color: var(--gray); }
        .empty-state i { font-size: 3rem; margin-bottom: 1rem; color: var(--gray-light); }
        .stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: white; border-radius: var(--radius-lg); padding: 1.25rem 1.5rem; box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 1rem; transition: var(--transition); }
        .stat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
        .stat-icon { width: 48px; height: 48px; border-radius: var(--radius); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .stat-icon.blue   { background: rgba(37, 99, 235, 0.1); color: var(--primary); }
        .stat-icon.green  { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .stat-value { font-size: 1.5rem; font-weight: 700; font-family: var(--font-heading); }
        .stat-label { font-size: 0.8rem; color: var(--gray); }
        /* ─── CHATBOT DYNAMIQUE CSS ─── */
        .chatbot-container {
            position: fixed; bottom: 2rem; right: 2rem; width: 370px; background: white;
            border-radius: 1rem; box-shadow: 0 10px 40px rgba(0,0,0,0.15); z-index: 1000;
            display: flex; flex-direction: column; overflow: hidden; transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            transform-origin: bottom right; height: 500px; max-height: calc(100vh - 100px);
        }
        .chatbot-container.collapsed { height: 60px !important; }
        .chatbot-header {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: white; padding: 1rem 1.25rem; display: flex; justify-content: space-between;
            align-items: center; cursor: pointer; user-select: none; min-height: 60px;
        }
        .chatbot-header:hover { background: linear-gradient(135deg, #334155 0%, #475569 100%); }
        .chatbot-avatar { width: 36px; height: 36px; background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
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

        /* Sortable column cursor */
        .sortable { cursor: pointer; user-select: none; transition: color 0.2s; }
        .sortable:hover { color: var(--primary); }
        .sortable.asc i { transform: rotate(180deg); }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .ck-editor__editable_inline { min-height: 400px; }
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
                <a href="back-portfolio.php" class="menu-item"><i class="fa-solid fa-folder-open"></i> Gestion Portfolios</a>
                <a href="back-offres.php" class="menu-item"><i class="fa-solid fa-briefcase"></i> Gestion Offres</a>
                <a href="back-certification.php" class="menu-item active"><i class="fa-solid fa-award"></i> Gestion Certifications</a>
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
                <span class="badge warning" style="font-size: 1rem; background: rgba(245,158,11,0.1); color: var(--accent);"><i class="fa-solid fa-lock"></i> Espace Sécurisé Admin</span>
            </div>

            <!-- ─── Statistics Row ─── -->
            <div class="stats-row fade-in-up">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fa-solid fa-award"></i></div>
                    <div>
                        <div class="stat-value"><?= count($certificats) ?></div>
                        <div class="stat-label">Certifications ISO</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fa-solid fa-list-ol"></i></div>
                    <div>
                        <div class="stat-value"><?= count($criteres) ?></div>
                        <div class="stat-label">Critères Globaux</div>
                    </div>
                </div>
            </div>

            <!-- ─── Dynamic Charts Section (Nouveaux Graphiques) ─── -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;" class="fade-in-up">
                <div class="card" style="margin-bottom: 0;">
                    <h3 style="margin-top: 0; font-size: 1rem; color: var(--gray); margin-bottom: 1rem;"><i class="fa-solid fa-asterisk text-primary"></i> Complexité (Critères/Norme)</h3>
                    <div style="height: 250px; position: relative;"><canvas id="polarChart"></canvas></div>
                </div>
                <div class="card" style="margin-bottom: 0;">
                    <h3 style="margin-top: 0; font-size: 1rem; color: var(--gray); margin-bottom: 1rem;"><i class="fa-solid fa-spider text-accent"></i> Analyse de Difficulté</h3>
                    <div style="height: 250px; position: relative;"><canvas id="radarChart"></canvas></div>
                </div>
                <div class="card" style="margin-bottom: 0;">
                    <h3 style="margin-top: 0; font-size: 1rem; color: var(--gray); margin-bottom: 1rem;"><i class="fa-solid fa-circle-exclamation text-danger"></i> Exigences Bloquantes</h3>
                    <div style="height: 250px; position: relative;"><canvas id="doughnutChart"></canvas></div>
                </div>
            </div>

            <section class="fade-in-up">
                
                <div class="tabs-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <button class="tab-btn <?= $activeTab === 'certifs' ? 'active' : '' ?>" onclick="openTab('certifs')">
                            <i class="fa-solid fa-award"></i> Certifications ISO
                        </button>
                        <button class="tab-btn <?= $activeTab === 'criteres' ? 'active' : '' ?>" onclick="openTab('criteres')">
                            <i class="fa-solid fa-list-check"></i> Critères (Globaux)
                        </button>
                    </div>
                    
                    <!-- ─── RECHERCHE DYNAMIQUE ─── -->
                    <div style="position: relative; width: 300px;">
                        <i class="fa-solid fa-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--gray);"></i>
                        <input type="text" id="searchInput" placeholder="Rechercher..." 
                            style="width: 100%; padding: 0.6rem 1rem 0.6rem 2.5rem; border: 1px solid var(--gray-light); border-radius: 2rem; outline: none; font-family: var(--font-main);"
                            onkeyup="filterTablesAndUpdateCharts()">
                    </div>
                </div>

                <!-- ─── TAB : CERTIFICATIONS ─── -->
                <div id="tab-certifs" class="tab-content <?= $activeTab === 'certifs' ? 'active' : '' ?>">
                    <div class="card admin-card">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 1.5rem; align-items: center;">
                            <h3 style="margin: 0; font-size: 1.1rem; color: var(--gray);">Liste des Certifications</h3>
                            <div style="display: flex; gap: 1rem; align-items: center;">
                                <select id="sortCertifs" onchange="sortDataBySelect('tableCertifs', this.value, 'certifs')" style="padding: 0.5rem; border-radius: var(--radius); border: 1px solid var(--gray-light); font-family: var(--font-main); outline: none;">
                                    <option value="">-- Trier par --</option>
                                    <option value="complexite_desc">Le plus complexe (nb critères)</option>
                                    <option value="complexite_asc">Le moins complexe</option>
                                    <option value="statut_logique">Statut (Actif en premier)</option>
                                    <option value="id_desc">ID (Plus récent)</option>
                                    <option value="id_asc">ID (Plus ancien)</option>
                                </select>
                                <button class="btn btn-outline" onclick="exportTableToCSV('tableCertifs', 'Certifications_ISO.csv')" title="Exporter en CSV"><i class="fa-solid fa-file-csv"></i> Exporter</button>
                                <button class="btn btn-primary" onclick="openModalAddCertif()"><i class="fa-solid fa-plus"></i></button>
                            </div>
                        </div>
                        <?php if (empty($certificats)): ?>
                            <div class="empty-state">
                                <i class="fa-solid fa-award"></i>
                                <h3>Aucune certification</h3>
                                <p>Commencez par ajouter une certification ISO.</p>
                            </div>
                        <?php else: ?>
                        <table class="data-table" id="tableCertifs">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Norme ISO</th>
                                    <th>Titre</th>
                                    <th>Statut / Version</th>
                                    <th>Critères Liés</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($certificats as $cert): ?>
                                <tr>
                                    <td>#<?= htmlspecialchars($cert->getId()) ?></td>
                                    <td><span class="badge primary"><?= htmlspecialchars($cert->getNorme()) ?></span></td>
                                    <td><strong><?= htmlspecialchars($cert->getTitre()) ?></strong></td>
                                    <td>
                                        <span class="badge <?= $cert->getStatut() === 'Actif' ? 'success' : 'warning' ?>"><?= htmlspecialchars($cert->getStatut()) ?></span>
                                        <small style="color:var(--gray);">&nbsp;v.<?= htmlspecialchars($cert->getVersion()) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge info"><i class="fa-solid fa-link"></i> <?= count($certifController->getCriteresByCertificat($cert->getId())) ?> Critères</span>
                                    </td>
                                    <td style="white-space: nowrap;">
                                        <!-- Actions Modif / Delete -->
                                        <a href="updateCertificat.php?id=<?= $cert->getId() ?>" class="btn btn-outline btn-sm" title="Modifier"><i class="fa-solid fa-pen"></i></a>
                                        <a href="../../Controller/CertificatController.php?action=delete_certif&id=<?= $cert->getId() ?>" class="btn btn-outline btn-sm btn-danger-outline btn-delete" data-type="Certification" title="Supprimer"><i class="fa-solid fa-trash"></i></a>
                                        <!-- Lien Many-to-Many -->
                                        <!-- On stocke les IDs liés et leurs Poids dans un objet JSON -->
                                        <button class="btn btn-primary btn-sm" style="margin-left: 0.5rem;" 
                                            onclick="openModalSync(<?= $cert->getId() ?>, '<?= htmlspecialchars($cert->getNorme()) ?>', <?= htmlspecialchars(json_encode($certifController->getCriteresByCertificat($cert->getId()))) ?>)">
                                            <i class="fa-solid fa-list-check"></i> Gérer Critères
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ─── TAB : CRITÈRES GLOBAUX ─── -->
                <div id="tab-criteres" class="tab-content <?= $activeTab === 'criteres' ? 'active' : '' ?>">
                    <div class="card admin-card">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 1.5rem; align-items: center;">
                            <h3 style="margin: 0; font-size: 1.1rem; color: var(--gray);">Base des Critères</h3>
                            <div style="display: flex; gap: 1rem; align-items: center;">
                                <select id="sortCriteres" onchange="sortDataBySelect('tableCriteres', this.value, 'criteres')" style="padding: 0.5rem; border-radius: var(--radius); border: 1px solid var(--gray-light); font-family: var(--font-main); outline: none;">
                                    <option value="">-- Trier par --</option>
                                    <option value="obligatoire_first">Urgence (Obligatoires d'abord)</option>
                                    <option value="difficulte_asc">Difficulté (Facile -> Difficile)</option>
                                    <option value="difficulte_desc">Difficulté (Difficile -> Facile)</option>
                                    <option value="id_desc">ID (Plus récent)</option>
                                    <option value="id_asc">ID (Plus ancien)</option>
                                </select>
                                <button class="btn btn-outline" onclick="exportTableToCSV('tableCriteres', 'Criteres_ISO.csv')" title="Exporter en CSV"><i class="fa-solid fa-file-csv"></i> Exporter</button>
                                <button class="btn btn-primary" onclick="openModalAddCritere()"><i class="fa-solid fa-plus"></i></button>
                            </div>
                        </div>
                        <?php if (empty($criteres)): ?>
                            <div class="empty-state">
                                <i class="fa-solid fa-list-ol"></i>
                                <h3>Aucun critère global</h3>
                                <p>Créez des critères d'évaluation de base.</p>
                            </div>
                        <?php else: ?>
                        <table class="data-table" id="tableCriteres">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Catégorie</th>
                                    <th>Nom du Critère</th>
                                    <th>Obligatoire</th>
                                    <th>Difficulté</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($criteres as $critere): ?>
                                <tr>
                                    <td>#<?= htmlspecialchars($critere->getId()) ?></td>
                                    <td><span class="badge primary"><?= htmlspecialchars($critere->getCategorie()) ?></span></td>
                                    <td>
                                        <strong><?= htmlspecialchars($critere->getNom()) ?></strong><br>
                                        <small style="color:var(--gray);">&rarr; Preuve: <?= htmlspecialchars($critere->getMoyenPreuve() ?? 'Aucune spécifiée') ?></small>
                                    </td>
                                    <td><?= $critere->getEstObligatoire() ? '<span class="badge" style="background:#fecdd3; color:#e11d48;">Oui</span>' : '<span class="badge" style="background:#e2e8f0; color:#64748b;">Non</span>' ?></td>
                                    <td><span class="badge warning"><?= htmlspecialchars($critere->getDifficulte()) ?></span></td>
                                    <td style="white-space: nowrap;">
                                        <?php if ($critere->getDocumentTemplate()): ?>
                                            <a href="<?= htmlspecialchars($critere->getDocumentTemplate()) ?>" target="_blank" class="btn btn-outline btn-sm" title="Télécharger le modèle"><i class="fa-solid fa-download"></i></a>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-outline btn-sm" title="Générer un modèle avec l'IA" onclick="generateTemplateAjax(<?= $critere->getId() ?>)"><i class="fa-solid fa-wand-magic-sparkles"></i></button>
                                        <a href="updateCritere.php?id=<?= $critere->getId() ?>" class="btn btn-outline btn-sm"><i class="fa-solid fa-pen"></i></a>
                                        <a href="../../Controller/CritereController.php?action=delete_critere&id=<?= $critere->getId() ?>" class="btn btn-outline btn-sm btn-danger-outline btn-delete" data-type="Critère"><i class="fa-solid fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- ─── Modal : Ajouter Certification ─── -->
    <div class="modal-overlay" id="modalAddCertif">
        <div class="modal">
            <button class="modal-close" onclick="closeModal('modalAddCertif')"><i class="fa-solid fa-xmark"></i></button>
            <h3><i class="fa-solid fa-plus-circle"></i> Nouvelle Certification ISO</h3>
            <form id="formAddCertif" method="POST" action="../../Controller/CertificatController.php">
                <input type="hidden" name="action" value="add_certif">
                <div style="display:flex; gap: 1rem;">
                    <div class="form-group" style="flex:1;">
                        <label for="norme">Norme ISO *</label>
                        <input type="text" id="norme" name="norme" placeholder="ex: ISO 27001">
                        <small id="error-norme" style="color:var(--danger); display:none; margin-top:5px;">Doit commencer par 'ISO'.</small>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label for="version">Version</label>
                        <input type="text" id="version" name="version" placeholder="ex: 2022" value="2022">
                    </div>
                </div>
                <div class="form-group">
                    <label for="titre">Titre *</label>
                    <input type="text" id="titre" name="titre" placeholder="ex: Sécurité de l'Information">
                    <small id="error-titre" style="color:var(--danger); display:none; margin-top:5px;">Minimum 3 caractères.</small>
                </div>
                <div style="display:flex; gap: 1rem;">
                    <div class="form-group" style="flex:1;">
                        <label for="statut">Statut</label>
                        <select id="statut" name="statut">
                            <option value="Actif">Actif</option>
                            <option value="En révision">En révision</option>
                            <option value="Obsolète">Obsolète</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label for="duree_validite">Validité (mois)</label>
                        <input type="number" id="duree_validite" name="duree_validite" value="36" min="12">
                    </div>
                </div>
                <div class="form-group">
                    <label for="description">Description (Optionnel)</label>
                    <textarea id="description" name="description"></textarea>
                </div>
                <div class="form-group">
                    <label for="organisme">Organisme</label>
                    <input type="text" id="organisme" name="organisme" placeholder="ex: ISO/IEC">
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-cancel" onclick="closeModal('modalAddCertif')">Annuler</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ─── Modal : Ajouter Critère Global ─── -->
    <div class="modal-overlay" id="modalAddCritere">
        <div class="modal">
            <button class="modal-close" onclick="closeModal('modalAddCritere')"><i class="fa-solid fa-xmark"></i></button>
            <h3><i class="fa-solid fa-plus-circle"></i> Nouveau Critère</h3>
            <form id="formAddCritere" method="POST" action="../../Controller/CritereController.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_critere">
                <div class="form-group">
                    <label for="nom">Nom du Critère *</label>
                    <input type="text" id="nom" name="nom" placeholder="ex: Politique de sécurité">
                    <small id="error-nom" style="color:var(--danger); display:none; margin-top:5px;">Minimum 3 caractères.</small>
                </div>
                <div class="form-group">
                    <label for="categorie">Catégorie *</label>
                    <select id="categorie" name="categorie">
                        <option value="Organisationnel">Organisationnel</option>
                        <option value="Technique">Technique</option>
                        <option value="Ressources Humaines">Ressources Humaines</option>
                        <option value="Juridique & Conformité">Juridique & Conformité</option>
                        <option value="Management">Management</option>
                        <option value="Général">Général</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="description_critere">Description contextuelle</label>
                    <textarea id="description_critere" name="description"></textarea>
                </div>
                <div class="form-group">
                    <label for="moyen_preuve">Preuve Attendue (Moyens de preuve) *</label>
                    <textarea id="moyen_preuve" name="moyen_preuve" placeholder="ex: Fournir le document PDF validé par la direction."></textarea>
                    <small id="error-preuve" style="color:var(--danger); display:none; margin-top:5px;">La preuve est requise (min 10 caractères).</small>
                </div>
                <div style="display:flex; gap: 1rem;">
                    <div class="form-group" style="flex:1;">
                        <label for="difficulte">Difficulté</label>
                        <select id="difficulte" name="difficulte">
                            <option value="Facile">Facile</option>
                            <option value="Moyen" selected>Moyen</option>
                            <option value="Difficile">Difficile</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1; display:flex; align-items:center; gap: 0.5rem; margin-top: 1.5rem;">
                        <input type="checkbox" id="est_obligatoire" name="est_obligatoire" checked style="transform: scale(1.5);">
                        <label for="est_obligatoire" style="margin:0; font-size:1rem;">Critère Bloquant (Obligatoire)</label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="document_template">Lien Modèle (URL) ou Upload Fichier</label>
                    <input type="text" name="document_template" placeholder="ex: https://... (ou laissez vide si upload)">
                    <input type="file" name="template_file" style="margin-top: 0.5rem; background: transparent; border: none; padding: 0;">
                    <small style="display:block; margin-top:0.5rem; color: var(--gray);">Après enregistrement, vous pourrez aussi générer automatiquement un modèle HTML via OpenAI depuis la liste des critères.</small>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-cancel" onclick="closeModal('modalAddCritere')">Annuler</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ─── Modal : Lier Critères (Many to Many) ─── -->
    <div class="modal-overlay" id="modalSyncCriteres">
        <div class="modal" style="max-width: 600px;">
            <button class="modal-close" onclick="closeModal('modalSyncCriteres')"><i class="fa-solid fa-xmark"></i></button>
            <h3><i class="fa-solid fa-link"></i> Lier Critères — <span id="syncCertifNom" class="text-primary"></span></h3>
            <p style="color: var(--gray); font-size: 0.9rem; margin-bottom: 1rem;">Cochez les critères globaux que vous souhaitez appliquer à cette certification.</p>
            
            <form id="formSync" method="POST" action="../../Controller/CertificatController.php">
                <input type="hidden" name="action" value="sync_criteres">
                <input type="hidden" name="certificat_id" id="syncCertifId" value="">
                
                <div class="checkbox-grid">
                    <?php if (empty($criteres)): ?>
                        <div style="text-align: center; color: var(--danger); padding: 1rem;">Aucun critère global existant. Allez dans l'onglet Critères pour en créer.</div>
                    <?php else: ?>
                        <?php foreach ($criteres as $critere): ?>
                        <label class="checkbox-item" id="lbl-critere-<?= $critere->getId() ?>">
                            <input type="checkbox" name="criteres[]" value="<?= $critere->getId() ?>" class="chk-critere">
                            <span style="flex: 1;"><?= htmlspecialchars($critere->getNom()) ?></span>
                            <div style="display:flex; align-items:center; gap: 0.5rem;" onclick="event.stopPropagation()">
                                <small style="margin:0;">Poids :</small>
                                <input type="number" name="poids_<?= $critere->getId() ?>" class="input-poids" style="width: 60px; padding: 0.2rem; border-color:var(--gray-light);" min="1" max="20" value="1" disabled>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-cancel" onclick="closeModal('modalSyncCriteres')">Annuler</button>
                    <button type="submit" class="btn btn-primary" <?= empty($criteres) ? 'disabled' : '' ?>><i class="fa-solid fa-save"></i> Enregistrer la liaison</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ─── CHATBOT DYNAMIQUE UI ─── -->
    <div class="chatbot-container" id="chatbot-container">
        <div class="chatbot-header" onclick="toggleChatbot()">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div class="chatbot-avatar"><i class="fa-solid fa-robot"></i></div>
                <div>
                    <h4 style="margin:0; font-size:0.95rem;">DigitBot IA</h4>
                    <span style="font-size:0.75rem; color:#10b981; display:flex; align-items:center; gap:0.25rem;"><span style="width:6px; height:6px; background:#10b981; border-radius:50%; display:inline-block;"></span> Assistant En ligne</span>
                </div>
            </div>
            <i class="fa-solid fa-chevron-up" id="chatbot-toggle-icon"></i>
        </div>
        <div class="chatbot-body" id="chatbot-body">
            <div class="chat-message bot-message">
                Bonjour Admin ! 👋 Je suis <strong>DigitBot</strong>. Avez-vous besoin d'aide avec vos certifications ou de voir les statistiques ?
            </div>
        </div>
        <div class="chatbot-footer">
            <button id="btn-speaker" onclick="toggleVoice()" title="Activer/Désactiver la voix"><i class="fa-solid fa-volume-xmark"></i></button>
            <input type="text" id="chatbot-input" placeholder="Demandez-moi quelque chose..." onkeypress="handleChatKeyPress(event)">
            <button id="btn-mic" onclick="startSpeechRecognition()" title="Parler"><i class="fa-solid fa-microphone"></i></button>
            <button onclick="sendMessage()"><i class="fa-solid fa-paper-plane"></i></button>
        </div>
    </div>

    <script>
        // ─── TABS LOGIC ───
        function openTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.getElementById('tab-' + tabName).classList.add('active');
            event.currentTarget.classList.add('active');
            const url = new URL(window.location);
            url.searchParams.set('tab', tabName);
            window.history.pushState({}, '', url);
        }

        // ─── MODAL LOGIC ───
        function openModalAddCertif() { document.getElementById('modalAddCertif').classList.add('active'); }
        function openModalAddCritere() { document.getElementById('modalAddCritere').classList.add('active'); }
        function closeModal(id) { document.getElementById(id).classList.remove('active'); }

        // Many-To-Many Sync Modal
        function openModalSync(certid, certNorme, linkedData) {
            document.getElementById('syncCertifNom').innerText = certNorme;
            document.getElementById('syncCertifId').value = certid;
            
            // Uncheck all and disable inputs
            document.querySelectorAll('.chk-critere').forEach(checkbox => {
                checkbox.checked = false;
                checkbox.parentElement.style.borderColor = 'var(--gray-light)';
                checkbox.parentElement.style.background = 'transparent';
                
                const inputPoids = checkbox.parentElement.querySelector('.input-poids');
                if(inputPoids) {
                    inputPoids.disabled = true;
                    inputPoids.value = 1;
                }
            });
            
            // Check the ones that are already linked and set their weight
            // linkedData is an object: { 'critere_id': poids }
            for (const [id, poids] of Object.entries(linkedData)) {
                const cb = document.querySelector(`.checkbox-item input[value="${id}"]`);
                if(cb) {
                    cb.checked = true;
                    // Styling
                    cb.parentElement.style.borderColor = 'var(--primary)';
                    cb.parentElement.style.background = '#f8fafc';
                    
                    // Enable and set weight input
                    const inputPoids = cb.parentElement.querySelector('.input-poids');
                    if(inputPoids) {
                        inputPoids.disabled = false;
                        inputPoids.value = poids;
                    }
                }
            }

            document.getElementById('modalSyncCriteres').classList.add('active');
        }

        // Highlight box on check and enable/disable weight input
        document.querySelectorAll('.chk-critere').forEach(cb => {
            cb.addEventListener('change', function() {
                const inputPoids = this.parentElement.querySelector('.input-poids');
                if(this.checked) {
                    this.parentElement.style.borderColor = 'var(--primary)';
                    this.parentElement.style.background = '#f8fafc';
                    if(inputPoids) inputPoids.disabled = false;
                } else {
                    this.parentElement.style.borderColor = 'var(--gray-light)';
                    this.parentElement.style.background = 'transparent';
                    if(inputPoids) inputPoids.disabled = true;
                }
            });
        });

        window.onclick = function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                event.target.classList.remove('active');
            }
        }
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('active'));
            }
        });
        
        // ─── VALIDATION CERTIFICAT ───
        document.getElementById('formAddCertif')?.addEventListener('submit', function(e) {
            let isValid = true;
            const normeInput = document.getElementById('norme');
            const titreInput = document.getElementById('titre');
            const errorNorme = document.getElementById('error-norme');
            const errorTitre = document.getElementById('error-titre');
            
            if (normeInput.value.trim() === '' || !normeInput.value.toUpperCase().startsWith('ISO')) {
                errorNorme.style.display = 'block'; normeInput.style.borderColor = 'var(--danger)'; isValid = false;
            } else {
                errorNorme.style.display = 'none'; normeInput.style.borderColor = 'var(--gray-light)';
            }
            if (titreInput.value.trim().length < 3) {
                errorTitre.style.display = 'block'; titreInput.style.borderColor = 'var(--danger)'; isValid = false;
            } else {
                errorTitre.style.display = 'none'; titreInput.style.borderColor = 'var(--gray-light)';
            }
            if (!isValid) e.preventDefault();
        });

        // ─── VALIDATION CRITÈRE ───
        document.getElementById('formAddCritere')?.addEventListener('submit', function(e) {
            let isValid = true;
            const nomInput    = document.getElementById('nom');
            const preuveInput = document.getElementById('moyen_preuve');
            const poidsInput  = document.getElementById('poids');

            if (nomInput.value.trim().length < 3) {
                document.getElementById('error-nom').style.display = 'block'; nomInput.style.borderColor = 'var(--danger)'; isValid = false;
            } else {
                document.getElementById('error-nom').style.display = 'none'; nomInput.style.borderColor = 'var(--gray-light)';
            }
            
            if (preuveInput && preuveInput.value.trim().length < 10) {
                document.getElementById('error-preuve').style.display = 'block'; preuveInput.style.borderColor = 'var(--danger)'; isValid = false;
            } else if(preuveInput) {
                document.getElementById('error-preuve').style.display = 'none'; preuveInput.style.borderColor = 'var(--gray-light)';
            }

            if (!isValid) {
                e.preventDefault();
                // Optional: show a small toast error if the user misses it
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: 'Veuillez vérifier les champs obligatoires.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            }
        });

        // ─── CONFIRMATION SUPPRESSION ───
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.getAttribute('href');
                const typeName = this.getAttribute('data-type');
                Swal.fire({
                    title: 'Êtes-vous sûr ?',
                    text: `La donnée sera supprimée définitivement !`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Oui, supprimer !',
                    cancelButtonText: 'Annuler',
                    backdrop: `rgba(15, 23, 42, 0.6)`
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                })
            });
        });

        // ─── TOAST NOTIFICATIONS ───
        <?php if (!empty($successMsg)): ?>
            const Toast = Swal.mixin({
                toast: true, position: 'bottom-end', showConfirmButton: false, timer: 4000,
                timerProgressBar: true, background: '#10b981', color: 'white', iconColor: 'white'
            });
            Toast.fire({ icon: 'success', title: '<?= addslashes($successMsg) ?>' });
            
            // Clean URL from success msg but keep tab
            const url = new URL(window.location);
            url.searchParams.delete('success');
            window.history.replaceState(null, null, url);
        <?php endif; ?>

        <?php if (!empty($errorMsg)): ?>
            Swal.fire({
                icon: 'error',
                title: 'Génération impossible',
                text: '<?= addslashes($errorMsg) ?>',
                confirmButtonColor: '#2563eb',
                backdrop: `rgba(15, 23, 42, 0.6)`
            });

            const errorUrl = new URL(window.location);
            errorUrl.searchParams.delete('error');
            window.history.replaceState(null, null, errorUrl);
        <?php endif; ?>

        // ─── DYNAMIC CHATBOT LOGIC ───
        const chatbotContainer = document.getElementById('chatbot-container');
        const chatbotBody = document.getElementById('chatbot-body');
        const chatbotInput = document.getElementById('chatbot-input');
        
        function toggleChatbot() {
            chatbotContainer.classList.toggle('collapsed');
            const icon = document.getElementById('chatbot-toggle-icon');
            if(chatbotContainer.classList.contains('collapsed')) {
                icon.classList.remove('fa-chevron-down'); icon.classList.add('fa-chevron-up');
            } else {
                icon.classList.remove('fa-chevron-up'); icon.classList.add('fa-chevron-down');
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
        
        function handleChatKeyPress(e) { if(e.key === 'Enter') sendMessage(); }
        
        async function sendMessage() {
            const text = chatbotInput.value.trim();
            if(!text) return;
            
            // Add User Message
            addMessage(text, 'user');
            chatbotInput.value = '';
            chatbotInput.disabled = true;
            
            // Show typing indicator
            const typingId = showTypingIndicator();
            
            try {
                // Appel au backend PHP qui communique avec Google Gemini
                const response = await fetch('../../Controller/ChatbotController.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: text })
                });
                
                const data = await response.json();
                document.getElementById(typingId)?.remove();
                
                if (data.reply) {
                    addMessage(data.reply, 'bot');
                    speakResponse(data.reply);
                } else {
                    addMessage("❌ Erreur : Impossible de lire la réponse de l'API.", 'bot');
                }
            } catch (error) {
                document.getElementById(typingId)?.remove();
                console.error("Erreur Chatbot:", error);
                addMessage("❌ Oups, je n'arrive pas à contacter le serveur. Vérifiez votre connexion.", 'bot');
            }
            
            chatbotInput.disabled = false;
            chatbotInput.focus();
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
        
        // Initialize Chatbot to collapsed state
        chatbotContainer.classList.add('collapsed');

        // ─── NOUVEAUX GRAPHIQUES (CHART.JS) ───
        let polarChartInstance = null;
        let radarChartInstance = null;
        let doughnutChartInstance = null;

        function initCharts() {
            // Options communes
            const commonOptions = { responsive: true, maintainAspectRatio: false };

            if(document.getElementById('polarChart')) {
                polarChartInstance = new Chart(document.getElementById('polarChart'), {
                    type: 'polarArea',
                    data: { labels: [], datasets: [{ data: [], backgroundColor: ['rgba(59, 130, 246, 0.7)', 'rgba(16, 185, 129, 0.7)', 'rgba(245, 158, 11, 0.7)', 'rgba(239, 68, 68, 0.7)', 'rgba(139, 92, 246, 0.7)'] }] },
                    options: { ...commonOptions, plugins: { legend: { position: 'right' } } }
                });
            }

            if(document.getElementById('radarChart')) {
                radarChartInstance = new Chart(document.getElementById('radarChart'), {
                    type: 'radar',
                    data: { labels: ['Facile', 'Moyen', 'Difficile'], datasets: [{ label: 'Critères', data: [0,0,0], backgroundColor: 'rgba(37, 99, 235, 0.2)', borderColor: 'rgba(37, 99, 235, 1)', pointBackgroundColor: 'rgba(37, 99, 235, 1)' }] },
                    options: { ...commonOptions, elements: { line: { borderWidth: 3 } } }
                });
            }

            if(document.getElementById('doughnutChart')) {
                doughnutChartInstance = new Chart(document.getElementById('doughnutChart'), {
                    type: 'doughnut',
                    data: { labels: ['Obligatoires (Bloquants)', 'Optionnels'], datasets: [{ data: [0,0], backgroundColor: ['#ef4444', '#94a3b8'], hoverOffset: 4 }] },
                    options: { ...commonOptions, cutout: '70%', plugins: { legend: { position: 'bottom' } } }
                });
            }
            
            filterTablesAndUpdateCharts(); // Fill them with initial data
        }

        // ─── MISE À JOUR DYNAMIQUE DES GRAPHIQUES ET RECHERCHE ───
        function filterTablesAndUpdateCharts() {
            const input = document.getElementById("searchInput");
            const filter = input ? input.value.toLowerCase() : "";
            
            // Certifications Logic (Polar Area)
            const tableCertifs = document.getElementById("tableCertifs");
            const certifsData = {}; // Norme -> nb_criteres
            
            if (tableCertifs) {
                const tr = tableCertifs.getElementsByTagName("tr");
                for (let i = 1; i < tr.length; i++) {
                    let textValue = tr[i].textContent || tr[i].innerText;
                    if (textValue.toLowerCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                        // Extraire la norme et le nombre de critères
                        let norme = tr[i].cells[1].innerText.trim();
                        let critText = tr[i].cells[4].innerText.trim();
                        let nbMatch = critText.match(/\d+/);
                        let nb = nbMatch ? parseInt(nbMatch[0]) : 0;
                        if(nb > 0) certifsData[norme] = nb;
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }

            // Critères Logic (Radar & Doughnut)
            const tableCriteres = document.getElementById("tableCriteres");
            const diffCount = { 'Facile': 0, 'Moyen': 0, 'Difficile': 0 };
            const reqCount = { 'Oui': 0, 'Non': 0 };
            
            if (tableCriteres) {
                const tr = tableCriteres.getElementsByTagName("tr");
                for (let i = 1; i < tr.length; i++) {
                    let textValue = tr[i].textContent || tr[i].innerText;
                    if (textValue.toLowerCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                        let reqText = tr[i].cells[3].innerText.trim();
                        if(reqText === 'Oui') reqCount['Oui']++; else reqCount['Non']++;
                        
                        let diffText = tr[i].cells[4].innerText.trim();
                        if(diffCount[diffText] !== undefined) diffCount[diffText]++;
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }

            // Update Polar
            if (polarChartInstance) {
                polarChartInstance.data.labels = Object.keys(certifsData);
                polarChartInstance.data.datasets[0].data = Object.values(certifsData);
                polarChartInstance.update();
            }
            // Update Radar
            if (radarChartInstance) {
                radarChartInstance.data.datasets[0].data = [diffCount['Facile'], diffCount['Moyen'], diffCount['Difficile']];
                radarChartInstance.update();
            }
            // Update Doughnut
            if (doughnutChartInstance) {
                doughnutChartInstance.data.datasets[0].data = [reqCount['Oui'], reqCount['Non']];
                doughnutChartInstance.update();
            }
        }

        // Initialize charts on load
        window.addEventListener('DOMContentLoaded', initCharts);

        // ─── TRI INTELLIGENT (MENU DÉROULANT) ───
        function sortDataBySelect(tableId, sortValue, type) {
            if(!sortValue) return;
            const table = document.getElementById(tableId);
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));

            rows.sort((a, b) => {
                if (type === 'certifs') {
                    if (sortValue.startsWith('id_')) {
                        const valA = parseInt(a.cells[0].innerText.replace('#', ''));
                        const valB = parseInt(b.cells[0].innerText.replace('#', ''));
                        return sortValue === 'id_asc' ? valA - valB : valB - valA;
                    }
                    if (sortValue.startsWith('complexite_')) {
                        const valA = parseInt(a.cells[4].innerText.match(/\d+/) || [0]);
                        const valB = parseInt(b.cells[4].innerText.match(/\d+/) || [0]);
                        return sortValue === 'complexite_asc' ? valA - valB : valB - valA;
                    }
                    if (sortValue === 'statut_logique') {
                        const order = { 'Actif': 1, 'En révision': 2, 'Obsolète': 3 };
                        const valA = a.cells[3].innerText.split('v.')[0].trim();
                        const valB = b.cells[3].innerText.split('v.')[0].trim();
                        return (order[valA] || 9) - (order[valB] || 9);
                    }
                } else if (type === 'criteres') {
                    if (sortValue.startsWith('id_')) {
                        const valA = parseInt(a.cells[0].innerText.replace('#', ''));
                        const valB = parseInt(b.cells[0].innerText.replace('#', ''));
                        return sortValue === 'id_asc' ? valA - valB : valB - valA;
                    }
                    if (sortValue === 'obligatoire_first') {
                        const valA = a.cells[3].innerText.trim() === 'Oui' ? 1 : 2;
                        const valB = b.cells[3].innerText.trim() === 'Oui' ? 1 : 2;
                        return valA - valB;
                    }
                    if (sortValue.startsWith('difficulte_')) {
                        const order = { 'Facile': 1, 'Moyen': 2, 'Difficile': 3 };
                        const valA = order[a.cells[4].innerText.trim()] || 9;
                        const valB = order[b.cells[4].innerText.trim()] || 9;
                        return sortValue === 'difficulte_asc' ? valA - valB : valB - valA;
                    }
                }
                return 0;
            });

            rows.forEach(row => tbody.appendChild(row));
        }

        // ─── EXPORT CSV ───
        function exportTableToCSV(tableId, filename) {
            const table = document.getElementById(tableId);
            let csv = [];
            // Headers
            const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.innerText.trim());
            headers.pop(); // Remove "Actions" column
            csv.push(headers.join(';'));
            
            // Rows (only visible ones)
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                if(row.style.display !== 'none') {
                    const rowData = Array.from(row.querySelectorAll('td')).map(td => {
                        let text = td.innerText.trim().replace(/\n/g, ' ');
                        return `"${text}"`;
                    });
                    rowData.pop(); // Remove "Actions"
                    csv.push(rowData.join(';'));
                }
            });

            const csvFile = new Blob(["\uFEFF"+csv.join('\n')], {type: "text/csv;charset=utf-8;"});
            const downloadLink = document.createElement("a");
            downloadLink.download = filename;
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = "none";
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        }
    </script>

    <!-- --- Modal WYSIWYG --- -->
    <div class="modal-overlay" id="modalWysiwyg">
        <div class="modal" style="max-width: 800px; max-height: 90vh; overflow-y: auto;">
            <button class="modal-close" onclick="closeModalWysiwyg()"><i class="fa-solid fa-xmark"></i></button>
            <h3><i class="fa-solid fa-file-signature"></i> �dition du Mod�le IA</h3>
            <div id="editor-container" style="margin-bottom: 1.5rem;">
                <textarea id="wysiwyg-editor"></textarea>
            </div>
            <div class="form-actions" style="justify-content: space-between;">
                <button type="button" class="btn btn-outline" onclick="downloadDocx()" style="border-color:#2b579a; color:#2b579a;"><i class="fa-solid fa-file-word"></i> T�l�charger en DOCX</button>
                <div style="display:flex; gap:0.75rem;">
                    <button type="button" class="btn btn-cancel" onclick="closeModalWysiwyg()">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="saveTemplateAjax()"><i class="fa-solid fa-save"></i> Sauvegarder sur le serveur</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // --- WYSIWYG & AJAX GENERATION ---
        let editorInstance;
        let currentCritereId = null;

        ClassicEditor
            .create(document.querySelector('#wysiwyg-editor'))
            .then(editor => { editorInstance = editor; })
            .catch(error => { console.error(error); });

        function generateTemplateAjax(critereId) {
            currentCritereId = critereId;
            Swal.fire({
                title: 'G�n�ration en cours...',
                html: 'L\'IA r�dige le mod�le ISO, veuillez patienter (environ 15-30s).',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const formData = new FormData();
            formData.append('action', 'ajax_generate_template');
            formData.append('id', currentCritereId);

            fetch('../../Controller/GenerateTemplateController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();
                if (data.success) {
                    editorInstance.setData(data.html);
                    document.getElementById('modalWysiwyg').classList.add('active');
                } else {
                    Swal.fire('Erreur', data.message, 'error');
                }
            })
            .catch(err => {
                Swal.close();
                Swal.fire('Erreur', 'Erreur r�seau lors de la g�n�ration.', 'error');
            });
        }

        function closeModalWysiwyg() {
            document.getElementById('modalWysiwyg').classList.remove('active');
        }

        function downloadDocx() {
            if(!currentCritereId) return;
            const htmlContent = editorInstance.getData();
            const fullHtml = <!DOCTYPE html><html><head><meta charset="utf-8"><title>Modele ISO</title></head><body> + htmlContent + </body></html>;
            const converted = htmlDocx.asBlob(fullHtml);
            saveAs(converted, 'Modele_ISO_Critere_' + currentCritereId + '.docx');
        }

        function saveTemplateAjax() {
            if(!currentCritereId) return;
            Swal.fire({
                title: 'Sauvegarde...',
                html: 'Enregistrement du document DOCX sur le serveur.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const htmlContent = editorInstance.getData();
            const fullHtml = <!DOCTYPE html><html><head><meta charset="utf-8"><title>Modele ISO</title></head><body> + htmlContent + </body></html>;
            const convertedBlob = htmlDocx.asBlob(fullHtml);

            const formData = new FormData();
            formData.append('action', 'ajax_save_template');
            formData.append('id', currentCritereId);
            formData.append('template_file', convertedBlob, 'modele.docx');

            fetch('../../Controller/GenerateTemplateController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Succ�s', data.message, 'success').then(() => {
                        closeModalWysiwyg();
                        window.location.reload(); 
                    });
                } else {
                    Swal.fire('Erreur', data.message, 'error');
                }
            })
            .catch(err => {
                Swal.fire('Erreur', 'Erreur lors de la sauvegarde.', 'error');
            });
        }
    </script>
</body>
</html>





