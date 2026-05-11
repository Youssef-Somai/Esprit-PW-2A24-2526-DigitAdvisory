<?php
require_once '../../Controller/QuizC.php';
require_once '../../Model/Quiz.php';

$quizC = new QuizC();
$quizData = null;

if (isset($_POST['id_quiz'])) {
    $id = $_POST['id_quiz'];
    $quizData = $quizC->showQuiz($id);
}

if (isset($_POST['save'])) {
    $id = $_POST['id_quiz'];
    $titre = $_POST['titre'];
    $description = $_POST['description'];

    $oldQuiz = $quizC->showQuiz($id);
    $imageName = $oldQuiz['image'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $imageName = time() . "_" . $_FILES['image']['name'];
        $tmpName = $_FILES['image']['tmp_name'];
        $uploadPath = "../../uploads/" . $imageName;
        move_uploaded_file($tmpName, $uploadPath);
    }

    $date_creation = date('Y-m-d H:i:s');

    $quiz = new Quiz($id, $titre, $description, $imageName, $date_creation);
    $quizC->updateQuiz($quiz, $id);

    header('Location: back-quiz.php');
    exit();
}

if (!$quizData) {
    $quizData = [
        'id_quiz' => '',
        'titre' => '',
        'description' => '',
        'image' => ''
    ];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Quiz</title>

    <link rel="stylesheet" href="../../css/update-quiz.css">
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
            <h2>Modifier un Quiz</h2>
        </div>

        <div class="card">
            <form method="POST" id="quizForm" enctype="multipart/form-data">

                <input type="hidden" name="id_quiz" value="<?php echo $quizData['id_quiz']; ?>">

                <div class="form-group">
                    <label for="titre">
                        <i class="fa-solid fa-heading"></i> Titre
                    </label>
                    <input type="text" name="titre" id="titre" class="form-control"
                           value="<?php echo $quizData['titre']; ?>"
                           placeholder="Saisir le titre du quiz">
                    <small id="titreError" class="error-message"></small>
                </div>

                <div class="form-group">
                    <label for="description">
                        <i class="fa-solid fa-align-left"></i> Description
                    </label>
                    <textarea name="description" id="description" class="form-control" rows="5"
                              placeholder="Saisir la description du quiz"><?php echo $quizData['description']; ?></textarea>
                    <small id="descriptionError" class="error-message"></small>
                </div>

                <div class="form-group">
                    <label>
                        <i class="fa-solid fa-image"></i> Image actuelle
                    </label>
                    <div class="current-image">
                        <img src="../../uploads/<?php echo $quizData['image']; ?>" alt="Image quiz">
                    </div>
                </div>

                <div class="form-group">
                    <label for="image">
                        <i class="fa-solid fa-image"></i> Nouvelle image
                    </label>

                    <div class="image-input">
                        <span class="input-icon">
                            <i class="fa-solid fa-image"></i>
                        </span>

                        <input type="file" name="image" id="image" class="form-control" >
                    </div>

                    <small id="imageError" class="error-message"></small>
                </div>

                <div class="button-group">
                    <button type="submit" name="save" class="btn btn-primary">
                        <i class="fa-solid fa-pen"></i> Modifier Quiz
                    </button>

                    <a href="back-quiz.php" class="btn btn-outline">
                        <i class="fa-solid fa-arrow-left"></i> Retour
                    </a>
                </div>

            </form>
        </div>
    </main>
</div>

<script src="js/update-quiz.js"></script>
</body>
</html>