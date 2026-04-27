<?php
require_once __DIR__ . '/../../config.php';

$db = config::getConnexion();

/* ================= QUIZ ================= */
$totalQuiz = $db->query("SELECT COUNT(*) AS total FROM quiz")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

$lastQuiz = $db->query("SELECT * FROM quiz ORDER BY date_creation DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

$firstQuiz = $db->query("SELECT * FROM quiz ORDER BY date_creation ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

$lastFiveQuiz = $db->query("SELECT * FROM quiz ORDER BY date_creation DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

/* Quiz ajoutés par mois */
$queryByMonth = $db->query("
    SELECT DATE_FORMAT(date_creation, '%Y-%m') AS mois, COUNT(*) AS total
    FROM quiz
    GROUP BY DATE_FORMAT(date_creation, '%Y-%m')
    ORDER BY mois ASC
");

$quizByMonth = $queryByMonth->fetchAll(PDO::FETCH_ASSOC);

$monthsLabels = [];
$monthsData = [];

foreach ($quizByMonth as $row) {
    $monthsLabels[] = $row['mois'];
    $monthsData[] = (int)$row['total'];
}

/* ================= QUESTIONS ================= */
$totalQuestions = $db->query("SELECT COUNT(*) AS total FROM question")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

$averageQuestions = $totalQuiz > 0 ? round($totalQuestions / $totalQuiz, 2) : 0;

$queryTopQuiz = $db->query("
    SELECT q.titre, COUNT(qus.id_question) AS total_questions
    FROM quiz q
    LEFT JOIN question qus ON q.id_quiz = qus.id_quiz
    GROUP BY q.id_quiz
    ORDER BY total_questions DESC
    LIMIT 1
");

$topQuizQuestions = $queryTopQuiz->fetch(PDO::FETCH_ASSOC);

$queryQuestionByQuiz = $db->query("
    SELECT q.titre, COUNT(qus.id_question) AS total_questions
    FROM quiz q
    LEFT JOIN question qus ON q.id_quiz = qus.id_quiz
    GROUP BY q.id_quiz
    ORDER BY total_questions DESC
");

$questionByQuiz = $queryQuestionByQuiz->fetchAll(PDO::FETCH_ASSOC);

$quizTitles = [];
$questionTotals = [];

foreach ($questionByQuiz as $row) {
    $quizTitles[] = $row['titre'];
    $questionTotals[] = (int)$row['total_questions'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques Quiz & Questions</title>

    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            background: #f1f5f9;
            font-family: 'Inter', sans-serif;
        }

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
            background: var(--dark);
            color: white;
            z-index: 100;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header .logo {
            color: white;
            font-weight: 700;
        }

        .sidebar-menu {
            padding: 1rem 0;
            flex: 1;
        }

        .menu-item {
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            color: var(--gray-light);
            text-decoration: none;
            border-left: 3px solid transparent;
            transition: .3s;
        }

        .menu-item:hover,
        .menu-item.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: var(--accent);
        }

        .user-profile-widget {
            padding: 1rem 1.5rem;
            background: rgba(0,0,0,0.2);
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
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
            background: white;
            padding: 1rem 2rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
        }

        .btn-back {
            background: linear-gradient(135deg, #2563eb, #60a5fa);
            color: white;
            padding: .8rem 1.2rem;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 600;
            transition: .3s;
        }

        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(37,99,235,.25);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            color: white;
            border-radius: 22px;
            padding: 1.5rem;
            min-height: 150px;
            box-shadow: 0 15px 35px rgba(15,23,42,.12);
            transition: .3s;
            animation: fadeUp .7s ease both;
        }

        .stat-card:hover {
            transform: translateY(-5px) scale(1.02);
        }

        .card-blue { background: linear-gradient(135deg, #2563eb, #60a5fa); }
        .card-green { background: linear-gradient(135deg, #427e9e, #5f6462); }
        .card-purple { background: linear-gradient(135deg, #7c3aeda2, #a78bfa); }
        .card-orange { background: linear-gradient(135deg, #e4a840, #fb923c); }

        .stat-icon {
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }

        .stat-label {
            font-size: .95rem;
            opacity: .95;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            margin-top: .4rem;
        }

        .stat-sub {
            margin-top: .5rem;
            font-size: .85rem;
            opacity: .9;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.2rem;
            margin-bottom: 1.5rem;
        }

        .panel,
        .chart-box {
            background: white;
            border-radius: 22px;
            padding: 1.7rem;
            box-shadow: 0 15px 35px rgba(15,23,42,.08);
            animation: fadeUp .8s ease both;
        }

        .chart-box {
            transition: .35s ease;
            overflow: hidden;
        }

        .chart-box:hover {
            transform: translateY(-6px) scale(1.015);
            box-shadow: 0 25px 50px rgba(37,99,235,.16);
        }

        .panel-title {
            margin-bottom: 1rem;
            color: #0f172a;
        }

        .mini-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 1rem;
            margin-bottom: .8rem;
            transition: .3s;
        }

        .mini-item:hover {
            transform: translateX(5px);
            background: #eef2ff;
        }

        .mini-item strong {
            display: block;
            margin-bottom: .3rem;
            color: #0f172a;
        }

        .mini-item span {
            color: #475569;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: .9rem;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }

        .data-table th {
            color: #64748b;
            font-weight: 600;
        }

        .data-table tr:hover {
            background: #f8fbff;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.2rem;
        }

        .chart-container {
            height: 500px;
            width: 100%;
            position: relative;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(18px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 1100px) {
            .stats-grid,
            .content-grid,
            .charts-grid {
                grid-template-columns: 1fr 1fr;
            }

            .chart-container {
                height: 440px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }

            .dashboard-container {
                flex-direction: column;
            }

            .main-content {
                margin-left: 0;
            }

            .stats-grid,
            .content-grid,
            .charts-grid {
                grid-template-columns: 1fr;
            }

            .chart-container {
                height: 420px;
            }
        }
    </style>
</head>

<body class="admin-theme">

<div class="dashboard-container">

    <aside class="sidebar admin-sidebar">
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
                <h4 style="font-size:.95rem; color:white;">Admin Système</h4>
                <span style="font-size:.8rem; color:var(--gray-light);">Admin</span>
            </div>
        </div>
    </aside>

    <main class="main-content">

        <div class="top-navbar">
            <h2 style="margin:0;">
                <i class="fa-solid fa-chart-column"></i> Statistiques Quiz & Questions
            </h2>

            <a href="back-quiz.php" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i> Retour
            </a>
        </div>

        <div class="stats-grid">

            <div class="stat-card card-blue">
                <div class="stat-icon"><i class="fa-solid fa-list-check"></i></div>
                <div class="stat-label">Nombre total de quiz</div>
                <div class="stat-value"><?= (int)$totalQuiz ?></div>
                <div class="stat-sub">Quiz enregistrés</div>
            </div>

            <div class="stat-card card-green">
                <div class="stat-icon"><i class="fa-solid fa-circle-question"></i></div>
                <div class="stat-label">Nombre total de questions</div>
                <div class="stat-value"><?= (int)$totalQuestions ?></div>
                <div class="stat-sub">Questions enregistrées</div>
            </div>

            <div class="stat-card card-purple">
                <div class="stat-icon"><i class="fa-solid fa-chart-simple"></i></div>
                <div class="stat-label">Moyenne questions / quiz</div>
                <div class="stat-value"><?= htmlspecialchars((string)$averageQuestions) ?></div>
                <div class="stat-sub">Calcul automatique</div>
            </div>

            <div class="stat-card card-orange">
                <div class="stat-icon"><i class="fa-solid fa-trophy"></i></div>
                <div class="stat-label">Quiz avec plus de questions</div>
                <div class="stat-value">
                    <?= $topQuizQuestions ? htmlspecialchars($topQuizQuestions['total_questions']) : 0 ?>
                </div>
                <div class="stat-sub">
                    <?= $topQuizQuestions ? htmlspecialchars($topQuizQuestions['titre']) : 'Aucun quiz' ?>
                </div>
            </div>

        </div>

        <div class="content-grid">

            <div class="panel">
                <h3 class="panel-title">Détails rapides Quiz</h3>

                <div class="mini-item">
                    <strong>Dernier quiz ajouté</strong>
                    <span><?= $lastQuiz ? htmlspecialchars($lastQuiz['titre']) . ' — ' . htmlspecialchars($lastQuiz['date_creation']) : 'Aucune donnée' ?></span>
                </div>

                <div class="mini-item">
                    <strong>Premier quiz ajouté</strong>
                    <span><?= $firstQuiz ? htmlspecialchars($firstQuiz['titre']) . ' — ' . htmlspecialchars($firstQuiz['date_creation']) : 'Aucune donnée' ?></span>
                </div>
            </div>

            <div class="panel">
                <h3 class="panel-title">Les 5 derniers quiz</h3>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($lastFiveQuiz)) { ?>
                            <?php foreach ($lastFiveQuiz as $quiz) { ?>
                                <tr>
                                    <td><?= htmlspecialchars($quiz['id_quiz']) ?></td>
                                    <td><?= htmlspecialchars($quiz['titre']) ?></td>
                                    <td><?= htmlspecialchars($quiz['date_creation']) ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="3">Aucun quiz trouvé.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

        </div>

        <div class="charts-grid">

            <div class="chart-box">
                <h3 class="panel-title">Évolution des quiz par mois</h3>
                <div class="chart-container">
                    <canvas id="quizByMonthChart"></canvas>
                </div>
            </div>

            <div class="chart-box">
                <h3 class="panel-title">Répartition des questions par quiz</h3>
                <div class="chart-container">
                    <canvas id="questionsByQuizChart"></canvas>
                </div>
            </div>

        </div>

    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const monthsLabels = <?= json_encode($monthsLabels) ?>;
const monthsData = <?= json_encode($monthsData) ?>;

const quizTitles = <?= json_encode($quizTitles) ?>;
const questionTotals = <?= json_encode($questionTotals) ?>;

const quizCtx = document.getElementById('quizByMonthChart').getContext('2d');
const quizGradient = quizCtx.createLinearGradient(0, 0, 0, 500);
quizGradient.addColorStop(0, 'rgba(37, 99, 235, 0.38)');
quizGradient.addColorStop(1, 'rgba(37, 99, 235, 0.02)');

new Chart(quizCtx, {
    type: 'line',
    data: {
        labels: monthsLabels,
        datasets: [{
            label: 'Quiz ajoutés',
            data: monthsData,
            borderColor: '#2563eb',
            backgroundColor: quizGradient,
            fill: true,
            tension: 0.45,
            pointRadius: 7,
            pointHoverRadius: 10,
            borderWidth: 4,
            pointBackgroundColor: '#2563eb',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 3
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        layout: {
            padding: 20
        },
        animation: {
            duration: 1800,
            easing: 'easeOutQuart'
        },
        plugins: {
            legend: {
                labels: {
                    font: {
                        size: 14,
                        weight: 'bold'
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0,
                    font: {
                        size: 13
                    }
                }
            },
            x: {
                ticks: {
                    font: {
                        size: 13
                    }
                }
            }
        }
    }
});

const questionCtx = document.getElementById('questionsByQuizChart').getContext('2d');

new Chart(questionCtx, {
    type: 'doughnut',
    data: {
        labels: quizTitles,
        datasets: [{
            label: 'Questions',
            data: questionTotals,
            backgroundColor: [
                '#7c3aed',
                '#2563eb',
                '#10b981',
                '#f59e0b',
                '#ef4444',
                '#0ea5e9',
                '#ec4899',
                '#14b8a6'
            ],
            borderColor: '#ffffff',
            borderWidth: 4,
            hoverOffset: 18
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '58%',
        layout: {
            padding: 25
        },
        animation: {
            animateRotate: true,
            animateScale: true,
            duration: 1800,
            easing: 'easeOutBounce'
        },
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 18,
                    font: {
                        size: 13,
                        weight: 'bold'
                    }
                }
            }
        }
    }
});
</script>

</body>
</html>