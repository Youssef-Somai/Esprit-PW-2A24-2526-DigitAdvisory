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
foreach ($certificats as $cert) {
    $cert->detailed_criteres = $controller->getDetailedCriteresByCertificat($cert->getId());
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
        
        /* ─── CARD STYLING ─── */
        .card { background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-sm); margin-bottom: 1.5rem; overflow: hidden; transition: box-shadow 0.3s ease; }
        .card:hover { box-shadow: var(--shadow-md); }
        .badge { padding: 0.3rem 0.75rem; border-radius: var(--radius-full); font-size: 0.8rem; font-weight: 600; display: inline-block;}
        .badge.success { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .badge.primary { background: rgba(37, 99, 235, 0.1); color: var(--primary); }
        .badge.info { background: rgba(14, 165, 233, 0.1); color: #0ea5e9; }
        
        /* ─── ACCORDION & EVALUATION STYLING ─── */
        .btn-eval {
            width: 100%; border: border: 1px solid var(--gray-light); background: #f8fafc; color: var(--dark);
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
        .eval-container.expanded { max-height: 1000px; padding: 1.5rem; border-top: 1px solid var(--gray-light); }

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
                <h2 style="margin: 0; font-size: 1.5rem;">Catalogue & Auto-Évaluation</h2>
                <span class="badge primary" style="font-size: 0.9rem;"><i class="fa-solid fa-rocket"></i> <?= count($certificats) ?> certification(s)</span>
            </div>

            <div style="background: white; padding: 1.5rem; border-radius: var(--radius-lg); margin-bottom: 2rem; box-shadow: var(--shadow-sm); border-left: 4px solid var(--accent);">
                <h3 style="margin-bottom: 0.5rem; color: var(--dark); font-size: 1.1rem;"><i class="fa-solid fa-lightbulb text-accent"></i> Nouvel outil d'évaluation interactif !</h3>
                <p style="color: var(--gray); font-size: 0.9rem; line-height: 1.5;">
                    Sélectionnez une certification ci-dessous pour découvrir ses <strong>critères d'éligibilité</strong>. Vous pouvez cocher les critères que votre entreprise maîtrise déjà afin de calculer en temps réel votre score de préparation à l'audit ISO.
                </p>
            </div>

            <section class="fade-in-up">
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
                        foreach($criteres as $c) { $totalPoints += $c->poids_specifique; }
                    ?>
                    <div class="card interactive-card" style="border-left: 4px solid <?= $style['border'] ?>;">
                        
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
                                        <input type="checkbox" onchange="calculateScore(<?= $cert->getId() ?>)" value="<?= $critere->poids_specifique ?>" class="chk-<?= $cert->getId() ?>">
                                        
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
                                            +<?= $critere->poids_specifique ?> pts
                                        </div>
                                    </label>
                                    <?php endforeach; ?>
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
            </section>
        </main>
    </div>

    <!-- Script de logique d'évaluation dynamique -->
    <script>
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
            }
        }
    </script>
</body>
</html>
