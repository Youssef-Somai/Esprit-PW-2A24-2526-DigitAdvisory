<?php
require_once '../../Controller/QuizC.php';

$quizC = new QuizC();

if (isset($_GET['id'])) {
    $quizC->deleteQuiz($_GET['id']);
    header('Location: back-quiz.php');
    exit();
}

$list = $quizC->listQuiz();

if ($list instanceof PDOStatement) {
    $list = $list->fetchAll(PDO::FETCH_ASSOC);
}

if (!is_array($list)) {
    $list = [];
}

$search = '';
$sort = $_GET['sort'] ?? 'id_desc';

$list = array_values($list);

// Tri
usort($list, function ($a, $b) use ($sort) {
    switch ($sort) {
        case 'id_asc':
            return $a['id_quiz'] <=> $b['id_quiz'];

        case 'id_desc':
            return $b['id_quiz'] <=> $a['id_quiz'];

        case 'titre_asc':
            return strcmp(mb_strtolower($a['titre']), mb_strtolower($b['titre']));

        case 'titre_desc':
            return strcmp(mb_strtolower($b['titre']), mb_strtolower($a['titre']));

        case 'date_asc':
            return strtotime($a['date_creation']) <=> strtotime($b['date_creation']);

        case 'date_desc':
            return strtotime($b['date_creation']) <=> strtotime($a['date_creation']);

        default:
            return $b['id_quiz'] <=> $a['id_quiz'];
    }
});

// ==============================
// PAGINATION
// ==============================
$itemsPerPage = 3;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($currentPage < 1) $currentPage = 1;

$totalItems = count($list);
$totalPages = ceil($totalItems / $itemsPerPage);

if ($totalPages < 1) $totalPages = 1;
if ($currentPage > $totalPages) $currentPage = $totalPages;

$offset = ($currentPage - 1) * $itemsPerPage;

$list = array_slice($list, $offset, $itemsPerPage);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Back Office | Gestion Quiz</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/style.css">

    <style>
        .sidebar { background: var(--dark); color: white; }
        .sidebar .menu-item { color: var(--gray-light); }
        .sidebar .menu-item:hover, .sidebar .menu-item.active { background: rgba(255,255,255,0.1); color: white; border-left-color: var(--accent); }
        .sidebar-header { border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header .logo { color: white; }
        .user-profile-widget { background: rgba(0,0,0,0.2); border-top: 1px solid rgba(255,255,255,0.1); }
        .dashboard-container { display: flex; min-height: 100vh; }
        .sidebar { width: 280px; display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 100; transition: var(--transition); }
        .sidebar-header { padding: 1.5rem; display: flex; align-items: center; }
        .sidebar-menu { padding: 1rem 0; flex: 1; overflow-y: auto; }
        .menu-item { padding: 0.75rem 1.5rem; display: flex; align-items: center; gap: 1rem; font-weight: 500; cursor: pointer; transition: var(--transition); border-left: 3px solid transparent; text-decoration: none; }
        .menu-item i { width: 20px; text-align: center; font-size: 1.1rem; }
        .user-profile-widget { padding: 1rem 1.5rem; display: flex; align-items: center; gap: 1rem; }
        .user-avatar { width: 40px; height: 40px; border-radius: 50%; background: var(--accent); color: white; display: flex; justify-content: center; align-items: center; font-weight: 600; }
        .main-content { flex: 1; margin-left: 280px; padding: 2rem; background: #f1f5f9; min-height: 100vh; }
        .top-navbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; background: white; padding: 1rem 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); }
        .card { background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-sm); margin-bottom: 2rem; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--gray-light); }
        .data-table th { color: var(--gray); font-weight: 500; }
        .badge { padding: 0.25rem 0.75rem; border-radius: var(--radius-full); font-size: 0.85rem; font-weight: 500; display: inline-block; }
        .badge.success { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .badge.primary { background: rgba(37, 99, 235, 0.1); color: var(--primary); }
        .btn-sm { padding: 0.4rem 0.8rem; font-size: 0.85rem; }

        .quiz-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        .toolbar {
            display: flex;
            gap: 12px;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .search-sort-form {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-input,
        .sort-select,
        .column-filter {
            padding: 0.75rem 1rem;
            border: 1px solid #dbe3ee;
            border-radius: 12px;
            outline: none;
            font-family: inherit;
            transition: 0.2s;
            background: white;
        }

        .search-input {
            min-width: 280px;
        }

        .search-input:focus,
        .sort-select:focus,
        .column-filter:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .data-table tbody tr {
            transition: 0.25s ease;
        }

        .data-table tbody tr:hover {
            background: #f8fbff;
            transform: scale(1.005);
        }

        .search-animate {
            animation: searchPulse 0.35s ease;
        }

        @keyframes searchPulse {
            from {
                opacity: 0.4;
                transform: translateY(6px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        mark.highlight {
            background: #fef08a;
            color: #854d0e;
            padding: 2px 4px;
            border-radius: 5px;
            font-weight: 700;
        }

        .no-result-row {
            text-align: center;
            color: #64748b;
            font-weight: 600;
            padding: 1.2rem;
        }

        .pagination-pro {
            margin-top: 25px;
            display: flex;
            justify-content: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .pagination-pro a,
        .pagination-pro span {
            padding: 8px 13px;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 600;
            border: 1px solid #dbe3ee;
            color: #2563eb;
            background: white;
            transition: .25s;
        }

        .pagination-pro a:hover {
            background: #2563eb;
            color: white;
            transform: translateY(-2px);
        }

        .pagination-pro .active-page {
            background: #2563eb;
            color: white;
        }

        .btn-stats {
            transition: 0.3s ease;
        }

        .btn-stats:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(37,99,235,.25);
        }

        .data-table td:last-child {
            white-space: nowrap;
        }

        .data-table td:last-child a,
        .data-table td:last-child form {
            display: inline-flex;
            vertical-align: middle;
            margin: 3px;
        }

        a.btn-pdf {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            padding: 0.4rem 0.8rem !important;
            border-radius: 999px !important;
            border: 2px solid #ef4444 !important;
            background: transparent !important;
            color: #ef4444 !important;
            font-size: 0.85rem !important;
            font-weight: 600 !important;
            text-decoration: none !important;
            line-height: 1 !important;
            width: auto !important;
            min-width: fit-content !important;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        a.btn-pdf:hover {
            background: #ef4444 !important;
            color: white !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(239, 68, 68, 0.3);
        }

        a.btn-pdf i {
            font-size: 14px !important;
            display: inline-block !important;
        }
    </style>
</head>

<body class="admin-theme">
    <div class="dashboard-container">
        <!-- Sidebar ADMIN -->
        <aside class="sidebar admin-sidebar slide-in-right">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fa-solid fa-user-shield text-accent"></i> Admin Panel
                </div>
            </div>

            <div class="sidebar-menu">
                <a href="back-utilisateur.php" class="menu-item"><i class="fa-solid fa-users"></i> Gestion Utilisateurs</a>
                <a href="back-quiz.php" class="menu-item active"><i class="fa-solid fa-list-check"></i> Gestion Quiz</a>
                <a href="back-portfolio.php" class="menu-item"><i class="fa-solid fa-folder-open"></i> Gestion Portfolios</a>
                <a href="back-offres.php" class="menu-item"><i class="fa-solid fa-briefcase"></i> Gestion Offres</a>
                <a href="back-certification.php" class="menu-item"><i class="fa-solid fa-award"></i> Gestion Certifications</a>
                <a href="back-messagerie.php" class="menu-item"><i class="fa-solid fa-comments"></i> Gestion Messagerie</a>
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
                <h2 style="margin: 0; font-size: 1.5rem;">Administration - Rôle Superviseur</h2>
                <span class="badge warning" style="font-size: 1rem;"><i class="fa-solid fa-lock"></i> Espace Sécurisé Admin</span>
            </div>

            <section class="fade-in-up">
                <div style="display: flex; justify-content: space-between; align-items: center;" class="mb-2">
                    <h2>Gestion des Quiz</h2>
                    <button class="btn btn-primary" onclick="window.location.href='Create-quiz.php'">
                        <i class="fa-solid fa-plus"></i> Nouveau Quiz
                    </button>
                </div>

                <a href="stats-quiz.php" class="btn btn-stats">
                    <i class="fa-solid fa-chart-column"></i> Statistiques
                </a>

                <div class="card admin-card hover-zoom">
                    <div class="toolbar">
                        <h3 style="margin:0;">Liste des Quiz actifs</h3>

                        <div class="search-sort-form">
                            <input
                                type="text"
                                id="searchInput"
                                class="search-input"
                                placeholder="Recherche dynamique..."
                            >

                            <select id="columnFilter" class="column-filter">
                                <option value="all">Toutes les colonnes</option>
                                <option value="id">ID</option>
                                <option value="titre">Titre</option>
                                <option value="description">Description</option>
                                <option value="date">Date création</option>
                            </select>

                            <select name="sort" class="sort-select" onchange="window.location.href='?sort=' + this.value">
                                <option value="id_desc" <?= $sort === 'id_desc' ? 'selected' : '' ?>>ID décroissant</option>
                                <option value="id_asc" <?= $sort === 'id_asc' ? 'selected' : '' ?>>ID croissant</option>
                                <option value="titre_asc" <?= $sort === 'titre_asc' ? 'selected' : '' ?>>Titre A-Z</option>
                                <option value="titre_desc" <?= $sort === 'titre_desc' ? 'selected' : '' ?>>Titre Z-A</option>
                                <option value="date_desc" <?= $sort === 'date_desc' ? 'selected' : '' ?>>Date récente</option>
                                <option value="date_asc" <?= $sort === 'date_asc' ? 'selected' : '' ?>>Date ancienne</option>
                            </select>
                        </div>
                    </div>

                    <a href="generate-quiz-ai.php" class="btn btn-stats">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> Générer Quiz IA
                    </a>

                    <table class="data-table mt-1" id="quizTable">
                        <thead>
                            <tr>
                                <th>id_quiz</th>
                                <th>titre</th>
                                <th>description</th>
                                <th>image</th>
                                <th>date_creation</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (!empty($list)) { ?>
                                <?php foreach ($list as $quiz) { ?>
                                    <tr>
                                        <td class="col-id"><?php echo $quiz['id_quiz']; ?></td>
                                        <td class="col-titre"><?php echo htmlspecialchars($quiz['titre']); ?></td>
                                        <td class="col-description"><?php echo htmlspecialchars($quiz['description']); ?></td>
                                        <td>
                                            <img src="../../uploads/<?php echo htmlspecialchars($quiz['image']); ?>" alt="quiz" class="quiz-image">
                                        </td>
                                        <td class="col-date"><?php echo htmlspecialchars($quiz['date_creation']); ?></td>
                                        <td>
                                            <a href="questions.php?id=<?php echo $quiz['id_quiz']; ?>" class="btn btn-outline btn-sm">
                                                <i class="fa-solid fa-eye"></i> Questions
                                            </a>

                                            <a href="export-quiz-print.php?id=<?php echo $quiz['id_quiz']; ?>"
                                               class="btn-pdf"
                                               target="_blank">
                                                <i class="fa-solid fa-file-pdf"></i> PDF
                                            </a>

                                            <form method="POST" action="updateQuiz.php" style="display:inline-block;">
                                                <input type="hidden" value="<?php echo $quiz['id_quiz']; ?>" name="id_quiz">
                                                <button type="submit" name="update" class="btn btn-outline btn-sm">
                                                    <i class="fa-solid fa-pen"></i>
                                                </button>
                                            </form>

                                            <a href="back-quiz.php?id=<?php echo $quiz['id_quiz']; ?>"
                                               class="btn-delete"
                                               onclick="return confirm('Voulez-vous vraiment supprimer ce quiz ?');">
                                                <i class="fa-solid fa-trash"></i> Supprimer
                                            </a>
                                        </td>
                                    </tr>
                                <?php } ?>

                                <tr id="noResultRow" style="display:none;">
                                    <td colspan="6" class="no-result-row">
                                        Aucun résultat trouvé.
                                    </td>
                                </tr>

                            <?php } else { ?>
                                <tr>
                                    <td colspan="6" style="text-align:center;">Aucun quiz trouvé.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>

                    <!-- ============================== -->
                    <!-- PAGINATION PRO -->
                    <!-- ============================== -->

                    <div class="pagination-pro">

                        <?php if ($currentPage > 1) { ?>
                            <a href="?sort=<?= urlencode($sort) ?>&page=<?= $currentPage - 1 ?>">
                                <i class="fa-solid fa-angle-left"></i>
                            </a>
                        <?php } ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                            <?php if ($i == $currentPage) { ?>
                                <span class="active-page"><?= $i ?></span>
                            <?php } else { ?>
                                <a href="?sort=<?= urlencode($sort) ?>&page=<?= $i ?>">
                                    <?= $i ?>
                                </a>
                            <?php } ?>
                        <?php } ?>

                        <?php if ($currentPage < $totalPages) { ?>
                            <a href="?sort=<?= urlencode($sort) ?>&page=<?= $currentPage + 1 ?>">
                                <i class="fa-solid fa-angle-right"></i>
                            </a>
                        <?php } ?>

                    </div>
                </div>
            </section>
        </main>
    </div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById("searchInput");
    const columnFilter = document.getElementById("columnFilter");
    const rows = document.querySelectorAll("#quizTable tbody tr:not(#noResultRow)");
    const noResultRow = document.getElementById("noResultRow");

    function removeHighlights(element) {
        element.innerHTML = element.textContent;
    }

    function highlightText(element, keyword) {
        const text = element.textContent;

        if (keyword === "") {
            element.innerHTML = text;
            return;
        }

        const escapedKeyword = keyword.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
        const regex = new RegExp("(" + escapedKeyword + ")", "gi");

        element.innerHTML = text.replace(regex, "<mark class='highlight'>$1</mark>");
    }

    function getCellByFilter(row, filter) {
        if (filter === "id") return row.querySelector(".col-id");
        if (filter === "titre") return row.querySelector(".col-titre");
        if (filter === "description") return row.querySelector(".col-description");
        if (filter === "date") return row.querySelector(".col-date");
        return null;
    }

    function dynamicSearch() {
        const value = input.value.toLowerCase().trim();
        const filter = columnFilter.value;
        let visibleCount = 0;

        rows.forEach(function (row) {
            const cellsToClean = [
                row.querySelector(".col-id"),
                row.querySelector(".col-titre"),
                row.querySelector(".col-description"),
                row.querySelector(".col-date")
            ];

            cellsToClean.forEach(function (cell) {
                if (cell) removeHighlights(cell);
            });

            let text = "";

            if (filter === "all") {
                text = row.textContent.toLowerCase();
            } else {
                const cell = getCellByFilter(row, filter);
                text = cell ? cell.textContent.toLowerCase() : "";
            }

            if (text.includes(value)) {
                row.style.display = "";
                row.classList.remove("search-animate");
                void row.offsetWidth;
                row.classList.add("search-animate");
                visibleCount++;

                if (value !== "") {
                    if (filter === "all") {
                        cellsToClean.forEach(function (cell) {
                            if (cell) highlightText(cell, value);
                        });
                    } else {
                        const cell = getCellByFilter(row, filter);
                        if (cell) highlightText(cell, value);
                    }
                }

            } else {
                row.style.display = "none";
            }
        });

        if (noResultRow) {
            noResultRow.style.display = visibleCount === 0 ? "" : "none";
        }
    }

    input.addEventListener("input", dynamicSearch);
    columnFilter.addEventListener("change", dynamicSearch);
});
</script>

</body>
</html>