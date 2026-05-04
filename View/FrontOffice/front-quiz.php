<?php
require_once __DIR__ . '/../../config.php';

$db = config::getConnexion();
$sql = "SELECT * FROM quiz ORDER BY id_quiz DESC";
$query = $db->prepare($sql);
$query->execute();
$quizzes = $query->fetchAll(PDO::FETCH_ASSOC);

$list = $quizzes;

$itemsPerPage = 3;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($currentPage < 1) {
    $currentPage = 1;
}

$totalQuiz = count($list);
$totalPages = ceil($totalQuiz / $itemsPerPage);

if ($totalPages < 1) {
    $totalPages = 1;
}

if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}

$offset = ($currentPage - 1) * $itemsPerPage;
$list = array_slice($list, $offset, $itemsPerPage);
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

        .quiz-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 28px;
        }

        .quiz-item {
            background: #ffffff;
            border-radius: 22px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            transition: all 0.3s ease;
            position: relative;
        }

        .quiz-item:hover {
            transform: translateY(-8px);
            box-shadow: 0 18px 45px rgba(37, 99, 235, 0.18);
            border-color: #bfdbfe;
        }

        .quiz-item img {
            width: 100%;
            height: 210px;
            object-fit: cover;
            transition: all 0.35s ease;
        }

        .quiz-item:hover img {
            transform: scale(1.05);
        }

        .quiz-body {
            padding: 24px;
        }

        .quiz-body h3 {
            font-size: 1.15rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 12px;
            line-height: 1.4;
        }

        .quiz-body p {
            color: #64748b;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 22px;
            min-height: 70px;
        }

        .quiz-body .btn {
            width: 100%;
            justify-content: center;
            padding: 12px 18px;
            border-radius: 14px;
            font-weight: 600;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border: none;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.28);
            transition: all 0.25s ease;
        }

        .quiz-body .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(37, 99, 235, 0.38);
        }

        .pagination-front {
            margin-top: 45px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 14px;
        }

        .pagination-front a {
            width: 46px;
            height: 46px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            border: 1px solid #bfdbfe;
            border-radius: 50%;
            color: #2563eb;
            text-decoration: none;
            font-size: 16px;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.16);
            transition: all 0.25s ease;
        }

        .pagination-front a:hover,
        .pagination-front a.active {
            background: #2563eb;
            color: #ffffff;
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(37, 99, 235, 0.35);
        }

        .page-info {
            padding: 10px 18px;
            background: #ffffff;
            border-radius: 999px;
            color: #2563eb;
            font-weight: 600;
            border: 1px solid #bfdbfe;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.12);
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

        <?php if (!empty($list)) { ?>
            <section class="quiz-grid">
                <?php foreach ($list as $quiz) { ?>
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

            <?php if ($totalPages > 1) { ?>
            <div class="pagination-front">

                <?php if ($currentPage > 1) { ?>
                    <a href="front-quiz.php?page=<?= $currentPage - 1 ?>" title="Précédent">
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>
                <?php } ?>

                <span class="page-info">
                    Page <?= $currentPage ?> / <?= $totalPages ?>
                </span>

                <?php if ($currentPage < $totalPages) { ?>
                    <a href="front-quiz.php?page=<?= $currentPage + 1 ?>" title="Suivant">
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                <?php } ?>

            </div>
            <?php } ?>

        <?php } else { ?>
            <div class="empty-box">
                <i class="fa-solid fa-folder-open" style="font-size: 3rem; color: var(--gray-light); margin-bottom: 1rem;"></i>
                <h3>Aucun quiz disponible</h3>
                <p>Les quiz ajoutés par l'admin apparaîtront ici automatiquement.</p>
            </div>
        <?php } ?>
    </main>
</div>
</body>
</html>