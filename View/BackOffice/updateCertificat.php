<?php
require_once __DIR__ . '/../../Controller/CertificatController.php';

$controller = new CertificatController();

// ─── Check ID ───
if (!isset($_GET['id'])) {
    header('Location: back-certification.php');
    exit;
}

$id = (int) $_GET['id'];

// ─── UPDATE action (POST) ───
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $certificat = new Certificat(
        (int) $_POST['id'],
        $_POST['norme'],
        $_POST['titre'],
        $_POST['description'],
        $_POST['organisme']
    );
    $controller->updateCertificat($certificat);
    header('Location: back-certification.php');
    exit;
}

// ─── Fetch existing data ───
$cert = $controller->getCertificat($id);
if (!$cert) {
    header('Location: back-certification.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Back Office | Modifier Certification</title>
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
        .card { background: white; border-radius: var(--radius-lg); padding: 2rem; box-shadow: var(--shadow-sm); margin-bottom: 2rem; }
        .badge { padding: 0.25rem 0.75rem; border-radius: var(--radius-full); font-size: 0.85rem; font-weight: 500; display: inline-block;}

        /* ─── Form Styles ─── */
        .form-card {
            max-width: 680px;
            margin: 0 auto;
            border-top: 4px solid var(--primary);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--dark);
        }
        .form-group label .required { color: var(--danger); }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem 1rem;
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
        .form-group textarea { resize: vertical; min-height: 100px; }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--gray-light);
        }
        .btn-cancel {
            background: var(--gray-light);
            color: var(--dark);
        }
        .btn-cancel:hover {
            background: #cbd5e1;
        }

        .form-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--gray-light);
        }
        .form-header i { font-size: 1.5rem; color: var(--primary); }
        .form-header h3 { font-size: 1.3rem; }
        .form-header .badge { margin-left: auto; }
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
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <a href="back-certification.php" class="btn btn-outline btn-sm"><i class="fa-solid fa-arrow-left"></i> Retour</a>
                    <h2 style="margin: 0; font-size: 1.5rem;">Modifier Certification</h2>
                </div>
                <span class="badge" style="font-size: 1rem; background: rgba(245,158,11,0.1); color: var(--accent);"><i class="fa-solid fa-lock"></i> Espace Sécurisé Admin</span>
            </div>

            <section class="fade-in-up">
                <div class="card form-card">
                    <div class="form-header">
                        <i class="fa-solid fa-pen-to-square"></i>
                        <h3>Modification — <?= htmlspecialchars($cert->getNorme()) ?></h3>
                        <span class="badge primary">#<?= $cert->getId() ?></span>
                    </div>

                    <form id="formUpdateCertif" method="POST" action="updateCertificat.php?id=<?= $cert->getId() ?>">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= $cert->getId() ?>">

                        <div class="form-group">
                            <label for="norme">Norme ISO <span class="required">*</span></label>
                            <input type="text" id="norme" name="norme" value="<?= htmlspecialchars($cert->getNorme()) ?>">
                            <small id="error-norme" style="color:var(--danger); display:none; margin-top:5px;">Ce champ est obligatoire et doit commencer par 'ISO'.</small>
                        </div>
                        <div class="form-group">
                            <label for="titre">Titre <span class="required">*</span></label>
                            <input type="text" id="titre" name="titre" value="<?= htmlspecialchars($cert->getTitre()) ?>">
                            <small id="error-titre" style="color:var(--danger); display:none; margin-top:5px;">Ce champ est obligatoire et doit contenir au moins 3 caractères.</small>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description"><?= htmlspecialchars($cert->getDescription() ?? '') ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="organisme">Organisme</label>
                            <input type="text" id="organisme" name="organisme" value="<?= htmlspecialchars($cert->getOrganisme() ?? '') ?>">
                        </div>

                        <div class="form-actions">
                            <a href="back-certification.php" class="btn btn-cancel">Annuler</a>
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Enregistrer les modifications</button>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    </div>

    <script>
        // ─── Contrôle de saisie JS ───
        const formUpdateCertif = document.getElementById('formUpdateCertif');
        formUpdateCertif.addEventListener('submit', function(e) {
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
                e.preventDefault(); // Empêche la soumission du formulaire
            }
        });
    </script>
</body>
</html>
