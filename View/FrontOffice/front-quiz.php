<?php
require_once __DIR__ . '/../../config.php';

$db = config::getConnexion();
$sql = "SELECT * FROM quiz ORDER BY id_quiz DESC";
$query = $db->prepare($sql);
$query->execute();
$quizzes = $query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Entreprise | Questionnaire</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        body { background-color: #f1f5f9; }
        .dashboard-container { display: flex; min-height: 100vh; }
        .sidebar { width: 280px; background: white; box-shadow: var(--shadow-md); display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 100; }
        .sidebar-header { padding: 1.5rem; border-bottom: 1px solid var(--gray-light); display: flex; align-items: center; }
        .sidebar-menu { padding: 1rem 0; flex: 1; overflow-y: auto; }
        .menu-item { padding: 0.75rem 1.5rem; display: flex; align-items: center; gap: 1rem; color: var(--gray); font-weight: 500; border-left: 3px solid transparent; text-decoration: none; }
        .menu-item:hover, .menu-item.active { background: rgba(37, 99, 235, 0.05); color: var(--primary); }
        .menu-item.active { border-left-color: var(--primary); }
        .user-profile-widget { padding: 1rem 1.5rem; border-top: 1px solid var(--gray-light); display: flex; align-items: center; gap: 1rem; background: white; }
        .user-avatar { width: 40px; height: 40px; border-radius: 50%; background: var(--primary); color: white; display: flex; justify-content: center; align-items: center; font-weight: 600; }
        .main-content { flex: 1; margin-left: 280px; padding: 2rem; }
        .top-navbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; background: white; padding: 1rem 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); }
        .quiz-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; }
        .quiz-item {
            background: white;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: .25s ease;
        }
        .quiz-item:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
        .quiz-item img {
            width: 100%;
            height: 190px;
            object-fit: cover;
        }
        .quiz-body { padding: 1.25rem; }
        .quiz-body h3 { margin-bottom: .75rem; }
        .quiz-body p { color: var(--gray); margin-bottom: 1rem; min-height: 60px; }
        .empty-box {
            background: white;
            padding: 2rem;
            text-align: center;
            border-radius: 18px;
            box-shadow: var(--shadow-sm);
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    <aside class="sidebar">
        <div class="sidebar-header"><a href="index.php" class="logo" style="text-decoration: none;"><i class="fa-solid fa-chart-pie text-primary"></i> Digit Advisory</a></div>
        <div class="sidebar-menu">
            <a href="front-entreprise-dashboard.php" class="menu-item"><i class="fa-solid fa-house"></i> Vue d'ensemble</a>
            <a href="front-utilisateur.php" class="menu-item"><i class="fa-solid fa-building"></i> Profil Entreprise</a>
            <a href="front-quiz.php" class="menu-item active"><i class="fa-solid fa-list-check"></i> Questionnaire</a>
            <a href="front-portfolio.php" class="menu-item"><i class="fa-solid fa-folder-open"></i> Mon Portfolio</a>
            <a href="front-offres.php" class="menu-item"><i class="fa-solid fa-briefcase"></i> Mes Offres de Mission</a>
            <a href="front-certification.php" class="menu-item"><i class="fa-solid fa-award"></i> Certifications ISO</a>
            <a href="front-messagerie.php" class="menu-item"><i class="fa-solid fa-comments"></i> Messagerie</a>
        </div>
        <div class="user-profile-widget">
            <div class="user-avatar">TC</div>
            <div><h4 style="font-size: 0.95rem; margin-bottom: 0.2rem;">TechCorp SAS</h4><span style="font-size: 0.8rem; color: var(--gray);">Compte Entreprise</span></div>
            <a href="login.php" style="margin-left: auto; color: var(--danger);"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
        </div>
    </aside>

    <main class="main-content">
        <div class="top-navbar">
            <h2 style="margin: 0; font-size: 1.5rem;">Questionnaires disponibles</h2>
        </div>

        <?php if (!empty($quizzes)) { ?>
            <section class="quiz-grid">
                <?php foreach ($quizzes as $quiz) { ?>
                   <div class="quiz-item">
                        <img src="/Esprit-PW-2A24-2526-DigitAdvisory/uploads/<?= htmlspecialchars($quiz['image']) ?>" alt="<?= htmlspecialchars($quiz['titre']) ?>">
                        <div class="quiz-body">
                            <h3><?= htmlspecialchars($quiz['titre']) ?></h3>
                            <p><?= htmlspecialchars($quiz['description']) ?></p>
                            <a href="front-quiz-detail.php?id=<?= (int)$quiz['id_quiz'] ?>" class="btn btn-primary">
                                Démarrer le Quiz <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                <?php } ?>
            </section>
        <?php } else { ?>
            <div class="empty-box">
                <h3>Aucun quiz disponible</h3>
                <p>Les quiz ajoutés par l’admin apparaîtront ici automatiquement.</p>
            </div>
        <?php } ?>
    </main>
</div>
</body>
</html>