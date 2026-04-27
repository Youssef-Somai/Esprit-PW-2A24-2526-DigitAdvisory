<?php
session_start();

require_once '../../config.php';
require_once '../../Controller/QuizC.php';
require_once '../../Model/Quiz.php';
require_once '../../Controller/QuestionC.php';
require_once '../../Model/Question.php';


if (!isset($_SESSION['quiz_ai_generated'])) {
    die("Aucun quiz généré.");
}

$quizData = $_SESSION['quiz_ai_generated'];


$questionsPost = $_POST['questions'] ?? [];
$selected = $_POST['selected'] ?? [];


if (empty($selected)) {
    die("Veuillez sélectionner au moins une question.");
}


$titre = trim($_POST['titre'] ?? ($quizData['titre'] ?? 'Quiz IA'));
$description = trim($_POST['description'] ?? ($quizData['description'] ?? ''));


$image = 'default.jpg';
$date_creation = date('Y-m-d H:i:s');


$db = config::getConnexion();

try {
    $db->beginTransaction();

    // ==============================
    // 1) Ajouter le quiz
    // ==============================
    $sqlQuiz = "INSERT INTO quiz (titre, description, image, date_creation)
                VALUES (:titre, :description, :image, :date_creation)";

    $queryQuiz = $db->prepare($sqlQuiz);
    $queryQuiz->execute([
        'titre' => $titre,
        'description' => $description,
        'image' => $image,
        'date_creation' => $date_creation
    ]);

   
    $idQuiz = $db->lastInsertId();

    // ==============================
    // 2) Ajouter seulement les questions cochées
    // ==============================
    $sqlQuestion = "INSERT INTO question 
        (question, choix1, choix2, choix3, choix4, bonne_reponse, id_quiz)
        VALUES 
        (:question, :choix1, :choix2, :choix3, :choix4, :bonne_reponse, :id_quiz)";

    $queryQuestion = $db->prepare($sqlQuestion);

    foreach ($selected as $index) {

        if (!isset($questionsPost[$index])) {
            continue;
        }

        $q = $questionsPost[$index];

        $queryQuestion->execute([
            'question' => trim($q['question'] ?? ''),
            'choix1' => trim($q['choix1'] ?? ''),
            'choix2' => trim($q['choix2'] ?? ''),
            'choix3' => trim($q['choix3'] ?? ''),
            'choix4' => trim($q['choix4'] ?? ''),
            'bonne_reponse' => (int)($q['bonne_reponse'] ?? 1),
            'id_quiz' => $idQuiz
        ]);
    }

    $db->commit();

    
    unset($_SESSION['quiz_ai_generated']);

  
    header("Location: back-quiz.php");
    exit();

} catch (Exception $e) {
    $db->rollBack();
    die("Erreur lors de l'enregistrement : " . $e->getMessage());
}
?>