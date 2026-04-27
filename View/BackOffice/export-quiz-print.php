<?php
require_once __DIR__ . '/../../config.php';

if (!isset($_GET['id'])) {
    die('Quiz introuvable');
}

$id = (int) $_GET['id'];
$db = config::getConnexion();

$query = $db->prepare("SELECT * FROM quiz WHERE id_quiz = :id");
$query->execute(['id' => $id]);
$quiz = $query->fetch(PDO::FETCH_ASSOC);

if (!$quiz) {
    die('Quiz non trouvé');
}

$queryQuestions = $db->prepare("SELECT * FROM question WHERE id_quiz = :id ORDER BY id_question ASC");
$queryQuestions->execute(['id' => $id]);
$questions = $queryQuestions->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Export Quiz</title>

<link rel="stylesheet" href="../../css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
.sidebar { background: var(--dark); color: white; }
.sidebar .menu-item { color: var(--gray-light); }
.sidebar .menu-item:hover,
.sidebar .menu-item.active {
    background: rgba(255,255,255,0.1);
    color: white;
    border-left-color: var(--accent);
}
.sidebar-header { border-bottom: 1px solid rgba(255,255,255,0.1); }
.sidebar-header .logo { color: white; }

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

.export-container {
    max-width: 950px;
    margin: auto;
}

.print-btn {
    padding: 0.75rem 1.2rem;
    background: linear-gradient(135deg, #ef4444, #f97316);
    color: white;
    border: none;
    border-radius: 999px;
    cursor: pointer;
    font-weight: 700;
    transition: .3s;
}

.print-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 18px rgba(239,68,68,.3);
}

.quiz-card {
    background: white;
    padding: 30px;
    border-radius: 22px;
    box-shadow: 0 15px 35px rgba(15, 23, 42, 0.08);
    margin-bottom: 25px;
    border-top: 6px solid #2563eb;
}

.quiz-header {
    display: flex;
    gap: 20px;
    align-items: center;
}

.quiz-image {
    width: 150px;
    height: 150px;
    object-fit: cover;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
}

.quiz-title {
    font-size: 28px;
    margin: 0 0 10px;
    color: #1e293b;
}

.quiz-description {
    color: #475569;
    line-height: 1.6;
}

.export-badge {
    display: inline-block;
    margin-top: 10px;
    padding: 7px 12px;
    border-radius: 999px;
    background: #eff6ff;
    color: #2563eb;
    font-weight: bold;
    font-size: 13px;
}

.question-card {
    background: white;
    padding: 22px;
    border-radius: 18px;
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.06);
    margin-bottom: 18px;
    border-left: 5px solid #7c3aed;
    page-break-inside: avoid;
}

.question-title {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 15px;
    color: #111827;
}

.choice {
    padding: 12px 14px;
    border-radius: 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    margin-bottom: 10px;
}

.correct {
    background: #ecfdf5;
    border-color: #10b981;
    color: #047857;
    font-weight: bold;
}

.answer {
    margin-top: 12px;
    color: #059669;
    font-weight: bold;
}

.empty {
    background: white;
    padding: 20px;
    border-radius: 16px;
    text-align: center;
    color: #64748b;
}

@media print {
    .sidebar,
    .top-navbar {
        display: none !important;
    }

    .main-content {
        margin-left: 0;
        padding: 0;
        background: white;
    }

    body {
        background: white;
    }

    .quiz-card,
    .question-card {
        box-shadow: none;
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
                <i class="fa-solid fa-file-pdf"></i> Exportation Quiz
            </h2>

            <div>
                <a href="back-quiz.php" class="btn btn-outline">
                    <i class="fa-solid fa-arrow-left"></i> Retour
                </a>

                <button class="print-btn" onclick="window.print()">
                    <i class="fa-solid fa-file-pdf"></i> Exporter PDF
                </button>
            </div>
        </div>

        <div class="export-container">

            <div class="quiz-card">
                <div class="quiz-header">

                    <?php if (!empty($quiz['image'])) { ?>
                        <img class="quiz-image" src="../../uploads/<?= htmlspecialchars($quiz['image']) ?>" alt="Image Quiz">
                    <?php } ?>

                    <div>
                        <h1 class="quiz-title"><?= htmlspecialchars($quiz['titre']) ?></h1>

                        <p class="quiz-description">
                            <?= htmlspecialchars($quiz['description']) ?>
                        </p>

                        <span class="export-badge">
                            Date création : <?= htmlspecialchars($quiz['date_creation']) ?>
                        </span>

                        <span class="export-badge">
                            Nombre de questions : <?= count($questions) ?>
                        </span>
                    </div>
                </div>
            </div>

            <?php if (!empty($questions)) { ?>

                <?php foreach ($questions as $index => $question) {
                    $bonne = (int)$question['bonne_reponse'];
                ?>
                    <div class="question-card">

                        <div class="question-title">
                            Question <?= $index + 1 ?> : <?= htmlspecialchars($question['question']) ?>
                        </div>

                        <div class="choice <?= $bonne === 1 ? 'correct' : '' ?>">
                            1. <?= htmlspecialchars($question['choix1']) ?>
                        </div>

                        <div class="choice <?= $bonne === 2 ? 'correct' : '' ?>">
                            2. <?= htmlspecialchars($question['choix2']) ?>
                        </div>

                        <div class="choice <?= $bonne === 3 ? 'correct' : '' ?>">
                            3. <?= htmlspecialchars($question['choix3']) ?>
                        </div>

                        <div class="choice <?= $bonne === 4 ? 'correct' : '' ?>">
                            4. <?= htmlspecialchars($question['choix4']) ?>
                        </div>

                        <div class="answer">
                            Bonne réponse : Choix <?= htmlspecialchars($question['bonne_reponse']) ?>
                        </div>

                    </div>
                <?php } ?>

            <?php } else { ?>

                <div class="empty">
                    Aucune question trouvée pour ce quiz.
                </div>

            <?php } ?>

        </div>

    </main>

</div>

</body>
</html>