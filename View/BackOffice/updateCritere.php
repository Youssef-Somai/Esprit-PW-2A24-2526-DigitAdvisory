<?php
require_once __DIR__ . '/../../Controller/CritereController.php';

$critereController = new CritereController();

$c_id = isset($_GET['id']) ? (int) $_GET['id'] : null;



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
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <script src="https://unpkg.com/html-docx-js/dist/html-docx.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
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
                <h2 style="margin: 0; font-size: 1.5rem;">Modification du Critère #<?= htmlspecialchars($critere->getId()) ?></h2>
                <a href="back-certification.php?tab=criteres" class="btn btn-outline" style="font-size: 0.9rem;"><i class="fa-solid fa-arrow-left"></i> Retour</a>
            </div>

            <section class="fade-in-up" style="max-width: 800px; margin: 0 auto;">
                <div class="card admin-card">
                    <div class="update-header">
                        <i class="fa-solid fa-pen-to-square"></i>
                        <h3 style="margin: 0; font-size: 1.25rem;">Formulaire d'Édition</h3>
                    </div>

                    <form id="formUpdateCritere" method="POST" action="../../Controller/CritereController.php" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update_critere">
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
                            <label for="document_template">Lien Modèle (URL) ou Nouveau Fichier</label>
                            <input type="text" id="document_template" name="document_template" value="<?= htmlspecialchars($critere->getDocumentTemplate() ?? '') ?>" placeholder="ex: https://...">
                            <input type="file" name="template_file" style="margin-top: 0.5rem; background: transparent; border: none; padding: 0;">
                            <?php if ($critere->getDocumentTemplate()): ?>
                                <small style="display:block; margin-top:0.5rem;">Fichier actuel : <a href="<?= htmlspecialchars($critere->getDocumentTemplate()) ?>" target="_blank"><?= basename($critere->getDocumentTemplate()) ?></a></small>
                            <?php endif; ?>
                            <small style="display:block; margin-top:0.5rem; color: var(--gray);">Vous pouvez aussi générer un modèle HTML via OpenAI après enregistrement.</small>
                        </div>
                        
                        <div class="form-actions">
                            <a href="back-certification.php?tab=criteres" class="btn-cancel">Annuler</a>
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Mettre à jour</button>
                        </div>
                    </form>
                    <div style="margin-top: 1rem; display:flex; justify-content:flex-end;">
                        <button type="button" class="btn btn-outline" onclick="generateTemplateAjax()"><i class="fa-solid fa-wand-magic-sparkles"></i> Générer le modèle avec l'IA</button>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- ─── Modal WYSIWYG ─── -->
    <div class="modal-overlay" id="modalWysiwyg">
        <div class="modal" style="max-width: 800px; max-height: 90vh; overflow-y: auto;">
            <button class="modal-close" onclick="closeModalWysiwyg()"><i class="fa-solid fa-xmark"></i></button>
            <h3><i class="fa-solid fa-file-signature"></i> Édition du Modèle IA</h3>
            <div id="editor-container" style="margin-bottom: 1.5rem;">
                <textarea id="wysiwyg-editor"></textarea>
            </div>
            <div class="form-actions" style="justify-content: space-between;">
                <button type="button" class="btn btn-outline" onclick="downloadDocx()" style="border-color:#2b579a; color:#2b579a;"><i class="fa-solid fa-file-word"></i> Télécharger en DOCX</button>
                <div style="display:flex; gap:0.75rem;">
                    <button type="button" class="btn btn-cancel" onclick="closeModalWysiwyg()">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="saveTemplateAjax()"><i class="fa-solid fa-save"></i> Sauvegarder sur le serveur</button>
                </div>
            </div>
        </div>
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

        // ─── WYSIWYG & AJAX GENERATION ───
        let editorInstance;
        let currentCritereId = <?= htmlspecialchars($critere->getId()) ?>;

        ClassicEditor
            .create(document.querySelector('#wysiwyg-editor'))
            .then(editor => { editorInstance = editor; })
            .catch(error => { console.error(error); });

        function generateTemplateAjax() {
            Swal.fire({
                title: 'Génération en cours...',
                html: 'L\'IA rédige le modèle ISO, veuillez patienter (environ 15-30s).',
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
                Swal.fire('Erreur', 'Erreur réseau lors de la génération.', 'error');
            });
        }

        function closeModalWysiwyg() {
            document.getElementById('modalWysiwyg').classList.remove('active');
        }

        function downloadDocx() {
            const htmlContent = editorInstance.getData();
            const fullHtml = `<!DOCTYPE html><html><head><meta charset="utf-8"><title>Modele ISO</title></head><body>${htmlContent}</body></html>`;
            const converted = htmlDocx.asBlob(fullHtml);
            saveAs(converted, 'Modele_ISO_Critere_' + currentCritereId + '.docx');
        }

        function saveTemplateAjax() {
            Swal.fire({
                title: 'Sauvegarde...',
                html: 'Enregistrement du document DOCX sur le serveur.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const htmlContent = editorInstance.getData();
            const fullHtml = `<!DOCTYPE html><html><head><meta charset="utf-8"><title>Modele ISO</title></head><body>${htmlContent}</body></html>`;
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
                    Swal.fire('Succès', data.message, 'success').then(() => {
                        closeModalWysiwyg();
                        window.location.reload(); // Pour rafraichir le lien du fichier actuel
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
