<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alertes IA — Suivi Intelligent des Missions</title>
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

        /* PAGE */
        .page-container { max-width: 1000px; margin: 2rem auto; padding: 0 1.5rem; }

        /* HERO */
        .page-hero {
            background: linear-gradient(135deg, #1a3c5e 0%, #7c3aed 100%);
            border-radius: 16px;
            padding: 2rem;
            color: white;
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
        }
        .page-hero::before {
            content: '';
            position: absolute;
            top: -60px; right: -60px;
            width: 220px; height: 220px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
        }
        .page-hero h1 { font-size: 1.6rem; font-weight: 700; margin-bottom: .5rem; }
        .page-hero p { opacity: .8; font-size: .95rem; margin: 0; }
        .hero-badge {
            display: inline-flex; align-items: center; gap: .4rem;
            background: rgba(255,255,255,0.15);
            padding: .3rem .9rem;
            border-radius: 20px;
            font-size: .8rem;
            font-weight: 600;
            margin-bottom: .8rem;
        }

        /* ANALYSE GLOBALE BUTTON */
        .btn-analyse-all {
            background: linear-gradient(135deg, #7c3aed, #2563eb);
            color: white;
            border: none;
            padding: .85rem 2rem;
            border-radius: 12px;
            font-size: .95rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: .6rem;
            transition: transform .2s, box-shadow .2s;
            margin-bottom: 1.5rem;
        }
        .btn-analyse-all:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(124,58,237,0.4); }
        .btn-analyse-all:disabled { opacity: .6; cursor: not-allowed; transform: none; }

        /* MISSION CARDS GRID */
        .missions-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }

        .mission-card {
            background: white;
            border-radius: 14px;
            padding: 1.25rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            border-left: 4px solid #e2e8f0;
            transition: box-shadow .2s, transform .2s;
            position: relative;
        }
        .mission-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.1); transform: translateY(-2px); }
        .mission-card.risk-high   { border-left-color: #ef4444; }
        .mission-card.risk-medium { border-left-color: #f59e0b; }
        .mission-card.risk-low    { border-left-color: #10b981; }
        .mission-card.risk-none   { border-left-color: #94a3b8; }

        .card-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: .8rem; }
        .mission-title { font-weight: 700; font-size: .95rem; color: #1a3c5e; line-height: 1.3; flex: 1; margin-right: .5rem; }
        .statut-pill {
            padding: .2rem .7rem;
            border-radius: 20px;
            font-size: .72rem;
            font-weight: 600;
            white-space: nowrap;
        }
        .pill-en-cours  { background: #dbeafe; color: #2563eb; }
        .pill-terminee  { background: #dcfce7; color: #16a34a; }
        .pill-suspendue { background: #fef3c7; color: #d97706; }

        .card-meta { display: flex; gap: 1rem; margin-bottom: .8rem; }
        .meta-item { font-size: .8rem; color: #64748b; display: flex; align-items: center; gap: .3rem; }

        /* RISK INDICATOR */
        .risk-indicator {
            display: flex; align-items: center; gap: .5rem;
            padding: .4rem .8rem;
            border-radius: 8px;
            font-size: .8rem;
            font-weight: 600;
            margin-bottom: .8rem;
        }
        .risk-high-bg   { background: #fee2e2; color: #dc2626; }
        .risk-medium-bg { background: #fef3c7; color: #d97706; }
        .risk-low-bg    { background: #dcfce7; color: #16a34a; }
        .risk-none-bg   { background: #f1f5f9; color: #64748b; }

        /* PER-CARD ANALYSE BUTTON */
        .btn-card-analyse {
            background: #f0f4f8;
            color: #1a3c5e;
            border: none;
            padding: .5rem 1rem;
            border-radius: 8px;
            font-size: .8rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: .4rem;
            width: 100%;
            justify-content: center;
            transition: background .2s;
        }
        .btn-card-analyse:hover:not(:disabled) { background: #e2e8f0; }
        .btn-card-analyse:disabled { opacity: .5; cursor: not-allowed; }

        /* AI RESULT PER CARD */
        .card-ai-result {
            margin-top: .8rem;
            border-radius: 10px;
            padding: .8rem 1rem;
            background: #f8fafc;
            font-size: .82rem;
            color: #334155;
            line-height: 1.6;
            display: none;
            border: 1px solid #e2e8f0;
        }
        .card-ai-result.visible { display: block; }
        .card-ai-result .ai-label {
            font-size: .72rem;
            font-weight: 700;
            color: #7c3aed;
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: .4rem;
            display: flex;
            align-items: center;
            gap: .3rem;
        }

        /* LOADING MINI */
        .mini-spinner {
            width: 14px; height: 14px;
            border: 2px solid #e2e8f0;
            border-top-color: #7c3aed;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            display: inline-block;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* GLOBAL ANALYSE RESULT */
        .global-result-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            margin-bottom: 1.5rem;
            display: none;
        }
        .global-result-card.visible { display: block; }
        .global-result-header {
            display: flex; align-items: center; gap: .8rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f4f8;
        }
        .ai-icon-purple {
            width: 44px; height: 44px;
            background: linear-gradient(135deg, #7c3aed, #2563eb);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 1.1rem;
        }
        .global-result-header h3 { font-size: 1.1rem; font-weight: 700; color: #1a3c5e; margin: 0; }
        .global-result-header span { font-size: .8rem; color: #94a3b8; }
        #global-analyse-content { line-height: 1.8; color: #334155; font-size: .95rem; white-space: pre-wrap; }

        /* STATS ROW */
        .stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
        .stat-box {
            background: white;
            border-radius: 12px;
            padding: 1rem;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .stat-box .stat-num { font-size: 1.8rem; font-weight: 800; }
        .stat-box .stat-lbl { font-size: .78rem; color: #64748b; margin-top: .2rem; }
        .color-red    { color: #ef4444; }
        .color-green  { color: #10b981; }
        .color-yellow { color: #f59e0b; }
        .color-blue   { color: #2563eb; }

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

    <!-- HERO -->
    <div class="page-hero">
        <div class="hero-badge">
            <i class="fas fa-robot"></i> Intelligence Artificielle
        </div>
        <h1><i class="fas fa-triangle-exclamation me-2"></i> Suivi Intelligent des Missions</h1>
        <p>L'IA analyse chaque mission, détecte les retards, et suggère des actions correctives en temps réel.</p>
    </div>

    <?php
    // Calcul du risque de retard pour chaque mission (logique PHP)
    $today = new DateTime();
    $missionsAvecRisque = [];

    foreach ($missions as $m) {
        $debut     = new DateTime($m['date_debut']);
        $joursPassés = (int)$today->diff($debut)->days;
        $livs      = array_filter($livrables, fn($l) => $l['mission_id'] == $m['id']);
        $nbLivs    = count($livs);
        $nbValides = count(array_filter($livs, fn($l) => $l['etat'] === 'Validé'));
        $nbRejetes = count(array_filter($livs, fn($l) => $l['etat'] === 'Rejeté'));
        $nbAttente = count(array_filter($livs, fn($l) => $l['etat'] === 'En attente'));

        // Calcul niveau de risque
        $risque = 'none';
        if ($m['statut'] === 'Terminée') {
            $risque = 'none';
        } elseif ($joursPassés > 180 && $nbValides === 0) {
            $risque = 'high';
        } elseif ($joursPassés > 90 && $nbValides < $nbLivs / 2) {
            $risque = 'high';
        } elseif ($nbRejetes > 0 || ($joursPassés > 60 && $nbAttente === $nbLivs && $nbLivs > 0)) {
            $risque = 'medium';
        } elseif ($nbValides === $nbLivs && $nbLivs > 0) {
            $risque = 'low';
        } else {
            $risque = 'medium';
        }

        $missionsAvecRisque[] = array_merge($m, [
            'jours_passes'  => $joursPassés,
            'nb_livs'       => $nbLivs,
            'nb_valides'    => $nbValides,
            'nb_rejetes'    => $nbRejetes,
            'nb_attente'    => $nbAttente,
            'risque'        => $risque,
            'livs_detail'   => array_values($livs),
        ]);
    }

    $nbHigh   = count(array_filter($missionsAvecRisque, fn($m) => $m['risque'] === 'high'));
    $nbMedium = count(array_filter($missionsAvecRisque, fn($m) => $m['risque'] === 'medium'));
    $nbLow    = count(array_filter($missionsAvecRisque, fn($m) => $m['risque'] === 'low'));
    $nbNone   = count(array_filter($missionsAvecRisque, fn($m) => $m['risque'] === 'none'));
    ?>

    <!-- STATS ROW -->
    <div class="stats-row">
        <div class="stat-box">
            <div class="stat-num color-red"><?= $nbHigh ?></div>
            <div class="stat-lbl">⚠️ Risque élevé</div>
        </div>
        <div class="stat-box">
            <div class="stat-num color-yellow"><?= $nbMedium ?></div>
            <div class="stat-lbl">⚡ Risque moyen</div>
        </div>
        <div class="stat-box">
            <div class="stat-num color-green"><?= $nbLow ?></div>
            <div class="stat-lbl">✅ Bon état</div>
        </div>
        <div class="stat-box">
            <div class="stat-num color-blue"><?= count($missions) ?></div>
            <div class="stat-lbl">📋 Total missions</div>
        </div>
    </div>

    <!-- ANALYSE GLOBALE BUTTON -->
    <button class="btn-analyse-all" id="btnAnalyseAll" onclick="analyseGlobale()">
        <i class="fas fa-robot"></i>
        Analyse Globale IA — Toutes les missions
        <i class="fas fa-wand-magic-sparkles"></i>
    </button>

    <!-- GLOBAL RESULT -->
    <div class="global-result-card" id="globalResultCard">
        <div class="global-result-header">
            <div class="ai-icon-purple"><i class="fas fa-robot"></i></div>
            <div>
                <h3>Analyse Globale du Portefeuille</h3>
                <span id="globalDate"></span>
            </div>
        </div>
        <div id="global-analyse-content"></div>
        <div class="action-buttons">
            <button class="btn-action btn-outline-action" onclick="reanalyser()">
                <i class="fas fa-rotate"></i> Réanalyser
            </button>
            <a href="index.php?action=back_list" class="btn-action btn-outline-action">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <!-- MISSIONS GRID -->
    <div class="missions-grid">
        <?php foreach ($missionsAvecRisque as $m):
            $risqueLbl = match($m['risque']) {
                'high'   => '⚠️ Risque élevé de retard',
                'medium' => '⚡ Risque modéré',
                'low'    => '✅ Mission en bonne voie',
                'none'   => '🏁 Mission terminée',
                default  => ''
            };
            $risqueBg = match($m['risque']) {
                'high'   => 'risk-high-bg',
                'medium' => 'risk-medium-bg',
                'low'    => 'risk-low-bg',
                'none'   => 'risk-none-bg',
                default  => 'risk-none-bg'
            };
            $pillClass = match($m['statut']) {
                'En cours'  => 'pill-en-cours',
                'Terminée'  => 'pill-terminee',
                'Suspendue' => 'pill-suspendue',
                default     => 'pill-en-cours'
            };
        ?>
        <div class="mission-card risk-<?= $m['risque'] ?>" id="card-<?= $m['id'] ?>">
            <div class="card-top">
                <div class="mission-title"><?= htmlspecialchars($m['titre']) ?></div>
                <span class="statut-pill <?= $pillClass ?>"><?= htmlspecialchars($m['statut']) ?></span>
            </div>
            <div class="card-meta">
                <div class="meta-item"><i class="fas fa-calendar"></i> <?= $m['jours_passes'] ?> jours</div>
                <div class="meta-item"><i class="fas fa-paperclip"></i> <?= $m['nb_livs'] ?> livrable(s)</div>
                <div class="meta-item"><i class="fas fa-check" style="color:#16a34a"></i> <?= $m['nb_valides'] ?></div>
                <?php if ($m['nb_rejetes'] > 0): ?>
                <div class="meta-item"><i class="fas fa-times" style="color:#dc2626"></i> <?= $m['nb_rejetes'] ?></div>
                <?php endif; ?>
            </div>
            <div class="risk-indicator <?= $risqueBg ?>">
                <?= $risqueLbl ?>
            </div>
            <button class="btn-card-analyse" id="btn-<?= $m['id'] ?>"
                onclick="analyserMission(<?= htmlspecialchars(json_encode($m)) ?>)">
                <i class="fas fa-robot"></i> Analyser avec l'IA
            </button>
            <div class="card-ai-result" id="result-<?= $m['id'] ?>">
                <div class="ai-label"><i class="fas fa-robot"></i> Analyse IA</div>
                <div id="result-text-<?= $m['id'] ?>"></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</div>

<!-- Passe les données PHP en JS -->
<script>
const allMissions = <?= json_encode($missionsAvecRisque) ?>;

// ──────────────────────────────────────
// Analyse d'UNE mission (bouton par carte)
// ──────────────────────────────────────
async function analyserMission(mission) {
    const btn    = document.getElementById('btn-' + mission.id);
    const result = document.getElementById('result-' + mission.id);
    const text   = document.getElementById('result-text-' + mission.id);

    btn.disabled = true;
    btn.innerHTML = '<span class="mini-spinner"></span> Analyse en cours...';
    result.classList.remove('visible');

    const livsText = mission.livs_detail.length === 0
        ? "Aucun livrable"
        : mission.livs_detail.map(l => `${l.nom_fichier} (${l.date_remise} — ${l.etat})`).join(', ');

    const prompt = `Tu es un expert en gestion de projet. Analyse cette mission en français et sois très concis (max 80 mots).

Mission : "${mission.titre}"
Statut : ${mission.statut}
Jours depuis début : ${mission.jours_passes} jours
Livrables : ${mission.nb_livs} total / ${mission.nb_valides} validés / ${mission.nb_rejetes} rejetés / ${mission.nb_attente} en attente
Détail : ${livsText}
Niveau de risque calculé : ${mission.risque}

Donne en 3 lignes max :
1. 🔍 Diagnostic (1 phrase)
2. 📌 Statut recommandé (choix : En cours / Suspendue / Terminée)
3. ⚡ Action urgente (1 phrase)`;

    try {
        const response = await fetch("https://api.anthropic.com/v1/messages", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                model: "claude-sonnet-4-20250514",
                max_tokens: 200,
                messages: [{ role: "user", content: prompt }]
            })
        });

        const data = await response.json();
        if (!response.ok || data.error) throw new Error(data.error?.message || "Erreur API");

        text.innerText = data.content?.[0]?.text || "Résultat non disponible.";
        result.classList.add('visible');
        btn.innerHTML = '<i class="fas fa-check" style="color:#16a34a"></i> Analysé';

    } catch (err) {
        text.innerText = "Erreur : " + err.message;
        result.classList.add('visible');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-robot"></i> Réessayer';
    }
}

// ──────────────────────────────────────
// Analyse GLOBALE de toutes les missions
// ──────────────────────────────────────
async function analyseGlobale() {
    const btn    = document.getElementById('btnAnalyseAll');
    const card   = document.getElementById('globalResultCard');
    const content = document.getElementById('global-analyse-content');

    btn.disabled = true;
    btn.innerHTML = '<span class="mini-spinner"></span> Analyse globale en cours...';
    card.classList.remove('visible');

    const resume = allMissions.map(m =>
        `- "${m.titre}" | Statut: ${m.statut} | ${m.jours_passes} jours | Livrables: ${m.nb_valides} validés / ${m.nb_rejetes} rejetés / ${m.nb_attente} en attente | Risque: ${m.risque}`
    ).join('\n');

    const nbHigh   = allMissions.filter(m => m.risque === 'high').length;
    const nbMedium = allMissions.filter(m => m.risque === 'medium').length;
    const nbLow    = allMissions.filter(m => m.risque === 'low').length;

    const prompt = `Tu es un directeur de projet senior. Analyse ce portefeuille de ${allMissions.length} missions en français et génère un rapport de suivi structuré (max 300 mots).

RÉSUMÉ DU PORTEFEUILLE :
- Missions à risque élevé : ${nbHigh}
- Missions à risque modéré : ${nbMedium}  
- Missions en bonne voie : ${nbLow}

DÉTAIL DE CHAQUE MISSION :
${resume}

Génère le rapport avec ces sections exactes :
🏢 ÉTAT GÉNÉRAL DU PORTEFEUILLE
(2-3 phrases sur la santé globale)

🚨 MISSIONS PRIORITAIRES
(liste les missions à risque élevé avec action recommandée)

📈 TENDANCES OBSERVÉES
(patterns détectés dans les données)

✅ RECOMMANDATIONS STRATÉGIQUES
(3 actions concrètes pour améliorer le suivi)`;

    try {
        const response = await fetch("https://api.anthropic.com/v1/messages", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                model: "claude-sonnet-4-20250514",
                max_tokens: 600,
                messages: [{ role: "user", content: prompt }]
            })
        });

        const data = await response.json();
        if (!response.ok || data.error) throw new Error(data.error?.message || "Erreur API");

        content.innerText = data.content?.[0]?.text || "Résultat non disponible.";
        card.classList.add('visible');
        document.getElementById('globalDate').innerText =
            'Analysé le ' + new Date().toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });

        btn.innerHTML = '<i class="fas fa-check"></i> Analyse globale terminée';
        btn.style.background = 'linear-gradient(135deg, #16a34a, #059669)';

    } catch (err) {
        content.innerText = "Erreur : " + err.message;
        card.classList.add('visible');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-robot"></i> Réessayer l\'analyse globale';
        btn.style.background = '';
    }
}

function reanalyser() {
    const btn = document.getElementById('btnAnalyseAll');
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-robot"></i> Analyse Globale IA — Toutes les missions <i class="fas fa-wand-magic-sparkles"></i>';
    btn.style.background = '';
    document.getElementById('globalResultCard').classList.remove('visible');
    analyseGlobale();
}
</script>
</body>
</html>
