<?php
require_once '../../Controller/QuestionC.php';
require_once '../../Model/Question.php';

$questionC = new QuestionC();
$questionData = null;
$error = "";

// Charger la question à modifier
if (isset($_POST['id_question'])) {
    $id = $_POST['id_question'];
    $questionData = $questionC->showQuestion($id);
}

// Enregistrer la modification
if (isset($_POST['save'])) {
    $id = $_POST['id_question'];
    $id_quiz = $_POST['id_quiz'];

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
        in_array($bonne_reponse, ['1', '2', '3', '4']) &&
        preg_match('/^[A-ZÀ-Ÿ].*[?.]$/u', $questionText) &&
        count(array_unique(array_map('mb_strtolower', $choixArray))) === 4
    ) {
        $question = new Question(
            $id,
            $id_quiz,
            $questionText,
            $choix1,
            $choix2,
            $choix3,
            $choix4,
            $bonne_reponse
        );

        $questionC->updateQuestion($question, $id);

        header('Location: questions.php?id=' . $id_quiz);
        exit();
    } else {
        $error = "Veuillez remplir correctement tous les champs.";
        $questionData = [
            'id_question' => $id,
            'id_quiz' => $id_quiz,
            'question' => $questionText,
            'choix1' => $choix1,
            'choix2' => $choix2,
            'choix3' => $choix3,
            'choix4' => $choix4,
            'bonne_reponse' => $bonne_reponse
        ];
    }
}

if (!$questionData) {
    $questionData = [
        'id_question' => '',
        'id_quiz' => '',
        'question' => '',
        'choix1' => '',
        'choix2' => '',
        'choix3' => '',
        'choix4' => '',
        'bonne_reponse' => ''
    ];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Question</title>
    <link rel="stylesheet" href="../../css/update-question.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-theme">

<div class="dashboard-container">

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
                <h4>Admin Système</h4>
                <span>Admin</span>
            </div>
        </div>
    </aside>

    <main class="main-content">
        <div class="top-navbar">
            <h2>Modifier une question</h2>
        </div>

        <div class="card">
            <?php if (!empty($error)) { ?>
                <p style="color:red; margin-bottom:15px;"><?php echo $error; ?></p>
            <?php } ?>

            <form method="POST" id="questionForm">
                <input type="hidden" name="id_question" value="<?php echo $questionData['id_question']; ?>">
                <input type="hidden" name="id_quiz" value="<?php echo $questionData['id_quiz']; ?>">

                <div class="form-group">
                    <label for="question">
                        <i class="fa-solid fa-circle-question"></i> Question
                    </label>
                    <textarea id="question" name="question" class="form-control"><?php echo $questionData['question']; ?></textarea>
                    <small id="questionError" class="error-message"></small>
                </div>

                <div class="form-group">
                    <label for="choix1">Choix 1</label>
                    <input type="text" id="choix1" name="choix1" class="form-control" value="<?php echo $questionData['choix1']; ?>">
                    <small id="choix1Error" class="error-message"></small>
                </div>

                <div class="form-group">
                    <label for="choix2">Choix 2</label>
                    <input type="text" id="choix2" name="choix2" class="form-control" value="<?php echo $questionData['choix2']; ?>">
                    <small id="choix2Error" class="error-message"></small>
                </div>

                <div class="form-group">
                    <label for="choix3">Choix 3</label>
                    <input type="text" id="choix3" name="choix3" class="form-control" value="<?php echo $questionData['choix3']; ?>">
                    <small id="choix3Error" class="error-message"></small>
                </div>

                <div class="form-group">
                    <label for="choix4">Choix 4</label>
                    <input type="text" id="choix4" name="choix4" class="form-control" value="<?php echo $questionData['choix4']; ?>">
                    <small id="choix4Error" class="error-message"></small>
                </div>

                <div class="form-group">
                    <label for="bonne_reponse">Bonne réponse</label>
                    <select id="bonne_reponse" name="bonne_reponse" class="form-control">
                        <option value="">-- choisir --</option>
                        <option value="1" <?php if ($questionData['bonne_reponse'] == 1) echo 'selected'; ?>>Choix 1</option>
                        <option value="2" <?php if ($questionData['bonne_reponse'] == 2) echo 'selected'; ?>>Choix 2</option>
                        <option value="3" <?php if ($questionData['bonne_reponse'] == 3) echo 'selected'; ?>>Choix 3</option>
                        <option value="4" <?php if ($questionData['bonne_reponse'] == 4) echo 'selected'; ?>>Choix 4</option>
                    </select>
                    <small id="bonneReponseError" class="error-message"></small>
                </div>

                <div class="button-group">
                    <button type="submit" name="save" class="btn btn-primary">
                        <i class="fa-solid fa-pen"></i> Modifier
                    </button>

                    <a href="questions.php?id=<?php echo $questionData['id_quiz']; ?>" class="btn btn-outline">
                        <i class="fa-solid fa-arrow-left"></i> Retour
                    </a>
                </div>
            </form>
        </div>
    </main>
</div>

<script src="js/update-question.js"></script>
</body>
</html>