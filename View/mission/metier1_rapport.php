<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rapport IA — Mission #<?= $mission['id'] ?></title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DM Sans', sans-serif; background: #f0f4f8; min-height: 100vh; }

        /* NAVBAR */
        .navbar { background: #1a3c5e; padding: 1rem 2rem; display: flex; align-items: center; justify-content: space-between; }
        .navbar-brand { color: #fff; font-weight: 700; font-size: 1.2rem; text-decoration: none; }
        .navbar-back { color: rgba(255,255,255,0.7); font-size: .9rem; text-decoration: none; display: flex; align-items: center; gap: .4rem; transition: color .2s; }
        .navbar-back:hover { color: #fff; }

        /* PAGE LAYOUT */
        .page-container { max-width: 900px; margin: 2rem auto; padding: 0 1.5rem; }

        /* MISSION HEADER CARD */
        .mission-header {
            background: linear-gradient(135deg, #1a3c5e 0%, #2563eb 100%);
            border-radius: 16px;
            padding: 2rem;
            color: white;
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
        }
        .mission-header::after {
            content: '';
            position: absolute;
            top: -50px; right: -50px;
            width: 200px; height: 200px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
        }
        .mission-header .badge-statut {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: .3rem .8rem;
            border-radius: 20px;
            font-size: .8rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: .8rem;
        }
        .mission-header h1 { font-size: 1.6rem; font-weight: 700; margin-bottom: .5rem; }
        .mission-header .meta { opacity: .8; font-size: .9rem; display: flex; gap: 1.5rem; flex-wrap: wrap; }

        /* AI BUTTON */
        .btn-generate {
            background: linear-gradient(135deg, #f59e0b, #ef4444);
            color: white;
            border: none;
            padding: .9rem 2rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: .6rem;
            transition: transform .2s, box-shadow .2s;
            width: 100%;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        .btn-generate:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(245,158,11,0.4); }
        .btn-generate:disabled { opacity: .6; cursor: not-allowed; transform: none; }

        /* LIVRABLES SECTION */
        .section-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        .section-title { font-size: 1rem; font-weight: 700; color: #1a3c5e; margin-bottom: 1rem; display: flex; align-items: center; gap: .5rem; }
        .livrable-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .7rem 1rem;
            border-radius: 10px;
            background: #f8fafc;
            margin-bottom: .5rem;
        }
        .livrable-item:last-child { margin-bottom: 0; }
        .livrable-name { font-weight: 600; font-size: .9rem; color: #334155; display: flex; align-items: center; gap: .5rem; }
        .etat-badge {
            padding: .2rem .7rem;
            border-radius: 20px;
            font-size: .75rem;
            font-weight: 600;
        }
        .etat-valide   { background: #dcfce7; color: #16a34a; }
        .etat-attente  { background: #fef3c7; color: #d97706; }
        .etat-rejete   { background: #fee2e2; color: #dc2626; }

        /* RAPPORT OUTPUT */
        .rapport-container {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            display: none;
        }
        .rapport-container.visible { display: block; }
        .rapport-header {
            display: flex;
            align-items: center;
            gap: .8rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f4f8;
        }
        .rapport-header .ai-icon {
            width: 44px; height: 44px;
            background: linear-gradient(135deg, #f59e0b, #ef4444);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 1.1rem;
        }
        .rapport-header h3 { font-size: 1.1rem; font-weight: 700; color: #1a3c5e; margin: 0; }
        .rapport-header span { font-size: .8rem; color: #94a3b8; }

        #rapport-content {
            line-height: 1.8;
            color: #334155;
            font-size: .95rem;
            white-space: pre-wrap;
        }
        #rapport-content .rapport-section { margin-bottom: 1.2rem; }
        #rapport-content strong { color: #1a3c5e; }

        /* LOADING SPINNER */
        .loading-box {
            text-align: center;
            padding: 2.5rem 1rem;
            display: none;
        }
        .loading-box.visible { display: block; }
        .spinner {
            width: 48px; height: 48px;
            border: 4px solid #e2e8f0;
            border-top-color: #2563eb;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .loading-text { color: #64748b; font-size: .95rem; }
        .loading-dots::after {
            content: '...';
            animation: dots 1.5s steps(4, end) infinite;
        }
        @keyframes dots {
            0%,20% { content: '.'; }
            40%     { content: '..'; }
            60%,100%{ content: '...'; }
        }

        /* ERROR */
        .error-box {
            background: #fee2e2;
            border-radius: 12px;
            padding: 1rem 1.5rem;
            color: #dc2626;
            font-size: .9rem;
            display: none;
        }
        .error-box.visible { display: flex; align-items: center; gap: .6rem; }

        /* ACTION BUTTONS */
        .action-buttons { display: flex; gap: .8rem; margin-top: 1.5rem; flex-wrap: wrap; }
        .btn-action {
            padding: .6rem 1.2rem;
            border-radius: 10px;
            font-size: .875rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: .4rem;
            text-decoration: none;
            transition: opacity .2s;
        }
        .btn-action:hover { opacity: .85; }
        .btn-primary-action { background: #1a3c5e; color: white; }
        .btn-outline-action { background: #f0f4f8; color: #1a3c5e; }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <a href="index.php" class="navbar-brand">🎓 Eduleb Consulting</a>
    <a href="index.php?action=back_list" class="navbar-back">
        <i class="fas fa-arrow-left"></i> Retour à l'administration
    </a>
</nav>

<div class="page-container">

    <!-- MISSION HEADER -->
    <div class="mission-header">
        <div class="badge-statut">
            <i class="fas fa-briefcase me-1"></i> Mission #<?= $mission['id'] ?>
        </div>
        <h1><?= htmlspecialchars($mission['titre']) ?></h1>
        <div class="meta">
            <span><i class="fas fa-calendar-alt me-1"></i> Début : <?= date('d/m/Y', strtotime($mission['date_debut'])) ?></span>
            <span><i class="fas fa-circle-dot me-1"></i> Statut : <?= htmlspecialchars($mission['statut']) ?></span>
            <span><i class="fas fa-paperclip me-1"></i> <?= count($livrables) ?> livrable(s)</span>
        </div>
    </div>

    <!-- LIVRABLES RÉSUMÉ -->
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-folder-open" style="color:#2563eb"></i>
            Livrables associés
        </div>
        <?php if (empty($livrables)): ?>
            <p style="color:#94a3b8;font-size:.9rem;text-align:center;padding:1rem 0">
                Aucun livrable pour cette mission.
            </p>
        <?php else: ?>
            <?php foreach ($livrables as $l):
                $etatClass = match($l['etat']) {
                    'Validé'     => 'etat-valide',
                    'Rejeté'     => 'etat-rejete',
                    'En attente' => 'etat-attente',
                    default      => 'etat-attente'
                };
                $fileIcon = str_ends_with($l['nom_fichier'], '.pdf') ? 'fa-file-pdf' : (str_ends_with($l['nom_fichier'], '.docx') ? 'fa-file-word' : 'fa-file');
            ?>
            <div class="livrable-item">
                <div class="livrable-name">
                    <i class="fas <?= $fileIcon ?>" style="color:#2563eb"></i>
                    <?= htmlspecialchars($l['nom_fichier']) ?>
                    <span style="color:#94a3b8;font-size:.8rem;font-weight:400">
                        — <?= date('d/m/Y', strtotime($l['date_remise'])) ?>
                    </span>
                </div>
                <span class="etat-badge <?= $etatClass ?>"><?= htmlspecialchars($l['etat']) ?></span>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- GENERATE BUTTON -->
    <button class="btn-generate" id="btnGenerate" onclick="genererRapport()">
        <i class="fas fa-robot"></i>
        Générer le Rapport Intelligent avec l'IA
        <i class="fas fa-sparkles"></i>
    </button>

    <!-- LOADING -->
    <div class="loading-box" id="loadingBox">
        <div class="spinner"></div>
        <div class="loading-text">L'IA analyse la mission<span class="loading-dots"></span></div>
        <div style="color:#94a3b8;font-size:.8rem;margin-top:.5rem">Cela peut prendre quelques secondes</div>
    </div>

    <!-- ERROR -->
    <div class="error-box" id="errorBox">
        <i class="fas fa-exclamation-triangle"></i>
        <span id="errorMsg"></span>
    </div>

    <!-- RAPPORT OUTPUT -->
    <div class="rapport-container" id="rapportContainer">
        <div class="rapport-header">
            <div class="ai-icon"><i class="fas fa-robot"></i></div>
            <div>
                <h3>Rapport Intelligent Généré</h3>
                <span id="rapportDate"></span>
            </div>
        </div>
        <div id="rapport-content"></div>
        <div class="action-buttons">
            <button class="btn-action btn-primary-action" onclick="imprimerRapport()">
                <i class="fas fa-print"></i> Imprimer
            </button>
            <button class="btn-action btn-outline-action" onclick="regenerer()">
                <i class="fas fa-rotate"></i> Régénérer
            </button>
            <a href="index.php?action=back_list" class="btn-action btn-outline-action">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

</div>

<script>
// Données PHP → JS
const missionData = {
    id: <?= $mission['id'] ?>,
    titre: <?= json_encode($mission['titre']) ?>,
    date_debut: <?= json_encode($mission['date_debut']) ?>,
    statut: <?= json_encode($mission['statut']) ?>,
    livrables: <?= json_encode($livrables) ?>
};

async function genererRapport() {
    const btn = document.getElementById('btnGenerate');
    const loading = document.getElementById('loadingBox');
    const error = document.getElementById('errorBox');
    const rapport = document.getElementById('rapportContainer');

    // Reset UI
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Génération en cours...';
    loading.classList.add('visible');
    error.classList.remove('visible');
    rapport.classList.remove('visible');

    // Prépare le résumé des livrables
    const livrablesSummary = missionData.livrables.length === 0
        ? "Aucun livrable associé à cette mission."
        : missionData.livrables.map(l =>
            `- ${l.nom_fichier} (remise: ${l.date_remise}, état: ${l.etat})`
          ).join('\n');

    const valides  = missionData.livrables.filter(l => l.etat === 'Validé').length;
    const rejetes  = missionData.livrables.filter(l => l.etat === 'Rejeté').length;
    const attente  = missionData.livrables.filter(l => l.etat === 'En attente').length;

    const prompt = `Tu es un expert en gestion de projet et consulting. Génère un rapport professionnel complet en français pour la mission suivante.

DONNÉES DE LA MISSION :
- Titre : ${missionData.titre}
- Date de début : ${missionData.date_debut}
- Statut actuel : ${missionData.statut}
- Nombre de livrables : ${missionData.livrables.length}
- Livrables validés : ${valides}
- Livrables rejetés : ${rejetes}
- Livrables en attente : ${attente}

LISTE DES LIVRABLES :
${livrablesSummary}

Génère un rapport structuré avec exactement ces sections :
1. 📋 RÉSUMÉ EXÉCUTIF
2. 📊 ANALYSE DES LIVRABLES
3. ✅ POINTS FORTS
4. ⚠️ POINTS D'AMÉLIORATION
5. 💡 RECOMMANDATIONS

Le rapport doit être professionnel, concis (max 400 mots), basé sur les données réelles fournies. Utilise les emojis des titres de sections.`;

    try {
        const response = await fetch("https://api.anthropic.com/v1/messages", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                model: "claude-sonnet-4-20250514",
                max_tokens: 1000,
                messages: [{ role: "user", content: prompt }]
            })
        });

        const data = await response.json();

        if (!response.ok || data.error) {
            throw new Error(data.error?.message || "Erreur API");
        }

        const texte = data.content?.[0]?.text || "Rapport non disponible.";

        // Affiche le rapport
        loading.classList.remove('visible');
        rapport.classList.add('visible');
        document.getElementById('rapport-content').innerText = texte;
        document.getElementById('rapportDate').innerText =
            'Généré le ' + new Date().toLocaleDateString('fr-FR', { day:'2-digit', month:'long', year:'numeric', hour:'2-digit', minute:'2-digit' });

        btn.innerHTML = '<i class="fas fa-check"></i> Rapport généré !';
        btn.style.background = 'linear-gradient(135deg, #16a34a, #059669)';

    } catch (err) {
        loading.classList.remove('visible');
        error.classList.add('visible');
        document.getElementById('errorMsg').innerText = 'Erreur : ' + err.message;
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-robot"></i> Réessayer la génération IA';
        btn.style.background = '';
    }
}

function regenerer() {
    const btn = document.getElementById('btnGenerate');
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-robot"></i> Générer le Rapport Intelligent avec l\'IA <i class="fas fa-sparkles"></i>';
    btn.style.background = '';
    document.getElementById('rapportContainer').classList.remove('visible');
    genererRapport();
}

function imprimerRapport() {
    window.print();
}
</script>
</body>
</html>
