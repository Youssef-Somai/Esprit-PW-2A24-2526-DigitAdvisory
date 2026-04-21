<?php
require_once __DIR__ . '/../../Controller/CritereController.php';

$critereController = new CritereController();

$c_id = isset($_GET['id']) ? (int) $_GET['id'] : null;

// Handling POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) $_POST['id'];
    $critere = new Critere(
        $id,
        $_POST['nom'],
        $_POST['categorie'] ?? 'Général',
        $_POST['description'],
        $_POST['moyen_preuve'] ?? null,
        isset($_POST['est_obligatoire']) ? 1 : 0,
        $_POST['difficulte'] ?? 'Moyen',
        $_POST['document_template'] ?? null
    );
    $critereController->updateCritere($critere);
    header('Location: back-certification.php?success=update_critere&tab=criteres');
    exit;
}

if (!$c_id) {
    header('Location: back-certification.php?tab=criteres');
    exit;
}

$critere = $critereController->getCritere($c_id);
if (!$critere) {
    echo "Critère introuvable.";
    exit;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Critère | Back Office</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; margin-bottom: 0.4rem; font-weight: 600; font-size: 0.9rem; color: var(--dark); }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%; padding: 0.7rem 1rem; border: 2px solid var(--gray-light); border-radius: var(--radius);
            font-family: var(--font-main); font-size: 0.95rem; background: #f8fafc; transition: var(--transition);
        }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
            outline: none; border-color: var(--primary); background: white; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .form-group textarea { resize: vertical; min-height: 120px; }

        .form-actions { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 2rem; }
        .btn-cancel { background: var(--gray-light); color: var(--dark); text-decoration: none; padding: 0.6rem 1.5rem; border-radius: var(--radius); font-weight: 500; }
        .btn-cancel:hover { background: #cbd5e1; }
        .btn-primary { padding: 0.6rem 1.5rem; border: none; cursor: pointer; }

        .update-header { border-bottom: 1px solid var(--gray-light); padding-bottom: 1rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem; }
        .update-header i { font-size: 1.5rem; color: var(--primary); }
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
                <h2 style="margin: 0; font-size: 1.5rem;">Modification du Critère #<?= htmlspecialchars($critere->getId()) ?></h2>
                <a href="back-certification.php?tab=criteres" class="btn btn-outline" style="font-size: 0.9rem;"><i class="fa-solid fa-arrow-left"></i> Retour</a>
            </div>

            <section class="fade-in-up" style="max-width: 800px; margin: 0 auto;">
                <div class="card admin-card">
                    <div class="update-header">
                        <i class="fa-solid fa-pen-to-square"></i>
                        <h3 style="margin: 0; font-size: 1.25rem;">Formulaire d'Édition</h3>
                    </div>

                    <form id="formUpdateCritere" method="POST" action="updateCritere.php?id=<?= $critere->getId() ?>">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($critere->getId()) ?>">
                        
                        <div class="form-group">
                            <label for="nom">Nom du Critère *</label>
                            <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($critere->getNom()) ?>">
                            <small id="error-nom" style="color:var(--danger); display:none; margin-top:5px;">Ce champ est obligatoire et doit contenir au moins 3 caractères.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="categorie">Catégorie *</label>
                            <select id="categorie" name="categorie">
                                <?php
                                $categories = ['Organisationnel', 'Technique', 'Ressources Humaines', 'Juridique & Conformité', 'Management', 'Général'];
                                foreach ($categories as $cat) {
                                    $selected = ($critere->getCategorie() === $cat) ? 'selected' : '';
                                    echo "<option value=\"$cat\" $selected>$cat</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description contextuelle</label>
                            <textarea id="description" name="description"><?= htmlspecialchars($critere->getDescription() ?? '') ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="moyen_preuve">Preuve Attendue (Moyens de preuve) *</label>
                            <textarea id="moyen_preuve" name="moyen_preuve"><?= htmlspecialchars($critere->getMoyenPreuve() ?? '') ?></textarea>
                            <small id="error-preuve" style="color:var(--danger); display:none; margin-top:5px;">La preuve est requise (min 10 caractères).</small>
                        </div>
                        
                        <div style="display:flex; gap: 1rem;">
                            <div class="form-group" style="flex:1;">
                                <label for="difficulte">Difficulté</label>
                                <select id="difficulte" name="difficulte">
                                    <option value="Facile" <?= $critere->getDifficulte() === 'Facile' ? 'selected' : '' ?>>Facile</option>
                                    <option value="Moyen" <?= $critere->getDifficulte() === 'Moyen' ? 'selected' : '' ?>>Moyen</option>
                                    <option value="Difficile" <?= $critere->getDifficulte() === 'Difficile' ? 'selected' : '' ?>>Difficile</option>
                                </select>
                            </div>
                            <div class="form-group" style="flex:1; display:flex; align-items:center; gap: 0.5rem; margin-top: 1.5rem;">
                                <input type="checkbox" id="est_obligatoire" name="est_obligatoire" <?= $critere->getEstObligatoire() ? 'checked' : '' ?> style="transform: scale(1.5);">
                                <label for="est_obligatoire" style="margin:0; font-size:1rem;">Critère Bloquant (Obligatoire)</label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="document_template">Lien Modèle / Template</label>
                            <input type="text" id="document_template" name="document_template" value="<?= htmlspecialchars($critere->getDocumentTemplate() ?? '') ?>">
                        </div>
                        
                        <div class="form-actions">
                            <a href="back-certification.php?tab=criteres" class="btn-cancel">Annuler</a>
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Mettre à jour</button>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    </div>

    <script>
        const formUpdateCritere = document.getElementById('formUpdateCritere');
        formUpdateCritere.addEventListener('submit', function(e) {
            let isValid = true;
            
            const nomInput    = document.getElementById('nom');
            const preuveInput = document.getElementById('moyen_preuve');

            const errorNom    = document.getElementById('error-nom');
            const errorPreuve = document.getElementById('error-preuve');
            
            if (nomInput.value.trim().length < 3) {
                errorNom.style.display = 'block'; nomInput.style.borderColor = 'var(--danger)'; isValid = false;
            } else {
                errorNom.style.display = 'none'; nomInput.style.borderColor = 'var(--gray-light)';
            }
            
            if (preuveInput.value.trim().length < 10) {
                errorPreuve.style.display = 'block'; preuveInput.style.borderColor = 'var(--danger)'; isValid = false;
            } else {
                errorPreuve.style.display = 'none'; preuveInput.style.borderColor = 'var(--gray-light)';
            }
            
            if (!isValid) {
                e.preventDefault();
                Swal.fire({ icon: 'error', title: 'Erreur', text: 'Veuillez corriger les erreurs dans le formulaire.', backdrop: `rgba(15, 23, 42, 0.6)` });
            }
        });
    </script>
</body>
</html>
