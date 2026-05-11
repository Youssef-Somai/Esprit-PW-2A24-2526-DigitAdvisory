<?php
require_once '../../Controller/QuestionC.php';

$questionC = new QuestionC();

if (!isset($_GET['id'])) {
    die("Quiz introuvable");
}

$id_quiz = $_GET['id'];


if (isset($_GET['delete_id'])) {
    $questionC->deleteQuestion($_GET['delete_id']);
    header('Location: questions.php?id=' . $id_quiz);
    exit();
}

$list = $questionC->listQuestionsByQuiz($id_quiz);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Questions</title>

    <link rel="stylesheet" href="../../css/questions.css">
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
            <h2>Questions du Quiz #<?php echo $id_quiz; ?></h2>
            <span class="badge-admin">
                <i class="fa-solid fa-lock"></i> Espace Sécurisé Admin
            </span>
        </div>

        <div class="page-header">
            <h2>Gestion des Questions</h2>

            <a href="Create-question.php?id_quiz=<?php echo $id_quiz; ?>" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Nouvelle Question
            </a>
        </div>

        <div class="card">
            <h3>Liste des questions</h3>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Question</th>
                        <th>Choix 1</th>
                        <th>Choix 2</th>
                        <th>Choix 3</th>
                        <th>Choix 4</th>
                        <th>Bonne réponse</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($list as $q) { ?>
                    <tr>
                        <td><?php echo $q['id_question']; ?></td>
                        <td><?php echo $q['question']; ?></td>
                        <td><?php echo $q['choix1']; ?></td>
                        <td><?php echo $q['choix2']; ?></td>
                        <td><?php echo $q['choix3']; ?></td>
                        <td><?php echo $q['choix4']; ?></td>
                        <td>
                            <span class="good-answer">
                                Choix <?php echo $q['bonne_reponse']; ?>
                            </span>
                        </td>

                        <td>
                            <div class="actions-cell">
                                <form method="POST" action="updateQuestion.php" style="display:inline-block;">
                                    <input type="hidden" name="id_question" value="<?php echo $q['id_question']; ?>">
                                    <input type="hidden" name="id_quiz" value="<?php echo $id_quiz; ?>">
                                    <button type="submit" name="update" class="btn-edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                </form>

                                <a href="questions.php?id=<?php echo $id_quiz; ?>&delete_id=<?php echo $q['id_question']; ?>"
                                   class="btn-delete"
                                   onclick="return confirm('Supprimer cette question ?');">
                                    <i class="fa-solid fa-trash"></i> Supprimer
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>

        </div>

    </main>
</div>

</body>
</html>