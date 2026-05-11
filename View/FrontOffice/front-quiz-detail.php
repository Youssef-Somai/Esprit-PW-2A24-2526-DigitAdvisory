<?php
require_once __DIR__ . '/../../config.php';

if (!isset($_GET['id'])) {
    die('Quiz introuvable');
}

$idQuiz = (int) $_GET['id'];
$db = config::getConnexion();

$sqlQuiz = "SELECT * FROM quiz WHERE id_quiz = :id";
$queryQuiz = $db->prepare($sqlQuiz);
$queryQuiz->execute(['id' => $idQuiz]);
$quiz = $queryQuiz->fetch(PDO::FETCH_ASSOC);

if (!$quiz) {
    die('Quiz non trouvé');
}


$sqlQuestions = "SELECT * FROM question WHERE id_quiz = :id ORDER BY id_question ASC";
$queryQuestions = $db->prepare($sqlQuestions);
$queryQuestions->execute(['id' => $idQuiz]);
$questions = $queryQuestions->fetchAll(PDO::FETCH_ASSOC);


$parPage = 20; 

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$total = count($questions);
$totalPages = ceil($total / $parPage);

$offset = ($page - 1) * $parPage;
$questions = array_slice($questions, $offset, $parPage);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($quiz['titre']) ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body { background: #f1f5f9; font-family: 'Inter', sans-serif; }

       
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: white;
            box-shadow: var(--shadow-md);
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            z-index: 100;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-light);
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
            color: var(--gray);
            font-weight: 500;
            border-left: 3px solid transparent;
            text-decoration: none;
        }

        .menu-item:hover,
        .menu-item.active {
            background: rgba(37, 99, 235, 0.05);
            color: var(--primary);
        }

        .menu-item.active {
            border-left-color: var(--primary);
        }

        .user-profile-widget {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--gray-light);
            display: flex;
            align-items: center;
            gap: 1rem;
            background: white;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
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

        .wrapper { max-width: 900px; margin: 2rem auto; }

        .header-box, .question-box {
            background: white;
            border-radius: 18px;
            box-shadow: var(--shadow-sm);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .question-box h4 { margin-bottom: 1rem; }

        .choice {
            display: block;
            padding: .9rem 1rem;
            margin-bottom: .75rem;
            border: 1px solid #dbe3ee;
            border-radius: 12px;
            cursor: pointer;
            transition: .2s;
        }

        .choice:hover { background: #eff6ff; border-color: var(--primary); }

        .submit-box { text-align: center; margin-top: 2rem; }

        .voice-btn {
            border: none;
            background: linear-gradient(135deg, #2563eb, #60a5fa);
            color: white;
            padding: 8px 14px;
            border-radius: 999px;
            cursor: pointer;
            margin-bottom: 15px;
            transition: .3s;
        }

        .voice-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(37,99,235,.25);
        }

    /* ===== Pagination flèches PRO animées ===== */
.pagination-front {
    margin-top: 45px;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 16px;
}

.pagination-front a {
    width: 52px;
    height: 52px;
    display: inline-flex;
    align-items: center;
    justify-content: center;

    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #ffffff;
    border-radius: 50%;
    text-decoration: none;

    font-size: 0;
    position: relative;
    overflow: hidden;

    box-shadow: 0 12px 28px rgba(37, 99, 235, 0.35);
    transition: all 0.35s ease;
}

/* effet lumière */
.pagination-front a::after {
    content: "";
    position: absolute;
    top: 0;
    left: -80%;
    width: 60%;
    height: 100%;
    background: rgba(255, 255, 255, 0.35);
    transform: skewX(-25deg);
    transition: 0.6s;
}

.pagination-front a:hover::after {
    left: 130%;
}

/* icône précédent */
.pagination-front a:first-child::before {
    content: "\f104";
    font-family: "Font Awesome 6 Free";
    font-weight: 900;
    font-size: 20px;
}

/* icône suivant */
.pagination-front a:last-child::before {
    content: "\f105";
    font-family: "Font Awesome 6 Free";
    font-weight: 900;
    font-size: 20px;
}

/* animation hover */
.pagination-front a:hover {
    transform: translateY(-5px) scale(1.08);
    box-shadow: 0 18px 38px rgba(37, 99, 235, 0.5);
}

/* animation clic */
.pagination-front a:active {
    transform: scale(0.92);
}

/* cacher les numéros */
.pagination-front a:not(:first-child):not(:last-child) {
    display: none;
}













    </style>
</head>

<body>

<div class="dashboard-container">

    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="index.php" class="logo" style="text-decoration: none;">
                <i class="fa-solid fa-chart-pie text-primary"></i> Digit Advisory
            </a>
        </div>

        <div class="sidebar-menu">
            <a href="front-entreprise-dashboard.php" class="menu-item">
                <i class="fa-solid fa-house"></i> Vue d'ensemble
            </a>
            <a href="front-utilisateur.php" class="menu-item">
                <i class="fa-solid fa-building"></i> Profil Entreprise
            </a>
            <a href="front-quiz.php" class="menu-item active">
                <i class="fa-solid fa-list-check"></i> Questionnaire
            </a>
            <a href="front-portfolio.php" class="menu-item">
                <i class="fa-solid fa-folder-open"></i> Mon Portfolio
            </a>
            <a href="front-offres.php" class="menu-item">
                <i class="fa-solid fa-briefcase"></i> Mes Offres de Mission
            </a>
            <a href="front-certification.php" class="menu-item">
                <i class="fa-solid fa-award"></i> Certifications ISO
            </a>
            <a href="front-messagerie.php" class="menu-item">
                <i class="fa-solid fa-comments"></i> Messagerie
            </a>
        </div>

        <div class="user-profile-widget">
            <div class="user-avatar">TC</div>
            <div>
                <h4 style="font-size: 0.95rem; margin-bottom: 0.2rem;">TechCorp SAS</h4>
                <span style="font-size: 0.8rem; color: var(--gray);">Compte Entreprise</span>
            </div>
            <a href="login.php" style="margin-left: auto; color: var(--danger);">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
            </a>
        </div>
    </aside>

    <main class="main-content">

        <div class="top-navbar">
            <h2 style="margin: 0; font-size: 1.5rem;">Questionnaire</h2>
        </div>

        <div class="wrapper">
            <div class="header-box">
                <h2><?= htmlspecialchars($quiz['titre']) ?></h2>
                <p><?= htmlspecialchars($quiz['description']) ?></p>
            </div>

            <form action="front-quiz-result.php" method="POST">
                <input type="hidden" name="id_quiz" value="<?= $idQuiz ?>">

                <?php if (!empty($questions)) { ?>
                    <?php foreach ($questions as $index => $question) { ?>
                        <div class="question-box">
                            <h4>Question <?= $index + 1 ?> : <?= htmlspecialchars($question['question']) ?></h4>

                            <button type="button"
                                    class="voice-btn voice-question-btn"
                                    data-question="<?= htmlspecialchars($question['question'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-id="<?= (int)$question['id_question'] ?>">
                                Lire + Répondre
                            </button>

                            <label class="choice">
                                <input type="radio" name="reponse[<?= (int)$question['id_question'] ?>]" value="1">
                                <?= htmlspecialchars($question['choix1']) ?>
                            </label>

                            <label class="choice">
                                <input type="radio" name="reponse[<?= (int)$question['id_question'] ?>]" value="2">
                                <?= htmlspecialchars($question['choix2']) ?>
                            </label>

                            <label class="choice">
                                <input type="radio" name="reponse[<?= (int)$question['id_question'] ?>]" value="3">
                                <?= htmlspecialchars($question['choix3']) ?>
                            </label>

                            <label class="choice">
                                <input type="radio" name="reponse[<?= (int)$question['id_question'] ?>]" value="4">
                                <?= htmlspecialchars($question['choix4']) ?>
                            </label>
                        </div>
                    <?php } ?>

                    <div class="submit-box">
                        <button type="submit" class="btn btn-primary">Voir le résultat</button>
                    </div>
                <?php } else { ?>
                    <div class="question-box">
                        <p>Aucune question n’a encore été ajoutée à ce quiz.</p>
                    </div>
                <?php } ?>
            </form>
        </div>




    <div class="pagination-front">

    <?php if ($page > 1): ?>
        <a href="?id=<?= $idQuiz ?>&page=<?= $page - 1 ?>">←</a>
    <?php endif; ?>

    <?php if ($page < $totalPages): ?>
        <a href="?id=<?= $idQuiz ?>&page=<?= $page + 1 ?>">→</a>
    <?php endif; ?>

</div>
    </main>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".voice-question-btn").forEach(function (btn) {
        btn.addEventListener("click", function () {
            const questionText = this.getAttribute("data-question");
            const questionId = this.getAttribute("data-id");
            readAndAnswer(questionText, questionId);
        });
    });
});

let recognition = null;
let isListening = false;

function readAndAnswer(text, questionId) {
    stopVoice();

    const speech = new SpeechSynthesisUtterance(text);
    speech.lang = "fr-FR";
    speech.rate = 1;

    speech.onend = function () {
        setTimeout(function () {
            startVoiceAnswer(questionId);
        }, 300);
    };

    window.speechSynthesis.cancel();
    window.speechSynthesis.speak(speech);
}

function startVoiceAnswer(questionId) {
    stopVoice();

    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

    if (!SpeechRecognition) {
        alert("Votre navigateur ne supporte pas la reconnaissance vocale.");
        return;
    }

    recognition = new SpeechRecognition();
    recognition.lang = "fr-FR";
    recognition.interimResults = false;
    recognition.continuous = false;
    recognition.maxAlternatives = 1;

    isListening = true;

    recognition.onresult = function (event) {
        const text = event.results[0][0].transcript.toLowerCase().trim();

        if (text.includes("1") || text.includes("un")) {
            selectAnswer(questionId, 1);
        } else if (text.includes("2") || text.includes("deux")) {
            selectAnswer(questionId, 2);
        } else if (text.includes("3") || text.includes("trois")) {
            selectAnswer(questionId, 3);
        } else if (text.includes("4") || text.includes("quatre")) {
            selectAnswer(questionId, 4);
        } else {
            alert("Réponse non comprise. Dites : 1, 2, 3 ou 4.");
        }

        stopVoice();
    };

    recognition.onerror = function (event) {
        alert("Erreur micro : " + event.error);
        isListening = false;
    };

    recognition.onend = function () {
        isListening = false;
    };

    recognition.start();
}

function stopVoice() {
    if (window.speechSynthesis) {
        window.speechSynthesis.cancel();
    }

    if (recognition && isListening) {
        try {
            recognition.stop();
        } catch (e) {}
    }

    isListening = false;
}

function selectAnswer(questionId, value) {
    const input = document.querySelector(
        "input[name='reponse[" + questionId + "]'][value='" + value + "']"
    );

    if (input) {
        input.checked = true;
        input.dispatchEvent(new Event("change"));
    } else {
        alert("Réponse introuvable. Vérifie le name des boutons radio.");
    }
}
</script>

</body>
</html>