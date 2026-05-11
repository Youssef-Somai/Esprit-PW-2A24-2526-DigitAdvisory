<?php
$error = "";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Générateur de Quiz IA</title>

    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body { background: #f1f5f9; font-family: 'Inter', sans-serif; }

        .dashboard-container { display: flex; min-height: 100vh; }

        .sidebar {
            width: 280px;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            z-index: 100;
            background: var(--dark);
            color: white;
            transition: var(--transition);
        }

        .sidebar-header {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header .logo { color: white; }

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
            color: var(--gray-light);
        }

        .menu-item:hover,
        .menu-item.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: var(--accent);
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

        .wrapper {
            max-width: 950px;
            margin: 0 auto;
            animation: fadeUp .7s ease;
        }

        .ai-hero {
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            color: white;
            border-radius: 24px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 15px 35px rgba(37,99,235,.18);
            position: relative;
            overflow: hidden;
        }

        .ai-hero::after {
            content: "";
            position: absolute;
            width: 230px;
            height: 230px;
            border-radius: 50%;
            background: rgba(255,255,255,.12);
            right: -80px;
            top: -80px;
        }

        .ai-hero h2 {
            margin-bottom: .7rem;
            display: flex;
            align-items: center;
            gap: .7rem;
        }

        .ai-hero p {
            opacity: .95;
            max-width: 700px;
        }

        .card {
            background: white;
            border-radius: 22px;
            box-shadow: 0 15px 35px rgba(15, 23, 42, .08);
            padding: 1.7rem;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(226,232,240,.9);
        }

        .info-box {
            background: #eff6ff;
            color: #1d4ed8;
            border-radius: 14px;
            padding: 1rem;
            margin-bottom: 1rem;
            font-size: .95rem;
            border: 1px solid #bfdbfe;
        }

        .form-group { margin-bottom: 1rem; }

        .form-group label {
            display: block;
            font-weight: 700;
            margin-bottom: .5rem;
            color: #0f172a;
        }

        .form-control {
            width: 100%;
            padding: .9rem 1rem;
            border: 1px solid #dbe3ee;
            border-radius: 14px;
            outline: none;
            transition: .25s ease;
            font-family: inherit;
            background: white;
        }

        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37,99,235,.12);
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .actions {
            display: flex;
            gap: 10px;
            margin-top: 1rem;
            flex-wrap: wrap;
        }

        .error-border {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 4px rgba(239,68,68,.12) !important;
        }

        .success-border {
            border-color: #10b981 !important;
        }

        .error-message {
            color: #ef4444;
            font-size: .85rem;
            margin-top: .35rem;
            display: block;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }

            .dashboard-container { flex-direction: column; }

            .main-content { margin-left: 0; }

            .top-navbar {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .grid-2 { grid-template-columns: 1fr; }
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
                <i class="fa-solid fa-wand-magic-sparkles"></i> Générateur de Quiz IA
            </h2>

            <span class="badge warning" style="font-size: 1rem;">
                <i class="fa-solid fa-lock"></i> Espace Sécurisé Admin
            </span>
        </div>

        <div class="wrapper">

            <div class="ai-hero">
                <h2><i class="fa-solid fa-robot"></i> Assistant intelligent de création de quiz</h2>
                <p>
                    Remplissez les paramètres du quiz. L’IA proposera automatiquement des questions,
                    des choix et les bonnes réponses. Vous pourrez ensuite modifier et valider avant l’enregistrement.
                </p>
            </div>

            <div class="card">

                <div class="info-box">
                    <i class="fa-solid fa-circle-info"></i>
                       Renseignez les paramètres du quiz, puis laissez l’IA générer automatiquement des questions adaptées à votre thème.
                </div>

                <?php if (!empty($error)) { ?>
                    <p style="color:red; margin-bottom:15px;"><?= htmlspecialchars($error) ?></p>
                <?php } ?>

                <form id="generateQuizAiForm" action="preview-quiz-ai.php" method="POST">

                    <div class="form-group">
                        <label for="titre">Titre du quiz</label>
                        <input type="text" id="titre" name="titre" class="form-control" placeholder="Ex : Introduction à la cybersécurité">
                        <small id="titreError" class="error-message"></small>
                    </div>

                    <div class="form-group">
                        <label for="theme">Thème</label>
                        <input type="text" id="theme" name="theme" class="form-control" placeholder="Ex : cybersécurité, marketing digital, PHP">
                        <small id="themeError" class="error-message"></small>
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label for="niveau">Niveau</label>
                            <select id="niveau" name="niveau" class="form-control">
                                <option value="">-- choisir --</option>
                                <option value="débutant">Débutant</option>
                                <option value="intermédiaire">Intermédiaire</option>
                                <option value="avancé">Avancé</option>
                            </select>
                            <small id="niveauError" class="error-message"></small>
                        </div>

                        <div class="form-group">
                            <label for="nb_questions">Nombre de questions</label>
                            <input type="text" id="nb_questions" name="nb_questions" class="form-control" value="5">
                            <small id="nbQuestionsError" class="error-message"></small>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label for="temps">Temps estimé en minutes</label>
                            <input type="text" id="temps" name="temps" class="form-control" value="10">
                            <small id="tempsError" class="error-message"></small>
                        </div>

                        <div class="form-group">
                            <label for="langue">Langue</label>
                            <select id="langue" name="langue" class="form-control">
                                <option value="">-- choisir --</option>
                                <option value="français">Français</option>
                                <option value="anglais">Anglais</option>
                            </select>
                            <small id="langueError" class="error-message"></small>
                        </div>
                    </div>

                    <div class="actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-robot"></i> Générer avec IA
                        </button>

                        <a href="back-quiz.php" class="btn btn-outline">
                            <i class="fa-solid fa-arrow-left"></i> Annuler
                        </a>
                    </div>

                </form>
            </div>

        </div>

    </main>

</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("generateQuizAiForm");

    const titre = document.getElementById("titre");
    const theme = document.getElementById("theme");
    const niveau = document.getElementById("niveau");
    const nbQuestions = document.getElementById("nb_questions");
    const temps = document.getElementById("temps");
    const langue = document.getElementById("langue");

    function setError(input, errorElementId, message) {
        input.classList.add("error-border");
        input.classList.remove("success-border");
        document.getElementById(errorElementId).textContent = message;
    }

    function setSuccess(input, errorElementId) {
        input.classList.remove("error-border");
        input.classList.add("success-border");
        document.getElementById(errorElementId).textContent = "";
    }

    function cleanValue(value) {
        return value.trim().replace(/\s+/g, " ");
    }

    function validateTitre() {
        const value = cleanValue(titre.value);

        if (value === "") {
            setError(titre, "titreError", "Le titre est obligatoire.");
            return false;
        }

        if (value.length < 3) {
            setError(titre, "titreError", "Le titre doit contenir au moins 3 caractères.");
            return false;
        }

        setSuccess(titre, "titreError");
        return true;
    }

    function validateTheme() {
        const value = cleanValue(theme.value);

        if (value === "") {
            setError(theme, "themeError", "Le thème est obligatoire.");
            return false;
        }

        if (value.length < 3) {
            setError(theme, "themeError", "Le thème doit contenir au moins 3 caractères.");
            return false;
        }

        setSuccess(theme, "themeError");
        return true;
    }

    function validateSelect(input, errorId, message) {
        if (input.value === "") {
            setError(input, errorId, message);
            return false;
        }

        setSuccess(input, errorId);
        return true;
    }

    function validateNumberText(input, errorId, label, min, max) {
        const value = cleanValue(input.value);

        if (value === "") {
            setError(input, errorId, label + " est obligatoire.");
            return false;
        }

        if (!/^[0-9]+$/.test(value)) {
            setError(input, errorId, label + " doit contenir uniquement des chiffres.");
            return false;
        }

        const number = parseInt(value, 10);

        if (number < min || number > max) {
            setError(input, errorId, label + " doit être entre " + min + " et " + max + ".");
            return false;
        }

        setSuccess(input, errorId);
        return true;
    }

    titre.addEventListener("input", validateTitre);
    theme.addEventListener("input", validateTheme);
    niveau.addEventListener("change", function () {
        validateSelect(niveau, "niveauError", "Le niveau est obligatoire.");
    });
    langue.addEventListener("change", function () {
        validateSelect(langue, "langueError", "La langue est obligatoire.");
    });
    nbQuestions.addEventListener("input", function () {
        validateNumberText(nbQuestions, "nbQuestionsError", "Le nombre de questions", 1, 20);
    });
    temps.addEventListener("input", function () {
        validateNumberText(temps, "tempsError", "Le temps", 1, 120);
    });

    form.addEventListener("submit", function (e) {
        const okTitre = validateTitre();
        const okTheme = validateTheme();
        const okNiveau = validateSelect(niveau, "niveauError", "Le niveau est obligatoire.");
        const okLangue = validateSelect(langue, "langueError", "La langue est obligatoire.");
        const okNb = validateNumberText(nbQuestions, "nbQuestionsError", "Le nombre de questions", 1, 20);
        const okTemps = validateNumberText(temps, "tempsError", "Le temps", 1, 120);

        if (!okTitre || !okTheme || !okNiveau || !okLangue || !okNb || !okTemps) {
            e.preventDefault();
        }
    });
});
</script>

</body>
</html>