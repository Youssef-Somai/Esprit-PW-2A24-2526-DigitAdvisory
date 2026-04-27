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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($quiz['titre']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        body { background: #f1f5f9; font-family: 'Inter', sans-serif; }
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



    </style>
</head>
<body>
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
              🔊 Lire + Répondre
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

        console.log("Réponse vocale :", text);

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
        console.log("Erreur micro exacte :", event.error);

        if (event.error === "not-allowed") {
            alert("Micro refusé. Autorisez le micro dans le navigateur.");
        } else if (event.error === "no-speech") {
            alert("Aucune voix détectée. Parlez plus clairement.");
        } else if (event.error === "network") {
            alert("Problème réseau ou navigateur non compatible.");
        } else {
            alert("Erreur micro : " + event.error);
        }

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

    console.log("Question ID:", questionId);
    console.log("Réponse:", value);
    console.log("Input trouvé:", input);

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
