<?php
require_once __DIR__ . '/../../config.php';

if (!isset($_POST['id_quiz']) || !isset($_POST['reponse'])) {
    die('Données invalides');
}

$idQuiz = (int) $_POST['id_quiz'];
$reponsesUtilisateur = $_POST['reponse'];

$db = config::getConnexion();

$sqlQuiz = "SELECT * FROM quiz WHERE id_quiz = :id";
$queryQuiz = $db->prepare($sqlQuiz);
$queryQuiz->execute(['id' => $idQuiz]);
$quiz = $queryQuiz->fetch(PDO::FETCH_ASSOC);

$sqlQuestions = "SELECT * FROM question WHERE id_quiz = :id";
$queryQuestions = $db->prepare($sqlQuestions);
$queryQuestions->execute(['id' => $idQuiz]);
$questions = $queryQuestions->fetchAll(PDO::FETCH_ASSOC);

$total = count($questions);
$bonnes = 0;

foreach ($questions as $question) {
    $idQuestion = $question['id_question'];
    $bonneReponse = (int) $question['bonne_reponse'];

    if (isset($reponsesUtilisateur[$idQuestion]) && (int)$reponsesUtilisateur[$idQuestion] === $bonneReponse) {
        $bonnes++;
    }
}

$score = $total > 0 ? round(($bonnes / $total) * 100) : 0;

$niveau = '';
$message = '';

if ($score <= 25) {
    $niveau = 'Faible';
    $message = "Votre entreprise a un niveau de digitalisation faible.";
} elseif ($score <= 50) {
    $niveau = 'Basique';
    $message = "Votre entreprise a commencé sa transformation digitale, mais il reste plusieurs améliorations à faire.";
} elseif ($score <= 75) {
    $niveau = 'Intermédiaire';
    $message = "Votre entreprise présente un bon niveau de digitalisation avec encore quelques axes de progression.";
} else {
    $niveau = 'Avancé';
    $message = "Votre entreprise est bien digitalisée et présente une forte maturité numérique.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultat du Quiz</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        body { background: #f1f5f9; font-family: 'Inter', sans-serif; }
        .result-wrapper {
            max-width: 700px;
            margin: 3rem auto;
            background: white;
            border-radius: 22px;
            padding: 2rem;
            box-shadow: var(--shadow-md);
            text-align: center;
        }
        .score-circle {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            margin: 1.5rem auto;
            background: linear-gradient(135deg, #2563eb, #60a5fa);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 700;
        }
        .level-badge {
            display: inline-block;
            padding: .7rem 1.2rem;
            background: #eff6ff;
            color: #2563eb;
            border-radius: 999px;
            font-weight: 600;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="result-wrapper">
        <h2>Résultat du questionnaire</h2>
        <?php if ($quiz) { ?>
            <p><?= htmlspecialchars($quiz['titre']) ?></p>
        <?php } ?>

        <div class="score-circle"><?= $score ?>%</div>
        <div class="level-badge">Niveau : <?= htmlspecialchars($niveau) ?></div>
        <p><?= htmlspecialchars($message) ?></p>

        <div style="margin-top: 2rem;">
            <a href="front-quiz.php" class="btn btn-secondary">Retour aux quiz</a>
            <a href="front-quiz-detail.php?id=<?= $idQuiz ?>" class="btn btn-primary">Refaire le quiz</a>
        </div>
    </div>
</body>
</html>
