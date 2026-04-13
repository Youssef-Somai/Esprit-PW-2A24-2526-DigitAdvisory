<?php
require_once '../../Controller/PortfolioController.php';
require_once '../../Controller/ElementPortfolioController.php';
require_once '../../Model/Portfolio.php';
require_once '../../Model/ElementPortfolio.php';

$portfolioC = new PortfolioController();
$elementC = new ElementPortfolioController();

// Actions GET
if (isset($_GET['action'])) {
    if ($_GET['action'] == 'deleteElement' && isset($_GET['id'])) {
        $elementC->deleteElement($_GET['id']);
        header('Location: front-portfolio.php');
        exit();
    }
    if ($_GET['action'] == 'deletePortfolio' && isset($_GET['id'])) {
        $portfolioC->deletePortfolio($_GET['id']);
        header('Location: front-portfolio.php');
        exit();
    }
}

// Formulaires POST
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action_form']) && $_POST['action_form'] == 'add_portfolio') {
        if (!empty($_POST["titre_portfolio"]) && !empty($_POST["description_portfolio"])) {
            $p = new Portfolio(null, $_POST['titre_portfolio'], $_POST['description_portfolio']);
            $portfolioC->addPortfolio($p);
            header('Location: front-portfolio.php');
            exit();
        } else { $error = "Veuillez remplir le titre et la description."; }
    } elseif (isset($_POST['action_form']) && $_POST['action_form'] == 'add_element') {
        if (!empty($_POST["id_portfolio"]) && !empty($_POST["type_element"]) && !empty($_POST["titre"]) && !empty($_POST["description"])) {
            $niveau = ($_POST['type_element'] == 'competence' && !empty($_POST['niveau'])) ? $_POST['niveau'] : null;
            $statut = ($_POST['type_element'] == 'projet' && !empty($_POST['statut'])) ? $_POST['statut'] : null;
            $el = new ElementPortfolio(null, $_POST["id_portfolio"], $_POST['type_element'], $_POST['titre'], $_POST['description'], $niveau, $statut);
            $elementC->addElement($el);
            header('Location: front-portfolio.php');
            exit();
        } else { $error = "Veuillez remplir les champs obligatoires."; }
    } elseif (isset($_POST['action_form']) && $_POST['action_form'] == 'update_element') {
        if (!empty($_POST["type_element"]) && !empty($_POST["titre"]) && !empty($_POST["description"])) {
            $niveau = ($_POST['type_element'] == 'competence' && !empty($_POST['niveau'])) ? $_POST['niveau'] : null;
            $statut = ($_POST['type_element'] == 'projet' && !empty($_POST['statut'])) ? $_POST['statut'] : null;
            $el = new ElementPortfolio($_POST['id_element'], $_POST["id_portfolio"], $_POST['type_element'], $_POST['titre'], $_POST['description'], $niveau, $statut);
            $elementC->updateElement($el, $_POST['id_element']);
            header('Location: front-portfolio.php');
            exit();
        } else { $error = "Veuillez remplir les champs obligatoires."; }
    } elseif (isset($_POST['action_form']) && $_POST['action_form'] == 'update_portfolio') {
        if (!empty($_POST["titre_portfolio"]) && !empty($_POST["description_portfolio"])) {
            $p = new Portfolio($_POST['id_portfolio'], $_POST['titre_portfolio'], $_POST['description_portfolio']);
            $portfolioC->updatePortfolio($p, $_POST['id_portfolio']);
            header('Location: front-portfolio.php');
            exit();
        } else { $error = "Veuillez remplir le titre et la description."; }
    }
}

$editElement = null;
if (isset($_GET['action']) && $_GET['action'] == 'editElement' && isset($_GET['id'])) {
    $editElement = $elementC->showElement($_GET['id']);
}

// Sélection du premier portfolio par défaut (comme c'est un compte entreprise global pour cette démo)
$stmt = $portfolioC->listPortfolios();
$portfolios = [];
while($row = $stmt->fetch()) {
    $portfolios[] = $row;
}
$myPortfolio = !empty($portfolios) ? $portfolios[0] : null;

$elements = [];
if ($myPortfolio) {
    if (isset($_GET['searchBtn'])) {
        $keyword = $_GET['search'];
        $type = $_GET['type_filter'] ?? '';
        $elements = $elementC->searchElements($myPortfolio['id_portfolio'], $keyword, $type);
    } else {
        $elements = $elementC->listElements($myPortfolio['id_portfolio']);
    }
}

$showAddPortfolioForm = isset($_GET['action']) && $_GET['action'] == 'addPortfolio';
$showAddElementForm = isset($_GET['action']) && $_GET['action'] == 'addElement' && $myPortfolio;
$showEditPortfolioForm = isset($_GET['action']) && $_GET['action'] == 'editPortfolio' && $myPortfolio;
$showEditElementForm = isset($_GET['action']) && $_GET['action'] == 'editElement' && $myPortfolio && $editElement;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Entreprise | Portfolio</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        body { background-color: #f1f5f9; }
        .dashboard-container { display: flex; min-height: 100vh; }
        .sidebar { width: 280px; background: white; box-shadow: var(--shadow-md); display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 100; transition: var(--transition); }
        .sidebar-header { padding: 1.5rem; border-bottom: 1px solid var(--gray-light); display: flex; align-items: center; }
        .sidebar-menu { padding: 1rem 0; flex: 1; overflow-y: auto; }
        .menu-item { padding: 0.75rem 1.5rem; display: flex; align-items: center; gap: 1rem; color: var(--gray); font-weight: 500; cursor: pointer; transition: var(--transition); border-left: 3px solid transparent; text-decoration: none;}
        .menu-item:hover, .menu-item.active { background: rgba(37, 99, 235, 0.05); color: var(--primary); }
        .menu-item.active { border-left-color: var(--primary); }
        .menu-item i { width: 20px; text-align: center; font-size: 1.1rem; }
        .user-profile-widget { padding: 1rem 1.5rem; border-top: 1px solid var(--gray-light); display: flex; align-items: center; gap: 1rem; background: white; }
        .user-avatar { width: 40px; height: 40px; border-radius: 50%; background: var(--primary); color: white; display: flex; justify-content: center; align-items: center; font-weight: 600; }
        .main-content { flex: 1; margin-left: 280px; padding: 2rem; }
        .top-navbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; background: white; padding: 1rem 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); }
        .card { background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-sm); margin-bottom: 2rem; }
        .badge { padding: 0.25rem 0.75rem; border-radius: var(--radius-full); font-size: 0.85rem; font-weight: 500; display: inline-block;}
        .badge.success { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .badge.primary { background: rgba(37, 99, 235, 0.1); color: var(--primary); }
        .btn { padding: 0.5rem 1rem; border-radius: var(--radius-md); border: none; cursor: pointer; text-decoration: none; display: inline-block; font-size: 0.9rem;}
        .btn-primary { background: var(--primary); color: white; }
        .btn-danger { background: var(--danger); color: white; }
        .btn-secondary { background: var(--gray); color: white; }
        .form-group { margin-bottom: 1rem; }
        .form-control { width: 100%; padding: 0.8rem; border: 1px solid var(--gray-light); border-radius: var(--radius-md); }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header"><a href="index.php" class="logo" style="text-decoration: none;"><i class="fa-solid fa-chart-pie text-primary"></i> Digit Advisory</a></div>
            <div class="sidebar-menu">
                <a href="front-entreprise-dashboard.php" class="menu-item"><i class="fa-solid fa-house"></i> Vue d'ensemble</a>
                <a href="front-utilisateur.php" class="menu-item"><i class="fa-solid fa-building"></i> Profil Entreprise</a>
                <a href="front-quiz.php" class="menu-item"><i class="fa-solid fa-list-check"></i> Questionnaire</a>
                <a href="front-portfolio.php" class="menu-item active"><i class="fa-solid fa-folder-open"></i> Mon Portfolio</a>
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
                <h2 style="margin: 0; font-size: 1.5rem;">Mon Portfolio</h2>
                <?php if ($myPortfolio && !$showAddPortfolioForm && !$showAddElementForm && !$showEditPortfolioForm && !$showEditElementForm): ?>
                    <a href="?action=addElement" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Ajouter Projet / Compétence</a>
                <?php endif; ?>
            </div>

            <?php if (!empty($error)): ?>
                <div style="background: rgba(239, 68, 68, 0.1); color: var(--danger); padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1rem;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($showAddPortfolioForm): ?>
                <div class="card fade-in-up">
                    <h3 class="mb-2">Créer votre premier Portfolio</h3>
                    <form method="POST" action="front-portfolio.php">
                        <input type="hidden" name="action_form" value="add_portfolio">
                        <div class="form-group">
                            <label>Titre</label>
                            <input type="text" name="titre_portfolio" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description_portfolio" class="form-control" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Créer le Portfolio</button>
                    </form>
                </div>

            <?php elseif ($showEditPortfolioForm): ?>
                <div class="card fade-in-up">
                    <h3 class="mb-2">Modifier le Portfolio</h3>
                    <form method="POST" action="front-portfolio.php">
                        <input type="hidden" name="action_form" value="update_portfolio">
                        <input type="hidden" name="id_portfolio" value="<?= $myPortfolio['id_portfolio'] ?>">
                        <div class="form-group">
                            <label>Titre</label>
                            <input type="text" name="titre_portfolio" class="form-control" value="<?= htmlspecialchars($myPortfolio['titre_portfolio']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description_portfolio" class="form-control" rows="3" required><?= htmlspecialchars($myPortfolio['description_portfolio']) ?></textarea>
                        </div>
                        <div style="margin-top: 1rem;">
                            <button type="submit" class="btn btn-primary">Mettre à jour</button>
                            <a href="front-portfolio.php" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>

            <?php elseif ($showAddElementForm): ?>
                <div class="card fade-in-up">
                    <h3 class="mb-2">Ajouter un Élément au Portfolio: <?= htmlspecialchars($myPortfolio['titre_portfolio']) ?></h3>
                    <form method="POST" action="front-portfolio.php">
                        <input type="hidden" name="action_form" value="add_element">
                        <input type="hidden" name="id_portfolio" value="<?= $myPortfolio['id_portfolio'] ?>">
                        <div class="form-group">
                            <label>Type d'élément *</label>
                            <select name="type_element" id="type_element" class="form-control" required onchange="toggleFields()">
                                <option value="projet">Projet</option>
                                <option value="competence">Compétence</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Titre *</label>
                            <input type="text" name="titre" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Description *</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="form-group" id="niveau-group" style="display:none;">
                            <label>Niveau</label>
                            <input type="text" name="niveau" class="form-control">
                        </div>
                        <div class="form-group" id="statut-group">
                            <label>Statut</label>
                            <input type="text" name="statut" class="form-control">
                        </div>
                        <div style="margin-top: 1rem;">
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                            <a href="front-portfolio.php" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                    <script>
                        function toggleFields() {
                            var type = document.getElementById('type_element').value;
                            document.getElementById('niveau-group').style.display = type === 'competence' ? 'block' : 'none';
                            document.getElementById('statut-group').style.display = type === 'projet' ? 'block' : 'none';
                        }
                    </script>
                </div>

            <?php elseif ($showEditElementForm): ?>
                <div class="card fade-in-up">
                    <h3 class="mb-2">Modifier un Élément</h3>
                    <form method="POST" action="front-portfolio.php">
                        <input type="hidden" name="action_form" value="update_element">
                        <input type="hidden" name="id_portfolio" value="<?= $myPortfolio['id_portfolio'] ?>">
                        <input type="hidden" name="id_element" value="<?= $editElement['id_element'] ?>">
                        <div class="form-group">
                            <label>Type d'élément *</label>
                            <select name="type_element" id="edit_type_element" class="form-control" required onchange="toggleEditFields()">
                                <option value="projet" <?= $editElement['type_element'] == 'projet' ? 'selected' : '' ?>>Projet</option>
                                <option value="competence" <?= $editElement['type_element'] == 'competence' ? 'selected' : '' ?>>Compétence</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Titre *</label>
                            <input type="text" name="titre" class="form-control" value="<?= htmlspecialchars($editElement['titre']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Description *</label>
                            <textarea name="description" class="form-control" rows="3" required><?= htmlspecialchars($editElement['description']) ?></textarea>
                        </div>
                        <div class="form-group" id="edit-niveau-group" style="display: <?= $editElement['type_element'] == 'competence' ? 'block' : 'none' ?>;">
                            <label>Niveau</label>
                            <input type="text" name="niveau" class="form-control" value="<?= htmlspecialchars($editElement['niveau'] ?? '') ?>">
                        </div>
                        <div class="form-group" id="edit-statut-group" style="display: <?= $editElement['type_element'] == 'projet' ? 'block' : 'none' ?>;">
                            <label>Statut</label>
                            <input type="text" name="statut" class="form-control" value="<?= htmlspecialchars($editElement['statut'] ?? '') ?>">
                        </div>
                        <div style="margin-top: 1rem;">
                            <button type="submit" class="btn btn-primary">Mettre à jour</button>
                            <a href="front-portfolio.php" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                    <script>
                        function toggleEditFields() {
                            var type = document.getElementById('edit_type_element').value;
                            document.getElementById('edit-niveau-group').style.display = type === 'competence' ? 'block' : 'none';
                            document.getElementById('edit-statut-group').style.display = type === 'projet' ? 'block' : 'none';
                        }
                    </script>
                </div>

            <?php elseif (!$myPortfolio): ?>
                <div class="card fade-in-up" style="text-align: center; padding: 3rem;">
                    <i class="fa-regular fa-folder-open text-primary" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                    <h3 class="mb-1">Vous n'avez pas encore de Portfolio</h3>
                    <p style="color: var(--gray); margin-bottom: 2rem;">Créez votre conteneur de portfolio pour commencer à ajouter vos compétences et projets.</p>
                    <a href="?action=addPortfolio" class="btn btn-primary">Créer mon espace Portfolio</a>
                </div>

            <?php else: ?>
                <section class="fade-in-up">
                    <div class="card">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div>
                                <h3 class="mb-1"><?= htmlspecialchars($myPortfolio['titre_portfolio']) ?></h3>
                                <p style="color: var(--gray); font-size: 0.9rem; margin-bottom: 1rem;"><?= htmlspecialchars($myPortfolio['description_portfolio']) ?></p>
                            </div>
                            <div style="display:flex; gap: 0.5rem; flex-direction:column; align-items:end;">
                                <a href="?action=editPortfolio&id=<?= $myPortfolio['id_portfolio'] ?>" class="btn btn-secondary" style="font-size: 0.8rem;"><i class="fa-solid fa-pen"></i> Modifier</a>
                                <a href="?action=deletePortfolio&id=<?= $myPortfolio['id_portfolio'] ?>" class="btn btn-danger" style="font-size: 0.8rem;" onclick="return confirm('Êtes-vous sûr de vouloir supprimer tout ce portfolio ?')"><i class="fa-solid fa-trash"></i> Supprimer</a>
                            </div>
                        </div>
                        
                        <form method="GET" action="front-portfolio.php" style="display: flex; gap: 1rem; margin-top: 2rem; margin-bottom: 2rem; background: #f8fafc; padding: 1rem; border-radius: var(--radius-md);">
                            <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="flex: 1;">
                            <select name="type_filter" class="form-control" style="width: auto;">
                                <option value="">Tous les types</option>
                                <option value="projet" <?= (isset($_GET['type_filter']) && $_GET['type_filter'] == 'projet') ? 'selected' : '' ?>>Projets</option>
                                <option value="competence" <?= (isset($_GET['type_filter']) && $_GET['type_filter'] == 'competence') ? 'selected' : '' ?>>Compétences</option>
                            </select>
                            <button type="submit" name="searchBtn" class="btn btn-primary"><i class="fa-solid fa-search"></i></button>
                            <?php if(isset($_GET['searchBtn'])): ?>
                                <a href="front-portfolio.php" class="btn btn-secondary">Reset</a>
                            <?php endif; ?>
                        </form>

                        <h4 class="mb-2">Éléments du Portfolio (<?= count($elements) ?>)</h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                            <?php foreach($elements as $el): ?>
                                <div class="card" style="border: 1px solid var(--gray-light); box-shadow: var(--shadow-sm); margin-bottom: 0;">
                                    <div style="display:flex; justify-content: space-between;">
                                        <?php if($el['type_element'] == 'competence'): ?>
                                            <h4 class="mb-1"><i class="fa-solid fa-shield-halved text-primary"></i> <?= htmlspecialchars($el['titre']) ?></h4>
                                        <?php else: ?>
                                            <h4 class="mb-1"><i class="fa-solid fa-certificate text-primary"></i> <?= htmlspecialchars($el['titre']) ?></h4>
                                        <?php endif; ?>
                                        <div style="display:flex; gap:0.5rem;">
                                            <a href="?action=editElement&id=<?= $el['id_element'] ?>" style="color: var(--gray);"><i class="fa-solid fa-pen"></i></a>
                                            <a href="?action=deleteElement&id=<?= $el['id_element'] ?>" style="color: var(--danger);" onclick="return confirm('Supprimer ?')"><i class="fa-solid fa-xmark"></i></a>
                                        </div>
                                    </div>
                                    <p style="font-size:0.9rem; color: var(--gray); margin-bottom: 0.5rem;"><?= htmlspecialchars($el['description']) ?></p>
                                    
                                    <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                                        <span class="badge <?= $el['type_element'] == 'projet' ? 'primary' : 'success' ?>" style="text-transform: capitalize;"><?= $el['type_element'] ?></span>
                                        <?php if($el['type_element'] == 'competence' && $el['niveau']): ?>
                                            <span style="font-size:0.8rem; color:var(--gray); display:flex; align-items:center;">• <?= htmlspecialchars($el['niveau']) ?></span>
                                        <?php endif; ?>
                                        <?php if($el['type_element'] == 'projet' && $el['statut']): ?>
                                            <span style="font-size:0.8rem; color:var(--gray); display:flex; align-items:center;">• <?= htmlspecialchars($el['statut']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if(count($elements) == 0): ?>
                                <p style="color: var(--gray); grid-column: 1 / -1;">Aucun élément à afficher.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
