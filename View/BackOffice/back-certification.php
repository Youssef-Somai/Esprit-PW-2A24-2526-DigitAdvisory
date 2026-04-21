<?php
require_once __DIR__ . '/../../Controller/CertificatController.php';
require_once __DIR__ . '/../../Controller/CritereController.php';

$certifController = new CertificatController();
$critereController = new CritereController();

// ─── DELETE actions ───
if (isset($_GET['action']) && isset($_GET['id'])) {
    if ($_GET['action'] === 'delete_certif') {
        $certifController->deleteCertificat((int) $_GET['id']);
        header('Location: back-certification.php?success=delete_certif&tab=certifs');
        exit;
    }
    if ($_GET['action'] === 'delete_critere') {
        $critereController->deleteCritere((int) $_GET['id']);
        header('Location: back-certification.php?success=delete_critere&tab=criteres');
        exit;
    }
}

// ─── POST actions (Create & Sync) ───
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // 1. Ajouter Certification
    if ($_POST['action'] === 'add_certif') {
        $certificat = new Certificat(
            null,
            $_POST['norme'],
            $_POST['titre'],
            $_POST['version'] ?? '2022',
            $_POST['statut'] ?? 'Actif',
            isset($_POST['duree_validite']) ? (int) $_POST['duree_validite'] : 36,
            $_POST['description'],
            $_POST['organisme'],
            null
        );
        $certifController->addCertificat($certificat);
        header('Location: back-certification.php?success=add_certif&tab=certifs');
        exit;
    }
    
    // 2. Ajouter Critère Global (Indépendant)
    if ($_POST['action'] === 'add_critere') {
        $critere = new Critere(
            null,
            $_POST['nom'],
            $_POST['categorie'] ?? 'Général',
            $_POST['description'],
            $_POST['moyen_preuve'] ?? null,
            isset($_POST['est_obligatoire']) ? 1 : 0,
            $_POST['difficulte'] ?? 'Moyen',
            $_POST['document_template'] ?? null
        );
        $critereController->addCritere($critere);
        header('Location: back-certification.php?success=add_critere&tab=criteres');
        exit;
    }

    // 3. Synchroniser les Critères (Many-to-Many)
    if ($_POST['action'] === 'sync_criteres') {
        $certificat_id = (int) $_POST['certificat_id'];
        $criteres_ids = $_POST['criteres'] ?? []; // Ce sera un tableau si des cases sont cochées
        $criteres_avec_poids = [];
        foreach ($criteres_ids as $c_id) {
            $poids = isset($_POST['poids_'.$c_id]) ? (int) $_POST['poids_'.$c_id] : 1;
            $criteres_avec_poids[$c_id] = $poids;
        }
        $certifController->syncCriteresForCertificat($certificat_id, $criteres_avec_poids);
        header('Location: back-certification.php?success=sync_criteres&tab=certifs');
        exit;
    }
}

// ─── SUCCESS MESSAGES ───
$successMsg = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'add_certif') $successMsg = "Certification ajoutée avec succès !";
    if ($_GET['success'] === 'delete_certif') $successMsg = "Certification supprimée !";
    if ($_GET['success'] === 'update_certif') $successMsg = "Certification modifiée !";
    
    if ($_GET['success'] === 'add_critere') $successMsg = "Critère ajouté avec succès !";
    if ($_GET['success'] === 'delete_critere') $successMsg = "Critère supprimé !";
    if ($_GET['success'] === 'update_critere') $successMsg = "Critère modifié !";

    if ($_GET['success'] === 'sync_criteres') $successMsg = "Critères liés avec succès !";
}

// ─── READ all ───
$certificats = $certifController->listCertificats();
$criteres = $critereController->listCriteres();

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

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
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

            <section class="fade-in-up">
                
                <div class="tabs-header">
                    <button class="tab-btn <?= $activeTab === 'certifs' ? 'active' : '' ?>" onclick="openTab('certifs')">
                        <i class="fa-solid fa-award"></i> Certifications ISO
                    </button>
                    <button class="tab-btn <?= $activeTab === 'criteres' ? 'active' : '' ?>" onclick="openTab('criteres')">
                        <i class="fa-solid fa-list-check"></i> Critères (Globaux)
                    </button>
                </div>

                <!-- ─── TAB : CERTIFICATIONS ─── -->
                <div id="tab-certifs" class="tab-content <?= $activeTab === 'certifs' ? 'active' : '' ?>">
                    <div class="card admin-card">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 1.5rem; align-items: center;">
                            <h3 style="margin: 0; font-size: 1.1rem; color: var(--gray);">Liste des Certifications</h3>
                            <div style="display: flex; gap: 1rem;">
                                <div style="position: relative; width: 250px;">
                                    <i class="fa-solid fa-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--gray);"></i>
                                    <input type="text" id="searchCertif" placeholder="Rechercher..." style="width: 100%; padding: 0.6rem 1rem 0.6rem 2.5rem; border: 1px solid var(--gray-light); border-radius: var(--radius); outline: none;">
                                </div>
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
                                        <a href="back-certification.php?action=delete_certif&id=<?= $cert->getId() ?>" class="btn btn-outline btn-sm btn-danger-outline btn-delete" data-type="Certification" title="Supprimer"><i class="fa-solid fa-trash"></i></a>
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
                            <div style="display: flex; gap: 1rem;">
                                <div style="position: relative; width: 250px;">
                                    <i class="fa-solid fa-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--gray);"></i>
                                    <input type="text" id="searchCritere" placeholder="Rechercher..." style="width: 100%; padding: 0.6rem 1rem 0.6rem 2.5rem; border: 1px solid var(--gray-light); border-radius: var(--radius); outline: none;">
                                </div>
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
                                        <a href="updateCritere.php?id=<?= $critere->getId() ?>" class="btn btn-outline btn-sm"><i class="fa-solid fa-pen"></i></a>
                                        <a href="back-certification.php?action=delete_critere&id=<?= $critere->getId() ?>" class="btn btn-outline btn-sm btn-danger-outline btn-delete" data-type="Critère"><i class="fa-solid fa-trash"></i></a>
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
            <form id="formAddCertif" method="POST" action="back-certification.php">
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
            <form id="formAddCritere" method="POST" action="back-certification.php">
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
                    <label for="document_template">Lien Modèle / Template (Optionnel)</label>
                    <input type="text" id="document_template" name="document_template" placeholder="ex: https://...">
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
            
            <form id="formSync" method="POST" action="back-certification.php">
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

        // ─── RECHERCHE ───
        function attachSearch(inputId, tableId) {
            document.getElementById(inputId)?.addEventListener('keyup', function() {
                let filter = this.value.toLowerCase();
                let rows = document.querySelectorAll('#' + tableId + ' tbody tr');
                rows.forEach(row => {
                    let text = row.innerText.toLowerCase();
                    row.style.display = text.indexOf(filter) > -1 ? '' : 'none';
                });
            });
        }
        attachSearch('searchCertif', 'tableCertifs');
        attachSearch('searchCritere', 'tableCriteres');

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
    </script>
</body>
</html>
