<?php
require_once '../../Controller/QuestionC.php';
require_once '../../Model/Question.php';

$questionC = new QuestionC();
$error = "";

if (!isset($_GET['id_quiz'])) {
    die("Quiz introuvable");
}

$id_quiz = $_GET['id_quiz'];

if (isset($_POST['submit'])) {

    $questionText = trim($_POST['question']);
    $choix1 = trim($_POST['choix1']);
    $choix2 = trim($_POST['choix2']);
    $choix3 = trim($_POST['choix3']);
    $choix4 = trim($_POST['choix4']);
    $bonne_reponse = $_POST['bonne_reponse'];

    $choixArray = [$choix1, $choix2, $choix3, $choix4];

    if (
        $questionText !== '' &&
        $choix1 !== '' &&
        $choix2 !== '' &&
        $choix3 !== '' &&
        $choix4 !== '' &&
        in_array($bonne_reponse, ['1','2','3','4']) &&
        preg_match('/^[A-ZÀ-Ÿ].*[?.]$/u', $questionText) &&
        count(array_unique(array_map('mb_strtolower', $choixArray))) === 4
    ) {
        $question = new Question(
            null,
            $id_quiz,
            $questionText,
            $choix1,
            $choix2,
            $choix3,
            $choix4,
            $bonne_reponse
        );

        $questionC->addQuestion($question);

        header('Location: questions.php?id=' . $id_quiz);
        exit();
    } else {
        $error = "Veuillez remplir correctement tous les champs.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter Question</title>

    <link rel="stylesheet" href="../../css/create-question.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="admin-theme">

<div class="dashboard-container">

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <i class="fa-solid fa-user-shield"></i> Admin Panel
            </div>
        </div>

        <div class="sidebar-menu">
            <a href="back-utilisateur.php" class="menu-item">
                <i class="fa-solid fa-users"></i> Gestion Utilisateurs
            </a>
            <a href="back-quiz.php" class="menu-item active">
                <i class="fa-solid fa-list-check"></i> Gestion Quiz
            </a>
        </div>

        <div class="user-profile-widget">
            <div class="user-avatar">AD</div>
            <div>
                <h4>Admin Système</h4>
                <span>Admin</span>
            </div>
        </div>
    </aside>

    
    <main class="main-content">

        <div class="top-navbar">
            <h2>Ajouter une question</h2>
        </div>

        <div class="card">

            <?php if (!empty($error)) { ?>
                <p style="color:red; margin-bottom:15px;"><?php echo $error; ?></p>
            <?php } ?>

            <form method="POST" id="questionForm">

                <div class="form-group">
                    <label>
                        <i class="fa-solid fa-circle-question"></i> Question
                    </label>
                    <textarea id="question" name="question" class="form-control" placeholder="Saisir la question"></textarea>
                    <small id="questionError" class="error-message"></small>
                </div>

                <div class="form-group">
                    <label>Choix 1</label>
                    <input type="text" id="choix1" name="choix1" class="form-control">
                    <small id="choix1Error" class="error-message"></small>
                </div>

                <div class="form-group">
                    <label>Choix 2</label>
                    <input type="text" id="choix2" name="choix2" class="form-control">
                    <small id="choix2Error" class="error-message"></small>
                </div>

                <div class="form-group">
                    <label>Choix 3</label>
                    <input type="text" id="choix3" name="choix3" class="form-control">
                    <small id="choix3Error" class="error-message"></small>
                </div>

                <div class="form-group">
                    <label>Choix 4</label>
                    <input type="text" id="choix4" name="choix4" class="form-control">
                    <small id="choix4Error" class="error-message"></small>
                </div>

                <div class="form-group">
                    <label>Bonne réponse</label>
                    <select id="bonne_reponse" name="bonne_reponse" class="form-control">
                        <option value="">-- choisir --</option>
                        <option value="1">Choix 1</option>
                        <option value="2">Choix 2</option>
                        <option value="3">Choix 3</option>
                        <option value="4">Choix 4</option>
                    </select>
                    <small id="bonneReponseError" class="error-message"></small>
                </div>

                <div class="button-group">

                    <button type="submit" name="submit" class="btn btn-primary">
                        <i class="fa-solid fa-plus"></i> Ajouter
                    </button>

                    <a href="questions.php?id=<?php echo $id_quiz; ?>" class="btn btn-outline">
                        <i class="fa-solid fa-arrow-left"></i> Retour
                    </a>

                </div>

            </form>

        </div>

    </main>

</div>

<script src="js/create-question.js"></script>

</body>
</html>