<?php
require_once '../../Controller/QuizC.php';
require_once '../../Model/Quiz.php';

$quizC = new QuizC();
$error = "";

if (isset($_POST['submit'])) {

    $titre = trim($_POST['titre']);
    $description = trim($_POST['description']);

    if ($titre !== '' && $description !== '') {

        
        $uploadDir = __DIR__ . "/../../uploads/";

       
        if (!file_exists($uploadDir)) {
            $error = "Le dossier uploads n'existe pas.";
        }

        
        $imageName = "default.jpg";

       
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {

            $originalName = $_FILES['image']['name'];
            $tmpName = $_FILES['image']['tmp_name'];

            $imageName = time() . "_" . basename($originalName);
            $uploadPath = $uploadDir . $imageName;

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            $imageExtension = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

            if (in_array($imageExtension, $allowedExtensions)) {

                if (!move_uploaded_file($tmpName, $uploadPath)) {
                    $error = "Erreur lors du téléchargement de l'image.";
                }

            } else {
                $error = "Format image invalide. Formats autorisés : jpg, jpeg, png, webp.";
            }
        }

        
        if ($error === "") {
            $date_creation = date('Y-m-d H:i:s');

            $quiz = new Quiz(
                null,
                $titre,
                $description,
                $imageName,
                $date_creation
            );

            $quizC->addQuiz($quiz);

            header('Location: back-quiz.php');
            exit();
        }

    } else {
        $error = "Veuillez remplir le titre et la description.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter Quiz</title>

    <link rel="stylesheet" href="../../css/create-quiz.css">
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
            <h2>Ajouter un Quiz</h2>
        </div>

        <div class="card">

            <?php if (!empty($error)) { ?>
                <p style="color:red; margin-bottom:15px;"><?php echo $error; ?></p>
            <?php } ?>

            <form method="POST" id="quizForm" novalidate enctype="multipart/form-data">

                <div class="form-group">
                    <label for="titre">
                        <i class="fa-solid fa-heading"></i> Titre
                    </label>
                    <input type="text" name="titre" id="titre" class="form-control" placeholder="Saisir le titre du quiz">
                    <small id="titreError" class="error-message"></small>
                </div>

                <div class="form-group">
                    <label for="description">
                        <i class="fa-solid fa-align-left"></i> Description
                    </label>
                    <textarea name="description" id="description" class="form-control" rows="5" placeholder="Saisir la description du quiz"></textarea>
                    <small id="descriptionError" class="error-message"></small>
                </div>

                <div class="form-group">
                    <label for="image">
                        <i class="fa-solid fa-image"></i> Image
                    </label>

                    <div class="image-input">
                        <span class="input-icon" id="imageTrigger" style="cursor: pointer;">
                            <i class="fa-solid fa-image"></i>
                        </span>

                        <input type="file" name="image" id="image" class="form-control" style="display: none;">

                        <input type="text" id="imageName" class="form-control" placeholder="Choisir une image..." readonly>
                    </div>

                    <small id="imageError" class="error-message"></small>
                </div>

                <div class="button-group">
                    <button type="submit" name="submit" class="btn btn-primary">
                        <i class="fa-solid fa-plus"></i> Ajouter Quiz
                    </button>

                    <a href="back-quiz.php" class="btn btn-outline">
                        <i class="fa-solid fa-arrow-left"></i> Retour
                    </a>
                </div>

            </form>
        </div>
    </main>
</div>

<script src="js/create-quiz.js"></script>
</body>
</html>