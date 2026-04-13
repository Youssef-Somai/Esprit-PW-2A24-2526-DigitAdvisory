<?php
require_once __DIR__ . '/../../Controller/CertificatController.php';

$controller = new CertificatController();

// ─── DELETE action ───
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $controller->deleteCertificat((int) $_GET['id']);
    header('Location: back-certification.php');
    exit;
}

// ─── CREATE action (POST) ───
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $certificat = new Certificat(
        null,
        $_POST['norme'],
        $_POST['titre'],
        $_POST['description'],
        $_POST['organisme']
    );
    $controller->addCertificat($certificat);
    header('Location: back-certification.php');
    exit;
}

// ─── READ all ───
$certificats = $controller->listCertificats();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Back Office | Gestion Certifications ISO</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        .btn-sm { padding: 0.4rem 0.8rem; font-size: 0.85rem; }

        /* ─── Modal Overlay ─── */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 2000;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.25s ease;
        }
        .modal-overlay.active { display: flex; }

        .modal {
            background: white;
            border-radius: var(--radius-lg);
            padding: 2.5rem;
            width: 100%;
            max-width: 560px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.25);
            position: relative;
            animation: slideUp 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .modal h3 {
            font-size: 1.4rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .modal h3 i { color: var(--primary); }

        .modal-close {
            position: absolute;
            top: 1rem; right: 1rem;
            background: none; border: none;
            font-size: 1.3rem;
            color: var(--gray);
            cursor: pointer;
            transition: var(--transition);
            width: 36px; height: 36px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
        }
        .modal-close:hover { background: var(--gray-light); color: var(--dark); }

        /* ─── Form Styles ─── */
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.4rem;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--dark);
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.7rem 1rem;
            border: 2px solid var(--gray-light);
            border-radius: var(--radius);
            font-family: var(--font-main);
            font-size: 0.95rem;
            transition: var(--transition);
            background: #f8fafc;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .form-group textarea { resize: vertical; min-height: 80px; }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }
        .btn-cancel {
            background: var(--gray-light);
            color: var(--dark);
        }
        .btn-cancel:hover {
            background: #cbd5e1;
        }

        .btn-danger-outline {
            color: var(--danger);
            border-color: var(--danger);
        }
        .btn-danger-outline:hover {
            background: var(--danger);
            color: white;
        }

        /* ─── Empty State ─── */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--gray);
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--gray-light);
        }

        /* ─── Stats Row ─── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.25rem 1.5rem;
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: var(--transition);
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
        .stat-icon {
            width: 48px; height: 48px;
            border-radius: var(--radius);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
        }
        .stat-icon.blue   { background: rgba(37, 99, 235, 0.1); color: var(--primary); }
        .stat-icon.green  { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .stat-icon.amber  { background: rgba(245, 158, 11, 0.1); color: var(--accent); }
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
                    <div class="stat-icon green"><i class="fa-solid fa-shield-halved"></i></div>
                    <div>
                        <div class="stat-value"><?= count(array_filter($certificats, fn($c) => str_starts_with($c->getNorme(), 'ISO 27'))) ?></div>
                        <div class="stat-label">Sécurité Info</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon amber"><i class="fa-solid fa-building"></i></div>
                    <div>
                        <div class="stat-value"><?= count(array_unique(array_map(fn($c) => $c->getOrganisme(), $certificats))) ?></div>
                        <div class="stat-label">Organismes</div>
                    </div>
                </div>
            </div>

            <section class="fade-in-up">
                <div style="display: flex; justify-content: space-between; align-items: center;" class="mb-2">
                    <h2>Gestion Certifications ISO & Critères</h2>
                    <button class="btn btn-primary" id="btnOpenAdd"><i class="fa-solid fa-plus"></i> Ajouter Certification</button>
                </div>

                <div class="card admin-card">
                    <?php if (empty($certificats)): ?>
                        <div class="empty-state">
                            <i class="fa-solid fa-award"></i>
                            <h3>Aucune certification</h3>
                            <p>Commencez par ajouter une certification ISO.</p>
                        </div>
                    <?php else: ?>
                    <table class="data-table" id="certifTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Norme ISO</th>
                                <th>Titre</th>
                                <th>Description</th>
                                <th>Organisme</th>
                                <th>Date Ajout</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($certificats as $cert): ?>
                            <tr>
                                <td>#<?= htmlspecialchars($cert->getId()) ?></td>
                                <td><span class="badge primary"><?= htmlspecialchars($cert->getNorme()) ?></span></td>
                                <td><?= htmlspecialchars($cert->getTitre()) ?></td>
                                <td style="max-width: 250px; font-size: 0.88rem; color: var(--gray);"><?= htmlspecialchars($cert->getDescription() ?? '') ?></td>
                                <td><?= htmlspecialchars($cert->getOrganisme() ?? '') ?></td>
                                <td><span class="badge success"><?= htmlspecialchars($cert->getDateAjout() ?? '') ?></span></td>
                                <td style="white-space: nowrap;">
                                    <a href="updateCertificat.php?id=<?= $cert->getId() ?>" class="btn btn-outline btn-sm"><i class="fa-solid fa-pen"></i> Modifier</a>
                                    <a href="back-certification.php?action=delete&id=<?= $cert->getId() ?>" class="btn btn-outline btn-sm btn-danger-outline"><i class="fa-solid fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>

    <!-- ─── Modal Ajouter ─── -->
    <div class="modal-overlay" id="modalAdd">
        <div class="modal">
            <button class="modal-close" id="btnCloseAdd"><i class="fa-solid fa-xmark"></i></button>
            <h3><i class="fa-solid fa-plus-circle"></i> Nouvelle Certification ISO</h3>
            <form id="formAddCertif" method="POST" action="back-certification.php">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label for="norme">Norme ISO *</label>
                    <input type="text" id="norme" name="norme" placeholder="ex: ISO 27001">
                    <small id="error-norme" style="color:var(--danger); display:none; margin-top:5px;">Ce champ est obligatoire et doit commencer par 'ISO'.</small>
                </div>
                <div class="form-group">
                    <label for="titre">Titre *</label>
                    <input type="text" id="titre" name="titre" placeholder="ex: Sécurité de l'Information">
                    <small id="error-titre" style="color:var(--danger); display:none; margin-top:5px;">Ce champ est obligatoire et doit contenir au moins 3 caractères.</small>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="Description de la certification..."></textarea>
                </div>
                <div class="form-group">
                    <label for="organisme">Organisme</label>
                    <input type="text" id="organisme" name="organisme" placeholder="ex: ISO/IEC">
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-cancel" id="btnCancelAdd">Annuler</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // ─── Modal logic ───
        const modalAdd      = document.getElementById('modalAdd');
        const btnOpenAdd     = document.getElementById('btnOpenAdd');
        const btnCloseAdd    = document.getElementById('btnCloseAdd');
        const btnCancelAdd   = document.getElementById('btnCancelAdd');

        function openModal()  { modalAdd.classList.add('active'); }
        function closeModal() { modalAdd.classList.remove('active'); }

        btnOpenAdd.addEventListener('click', openModal);
        btnCloseAdd.addEventListener('click', closeModal);
        btnCancelAdd.addEventListener('click', closeModal);
        modalAdd.addEventListener('click', (e) => { if (e.target === modalAdd) closeModal(); });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });
        
        // ─── Contrôle de saisie JS ───
        const formAddCertif = document.getElementById('formAddCertif');
        formAddCertif.addEventListener('submit', function(e) {
            let isValid = true;
            
            const normeInput = document.getElementById('norme');
            const titreInput = document.getElementById('titre');
            const errorNorme = document.getElementById('error-norme');
            const errorTitre = document.getElementById('error-titre');
            
            // Validation de la Norme (ex: doit commencer par ISO)
            if (normeInput.value.trim() === '' || !normeInput.value.toUpperCase().startsWith('ISO')) {
                errorNorme.style.display = 'block';
                normeInput.style.borderColor = 'var(--danger)';
                isValid = false;
            } else {
                errorNorme.style.display = 'none';
                normeInput.style.borderColor = 'var(--gray-light)';
            }
            
            // Validation du Titre (minimum 3 caractères)
            if (titreInput.value.trim().length < 3) {
                errorTitre.style.display = 'block';
                titreInput.style.borderColor = 'var(--danger)';
                isValid = false;
            } else {
                errorTitre.style.display = 'none';
                titreInput.style.borderColor = 'var(--gray-light)';
            }
            
            if (!isValid) {
                e.preventDefault(); // Empêche la soumission du formulaire si invalide
            }
        });
    </script>
</body>
</html>
