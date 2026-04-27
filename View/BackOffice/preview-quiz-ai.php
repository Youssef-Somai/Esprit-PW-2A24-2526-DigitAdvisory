<?php
session_start();

// ------------------------------
// 1) Vérification des données POST
// ------------------------------
if (
    !isset($_POST['titre']) ||
    !isset($_POST['theme']) ||
    !isset($_POST['niveau']) ||
    !isset($_POST['nb_questions']) ||
    !isset($_POST['temps']) ||
    !isset($_POST['langue'])
) {
    die("Données invalides.");
}

$titre = trim($_POST['titre']);
$theme = trim($_POST['theme']);
$niveau = trim($_POST['niveau']);
$nb_questions = (int) $_POST['nb_questions'];
$temps = (int) $_POST['temps'];
$langue = trim($_POST['langue']);

if (
    $titre === '' ||
    $theme === '' ||
    $niveau === '' ||
    $nb_questions <= 0 ||
    $temps <= 0 ||
    $langue === ''
) {
    die("Veuillez remplir correctement tous les champs.");
}

// ------------------------------
// 2) Clé API Gemini
// ------------------------------
//$apiKey = 'AIzaSyBA04q0DzF2_gLBFdMPvn4PRvoTRqDwk6k';
$apiKey = 'AIzaSyDgNrznkuLM1u6hGzWf9yFWwt1ueQzsQtQ';
// ------------------------------
// 3) Prompt
// Ton système actuel utilise :
// question, choix1, choix2, choix3, bonne_reponse, point
// ------------------------------
$prompt = "
Génère un quiz en {$langue} sur le thème : {$theme}.

Contraintes :
- Titre du quiz : {$titre}
- Niveau : {$niveau}
- Nombre de questions : {$nb_questions}
- Temps estimé : {$temps} minutes
- Retourne uniquement du JSON valide
- Chaque question doit avoir exactement 4 choix
- Les clés choix1, choix2, choix3 et choix4 doivent toujours exister
- bonne_reponse doit être 1, 2, 3 ou 4
- La description doit être claire, courte et professionnelle
- Les questions doivent être claires et adaptées au niveau {$niveau}
- Ne mets aucun texte hors du JSON

Format JSON exact :
{
  \"titre\": \"...\",
  \"description\": \"...\",
  \"questions\": [
    {
      \"question\": \"...\",
      \"choix1\": \"...\",
      \"choix2\": \"...\",
      \"choix3\": \"...\",
      \"choix4\": \"...\",
      \"bonne_reponse\": 1
    }
  ]
}
";

// ------------------------------
// 4) Appel API Gemini
// ------------------------------
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent";


$payload = [
    "contents" => [
        [
            "parts" => [
                ["text" => $prompt]
            ]
        ]
    ],
    "generationConfig" => [
        "temperature" => 0.7
    ]
];

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "X-goog-api-key: " . $apiKey
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$response = curl_exec($ch);

if (curl_errno($ch)) {
    die("Erreur cURL : " . curl_error($ch));
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// ------------------------------
// 5) Vérification réponse brute
// ------------------------------
if ($response === false || $response === '') {
    die("Réponse vide de l'API.");
}

$responseData = json_decode($response, true);

if ($responseData === null) {
    echo "<pre>";
    echo "Réponse brute non JSON :\n";
    echo htmlspecialchars($response);
    echo "</pre>";
    exit;
}

// ------------------------------
// 6) Si l'API renvoie une erreur
// ------------------------------
if (isset($responseData['error'])) {
    echo "<pre>";
    echo "Erreur API :\n";
    print_r($responseData);
    echo "</pre>";
    exit;
}

if ($httpCode !== 200) {
    echo "<pre>";
    echo "Code HTTP inattendu : " . $httpCode . "\n";
    print_r($responseData);
    echo "</pre>";
    exit;
}

// ------------------------------
// 7) Extraction du texte IA
// ------------------------------
$jsonText = '';

if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
    $jsonText = $responseData['candidates'][0]['content']['parts'][0]['text'];
} else {
    echo "<pre>";
    echo "Structure de réponse inattendue :\n";
    print_r($responseData);
    echo "</pre>";
    exit;
}

// ------------------------------
// 8) Nettoyage si Gemini renvoie ```json ... ```
// ------------------------------
$jsonText = trim($jsonText);
$jsonText = preg_replace('/^```json\s*/i', '', $jsonText);
$jsonText = preg_replace('/^```\s*/', '', $jsonText);
$jsonText = preg_replace('/\s*```$/', '', $jsonText);
$jsonText = trim($jsonText);

// ------------------------------
// 9) Décodage du JSON du quiz
// ------------------------------
$quizData = json_decode($jsonText, true);

if ($quizData === null || !isset($quizData['questions']) || !is_array($quizData['questions'])) {
    echo "<pre>";
    echo "Le JSON généré par l'IA est invalide.\n\n";
    echo "Texte reçu :\n";
    echo htmlspecialchars($jsonText);
    echo "\n\nRéponse API complète :\n";
    print_r($responseData);
    echo "</pre>";
    exit;
}

// ------------------------------
// 10) Sauvegarde en session
// ------------------------------
$_SESSION['quiz_ai_generated'] = $quizData;
?>




<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Prévisualisation Quiz IA</title>

    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            background: #f1f5f9;
            font-family: 'Inter', sans-serif;
        }

        .sidebar { background: var(--dark); color: white; }
        .sidebar .menu-item { color: var(--gray-light); }

        .sidebar .menu-item:hover,
        .sidebar .menu-item.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: var(--accent);
        }

        .sidebar-header {
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header .logo {
            color: white;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            z-index: 100;
            transition: var(--transition);
        }

        .sidebar-header {
            padding: 1.5rem;
            display: flex;
            align-items: center;
        }

        .sidebar-menu {
            padding: 1rem 0;
            flex: 1;
            overflow-y: auto;
        }

        .menu-item {
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            border-left: 3px solid transparent;
            text-decoration: none;
        }

        .menu-item i {
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }

        .user-profile-widget {
            background: rgba(0,0,0,0.2);
            border-top: 1px solid rgba(255,255,255,0.1);
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--accent);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: 600;
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
            background: #f1f5f9;
            min-height: 100vh;
        }

        .top-navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            background: white;
            padding: 1rem 2rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
        }

        .wrapper {
            max-width: 1100px;
            margin: 0 auto;
            animation: fadeUp .7s ease;
        }

        .hero-card,
        .question-box,
        .validation-card {
            background: rgba(255,255,255,.95);
            border-radius: 22px;
            padding: 1.7rem;
            box-shadow: 0 15px 35px rgba(15, 23, 42, .08);
            margin-bottom: 1.2rem;
            border: 1px solid rgba(226,232,240,.9);
        }

        .hero-card {
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .hero-card::after {
            content: "";
            position: absolute;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: rgba(255,255,255,.12);
            right: -70px;
            top: -70px;
        }

        .hero-title {
            display: flex;
            align-items: center;
            gap: .8rem;
            margin-bottom: .7rem;
        }

        .hero-title i {
            background: rgba(255,255,255,.18);
            padding: .8rem;
            border-radius: 16px;
        }

        .ai-badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .5rem .9rem;
            border-radius: 999px;
            background: rgba(255,255,255,.18);
            color: white;
            font-weight: 600;
            margin-right: .5rem;
            margin-top: .5rem;
        }

        .question-box {
            animation: fadeUp .6s ease both;
            transition: .3s ease;
        }

        .question-box:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(37, 99, 235, .12);
        }

        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .question-number {
            background: linear-gradient(135deg, #2563eb, #60a5fa);
            color: white;
            padding: .55rem 1rem;
            border-radius: 999px;
            font-weight: 700;
        }

        .select-line {
            background: #f8fafc;
            padding: .75rem 1rem;
            border-radius: 14px;
            font-weight: 600;
            border: 1px solid #e2e8f0;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            font-weight: 700;
            margin-bottom: .4rem;
            color: #0f172a;
        }

        .form-control {
            width: 100%;
            padding: .85rem 1rem;
            border: 1px solid #dbe3ee;
            border-radius: 14px;
            outline: none;
            transition: .25s ease;
            font-family: inherit;
            background: white;
        }

        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37,99,235,.12);
        }

        textarea.form-control {
            min-height: 90px;
            resize: vertical;
        }

        .choice-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .btn-delete-ai {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: transparent;
            border: 2px solid #ef4444;
            color: #ef4444;
            padding: .65rem 1rem;
            border-radius: 999px;
            cursor: pointer;
            transition: .3s ease;
            font-weight: 700;
        }

        .btn-delete-ai:hover {
            background: #ef4444;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(239,68,68,.25);
        }

        .validation-card {
            border-top: 4px solid #10b981;
        }

        .error-border {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 4px rgba(239,68,68,.12) !important;
        }

        .success-border {
            border-color: #10b981 !important;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(18px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 230px;
            }

            .main-content {
                margin-left: 230px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }

            .dashboard-container {
                flex-direction: column;
            }

            .main-content {
                margin-left: 0;
            }

            .choice-grid {
                grid-template-columns: 1fr;
            }

            .top-navbar {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
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
            <a href="back-utilisateur.php" class="menu-item">
                <i class="fa-solid fa-users"></i> Gestion Utilisateurs
            </a>

            <a href="back-quiz.php" class="menu-item active">
                <i class="fa-solid fa-list-check"></i> Gestion Quiz
            </a>

            <a href="back-portfolio.php" class="menu-item">
                <i class="fa-solid fa-folder-open"></i> Gestion Portfolios
            </a>

            <a href="back-offres.php" class="menu-item">
                <i class="fa-solid fa-briefcase"></i> Gestion Offres
            </a>

            <a href="back-certification.php" class="menu-item">
                <i class="fa-solid fa-award"></i> Gestion Certifications
            </a>

            <a href="back-messagerie.php" class="menu-item">
                <i class="fa-solid fa-comments"></i> Gestion Messagerie
            </a>
        </div>

        <div class="user-profile-widget">
            <div class="user-avatar">AD</div>
            <div>
                <h4 style="font-size: 0.95rem; margin-bottom: 0.2rem; color: white;">Admin Système</h4>
                <span style="font-size: 0.8rem; color: var(--gray-light);">Admin</span>
            </div>
        </div>
    </aside>

    <main class="main-content">

        <div class="top-navbar">
            <h2 style="margin: 0; font-size: 1.5rem;">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Prévisualisation Quiz IA
            </h2>

            <span class="badge warning" style="font-size: 1rem;">
                <i class="fa-solid fa-lock"></i> Espace Sécurisé Admin
            </span>
        </div>

        <div class="wrapper">

            <div class="hero-card">
                <div class="hero-title">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    <div>
                        <h2><?= htmlspecialchars($quizData['titre'] ?? $titre) ?></h2>
                        <p><?= htmlspecialchars($quizData['description'] ?? '') ?></p>
                    </div>
                </div>

                <span class="ai-badge"><i class="fa-solid fa-layer-group"></i> Niveau : <?= htmlspecialchars($niveau) ?></span>
                <span class="ai-badge"><i class="fa-solid fa-clock"></i> Temps : <?= htmlspecialchars((string)$temps) ?> min</span>
                <span class="ai-badge"><i class="fa-solid fa-circle-question"></i> Questions : <?= count($quizData['questions']) ?></span>
            </div>

            <form id="aiQuizPreviewForm" action="save-generated-quiz.php" method="POST">

                <input type="hidden" name="titre" value="<?= htmlspecialchars($quizData['titre'] ?? $titre) ?>">
                <input type="hidden" name="description" value="<?= htmlspecialchars($quizData['description'] ?? '') ?>">

                <?php foreach ($quizData['questions'] as $index => $q) { ?>
                    <div class="question-box" id="questionBox<?= $index ?>">
                        <div class="question-header">
                            <span class="question-number">Question <?= $index + 1 ?></span>

                            <div class="select-line">
                                <label>
                                    <input type="checkbox" name="selected[]" value="<?= $index ?>" checked>
                                    Ajouter cette question
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Texte de la question</label>
                            <textarea name="questions[<?= $index ?>][question]" class="form-control"><?= htmlspecialchars($q['question'] ?? '') ?></textarea>
                        </div>

                        <div class="choice-grid">
                            <div class="form-group">
                                <label>Choix 1</label>
                                <input type="text" name="questions[<?= $index ?>][choix1]" class="form-control" value="<?= htmlspecialchars($q['choix1'] ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label>Choix 2</label>
                                <input type="text" name="questions[<?= $index ?>][choix2]" class="form-control" value="<?= htmlspecialchars($q['choix2'] ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label>Choix 3</label>
                                <input type="text" name="questions[<?= $index ?>][choix3]" class="form-control" value="<?= htmlspecialchars($q['choix3'] ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label>Choix 4</label>
                                <input type="text" name="questions[<?= $index ?>][choix4]" class="form-control" value="<?= htmlspecialchars($q['choix4'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Bonne réponse</label>
                            <select name="questions[<?= $index ?>][bonne_reponse]" class="form-control">
                                <option value="1" <?= ($q['bonne_reponse'] ?? 1) == 1 ? 'selected' : '' ?>>Choix 1</option>
                                <option value="2" <?= ($q['bonne_reponse'] ?? 1) == 2 ? 'selected' : '' ?>>Choix 2</option>
                                <option value="3" <?= ($q['bonne_reponse'] ?? 1) == 3 ? 'selected' : '' ?>>Choix 3</option>
                                <option value="4" <?= ($q['bonne_reponse'] ?? 1) == 4 ? 'selected' : '' ?>>Choix 4</option>
                            </select>
                        </div>

                        <div class="actions">
                            <button type="button" class="btn-delete-ai" onclick="deleteQuestion(<?= $index ?>)">
                                <i class="fa-solid fa-trash"></i> Supprimer cette question
                            </button>
                        </div>
                    </div>
                <?php } ?>

                <div class="validation-card">
                    <h3><i class="fa-solid fa-circle-check"></i> Validation finale</h3>
                    <p>Seules les questions cochées seront enregistrées dans la base.</p>

                    <div class="actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-database"></i> Enregistrer les questions sélectionnées
                        </button>

                        <a href="generate-quiz-ai.php" class="btn btn-outline">
                            <i class="fa-solid fa-arrow-left"></i> Annuler
                        </a>
                    </div>
                </div>

            </form>

        </div>

    </main>

</div>

<script>
function deleteQuestion(index) {
    const box = document.getElementById("questionBox" + index);

    if (box) {
        box.style.opacity = "0";
        box.style.transform = "translateX(30px)";
        setTimeout(function () {
            box.remove();
        }, 250);
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("aiQuizPreviewForm");

    form.addEventListener("submit", function (e) {
        let isValid = true;
        const selected = document.querySelectorAll("input[name='selected[]']:checked");

        document.querySelectorAll(".form-control").forEach(function (el) {
            el.classList.remove("error-border", "success-border");
        });

        if (selected.length === 0) {
            alert("Veuillez sélectionner au moins une question.");
            isValid = false;
        }

        selected.forEach(function (checkbox) {
            const index = checkbox.value;
            const box = document.getElementById("questionBox" + index);

            if (!box) return;

            const fields = [
                box.querySelector("[name='questions[" + index + "][question]']"),
                box.querySelector("[name='questions[" + index + "][choix1]']"),
                box.querySelector("[name='questions[" + index + "][choix2]']"),
                box.querySelector("[name='questions[" + index + "][choix3]']"),
                box.querySelector("[name='questions[" + index + "][choix4]']")
            ];

            fields.forEach(function (field) {
                if (field.value.trim() === "") {
                    field.classList.add("error-border");
                    isValid = false;
                } else {
                    field.classList.add("success-border");
                }
            });

            const choices = fields.slice(1).map(function (field) {
                return field.value.trim().toLowerCase();
            });

            const unique = new Set(choices);

            if (unique.size !== 4) {
                alert("Les 4 choix doivent être différents.");
                fields.slice(1).forEach(function (field) {
                    field.classList.add("error-border");
                });
                isValid = false;
            }
        });

        if (!isValid) {
            e.preventDefault();
        }
    });
});
</script>

</body>
</html>