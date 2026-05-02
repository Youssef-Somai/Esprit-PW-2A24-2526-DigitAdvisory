<?php
require_once __DIR__ . '/../../config.php';

if (!isset($_POST['id_quiz']) || !isset($_POST['reponse'])) {
    die('Données invalides');
}

$idQuiz = (int) $_POST['id_quiz'];
$reponsesUtilisateur = $_POST['reponse'];

$db = config::getConnexion();

$sqlQuiz = "SELECT * FROM quiz WHERE id_quiz = :id";
$queryQuiz = $db->prepare($sqlQuiz);
$queryQuiz->execute(['id' => $idQuiz]);
$quiz = $queryQuiz->fetch(PDO::FETCH_ASSOC);

$sqlQuestions = "SELECT * FROM question WHERE id_quiz = :id";
$queryQuestions = $db->prepare($sqlQuestions);
$queryQuestions->execute(['id' => $idQuiz]);
$questions = $queryQuestions->fetchAll(PDO::FETCH_ASSOC);

$total = count($questions);
$bonnes = 0;

foreach ($questions as $question) {
    $idQuestion = $question['id_question'];
    $bonneReponse = (int) $question['bonne_reponse'];

    if (isset($reponsesUtilisateur[$idQuestion]) && (int)$reponsesUtilisateur[$idQuestion] === $bonneReponse) {
        $bonnes++;
    }
}

$score = $total > 0 ? round(($bonnes / $total) * 100) : 0;

$niveau = '';
$message = '';

if ($score <= 25) {
    $niveau = 'Faible';
    $message = "Votre entreprise a un niveau de digitalisation faible.";
} elseif ($score <= 50) {
    $niveau = 'Basique';
    $message = "Votre entreprise a commencé sa transformation digitale, mais il reste plusieurs améliorations à faire.";
} elseif ($score <= 75) {
    $niveau = 'Intermédiaire';
    $message = "Votre entreprise présente un bon niveau de digitalisation avec encore quelques axes de progression.";
} else {
    $niveau = 'Avancé';
    $message = "Votre entreprise est bien digitalisée et présente une forte maturité numérique.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultat du Quiz</title>
    <link rel="stylesheet" href="../../css/style.css">

    <style>
        body {
            background: #f1f5f9;
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
        }

        .result-wrapper {
            max-width: 700px;
            margin: 3rem auto;
            background: white;
            border-radius: 22px;
            padding: 2rem;
            box-shadow: var(--shadow-md);
            text-align: center;
            position: relative;
            z-index: 5;
            animation: cardEnter 0.9s ease;
        }

        .score-circle {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            margin: 1.5rem auto;
            background: linear-gradient(135deg, #2563eb, #60a5fa);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 700;
            animation: scorePop 1s ease, scoreGlow 2s infinite alternate;
        }

        .level-badge {
            display: inline-block;
            padding: .7rem 1.2rem;
            background: #eff6ff;
            color: #2563eb;
            border-radius: 999px;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .congrats {
            color: #f59e0b;
            font-size: 2rem;
            margin-top: 1.5rem;
            animation: congratsPop .8s ease, congratsGlow 1.5s infinite alternate, congratsFloat 2s infinite ease-in-out;
        }

        .icons-party {
            font-size: 35px;
            animation: iconsDance 1.5s infinite ease-in-out;
        }

        .email-form {
            margin-top: 2rem;
            padding: 1.3rem;
            position: relative;
            overflow: hidden;
            border-radius: 24px;
            background: linear-gradient(135deg, #ffffff, #eff6ff);
            box-shadow: 0 18px 45px rgba(37, 99, 235, .18);
            animation: emailShow 1s ease, emailPulse 3s infinite ease-in-out;
        }

        .email-form::before {
            content: "";
            position: absolute;
            inset: -3px;
            background: linear-gradient(90deg, #2563eb, #60a5fa, #10b981, #f59e0b, #2563eb);
            background-size: 300%;
            animation: borderMove 4s linear infinite;
            z-index: 0;
        }

        .email-form::after {
            content: "";
            position: absolute;
            inset: 3px;
            background: #f8fafc;
            border-radius: 21px;
            z-index: 1;
        }

        .email-form h3,
        .email-form input,
        .email-form button,
        .email-error,
        .email-form br {
            position: relative;
            z-index: 2;
        }

        .email-form h3::before {
            content: "📧 ";
        }

        .email-form h3::after {
            content: " ✨";
        }

        .email-form input {
            width: 80%;
            padding: .9rem;
            border-radius: 999px;
            border: 1px solid #cbd5e1;
            text-align: center;
            margin-bottom: .5rem;
            outline: none;
            transition: .3s;
        }

        .email-form input:focus {
            transform: scale(1.04);
            border-color: #2563eb;
            box-shadow: 0 0 0 5px rgba(37, 99, 235, .18);
        }

        .email-form input.valid {
            border-color: #10b981;
            box-shadow: 0 0 0 5px rgba(16, 185, 129, .15);
        }

        .email-form input.invalid {
            border-color: #ef4444;
            box-shadow: 0 0 0 5px rgba(239, 68, 68, .15);
            animation: shake .35s ease;
        }

        .email-error {
            display: none;
            color: #ef4444;
            font-size: .9rem;
            margin-bottom: 1rem;
        }

        .btn-send {
            padding: .9rem 1.4rem;
            border-radius: 999px;
            border: none;
            background: linear-gradient(135deg, #10b981, #34d399);
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: .3s;
            box-shadow: 0 10px 25px rgba(16, 185, 129, .25);
        }

        .btn-send:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 16px 35px rgba(16, 185, 129, .38);
        }

        .balloon {
            position: fixed;
            bottom: -140px;
            width: 55px;
            height: 75px;
            border-radius: 50%;
            z-index: 2;
            animation: floatUp 8s linear infinite;
        }

        .balloon::after {
            content: "";
            position: absolute;
            width: 2px;
            height: 55px;
            background: #475569;
            top: 75px;
            left: 50%;
        }

        .b1 { left: 8%; background: #ef4444; animation-delay: 0s; }
        .b2 { left: 25%; background: #3b82f6; animation-delay: 1s; }
        .b3 { left: 50%; background: #22c55e; animation-delay: 2s; }
        .b4 { left: 70%; background: #f59e0b; animation-delay: .5s; }
        .b5 { left: 88%; background: #a855f7; animation-delay: 1.5s; }

        .party-light {
            position: fixed;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            pointer-events: none;
            z-index: 1;
            animation: lightAnim 2s ease-out forwards;
            box-shadow: 0 0 15px currentColor, 0 0 35px currentColor;
        }

        .confetti-piece {
            position: fixed;
            top: -20px;
            width: 10px;
            height: 16px;
            pointer-events: none;
            z-index: 3;
            animation: confettiFall 4s linear forwards;
        }

        .explosion-piece {
            position: fixed;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            pointer-events: none;
            z-index: 10;
            animation: explosionMove 1.4s ease-out forwards;
            box-shadow: 0 0 18px currentColor;
        }

        @keyframes cardEnter {
            from { opacity: 0; transform: translateY(35px) scale(.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes scorePop {
            from { transform: scale(.4); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        @keyframes scoreGlow {
            from { box-shadow: 0 12px 25px rgba(37, 99, 235, .30); }
            to { box-shadow: 0 18px 45px rgba(37, 99, 235, .55); }
        }

        @keyframes congratsPop {
            from { opacity: 0; transform: scale(.3) rotate(-10deg); }
            to { opacity: 1; transform: scale(1) rotate(0); }
        }

        @keyframes congratsGlow {
            from { text-shadow: 0 0 5px #facc15; }
            to { text-shadow: 0 0 30px #f59e0b, 0 0 50px #f97316; }
        }

        @keyframes congratsFloat {
            0%,100% { transform: translateY(0); }
            50% { transform: translateY(-7px); }
        }

        @keyframes iconsDance {
            0%,100% { transform: scale(1) rotate(0); }
            50% { transform: scale(1.15) rotate(5deg); }
        }

        @keyframes emailShow {
            from { opacity: 0; transform: translateY(35px) scale(.94); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes emailPulse {
            0%,100% { box-shadow: 0 18px 45px rgba(37, 99, 235, .18); }
            50% { box-shadow: 0 22px 60px rgba(16, 185, 129, .25); }
        }

        @keyframes borderMove {
            from { background-position: 0%; }
            to { background-position: 300%; }
        }

        @keyframes floatUp {
            from { transform: translateY(0) rotate(0deg); opacity: 1; }
            to { transform: translateY(-120vh) rotate(18deg); opacity: 0; }
        }

        @keyframes lightAnim {
            0% { opacity: 0; transform: scale(0); }
            30% { opacity: 1; transform: scale(1.5); }
            100% { opacity: 0; transform: scale(.3) translateY(-90px); }
        }

        @keyframes confettiFall {
            0% { transform: translateY(0) rotate(0deg); opacity: 1; }
            100% { transform: translateY(110vh) rotate(720deg); opacity: 0; }
        }

        @keyframes explosionMove {
            0% {
                opacity: 1;
                transform: translate(0, 0) scale(1);
            }
            100% {
                opacity: 0;
                transform: translate(var(--x), var(--y)) scale(.2);
            }
        }

        @keyframes shake {
            0%,100% { transform: translateX(0); }
            25% { transform: translateX(-6px); }
            50% { transform: translateX(6px); }
            75% { transform: translateX(-4px); }
        }
    </style>
</head>

<body>

    <div class="balloon b1"></div>
    <div class="balloon b2"></div>
    <div class="balloon b3"></div>
    <div class="balloon b4"></div>
    <div class="balloon b5"></div>

    <audio id="felicitationSound">
        <source src="../../felicitation.mp3" type="audio/mpeg">
    </audio>

    <div class="result-wrapper">
        <h2>Résultat du questionnaire</h2>
        <?php if ($quiz) { ?>
            <p><?= htmlspecialchars($quiz['titre']) ?></p>
        <?php } ?>

        <div class="score-circle"><?= $score ?>%</div>
        <div class="level-badge">Niveau : <?= htmlspecialchars($niveau) ?></div>
        <p><?= htmlspecialchars($message) ?></p>

        <h2 class="congrats">🎉 Félicitations 🎊</h2>
        <p class="icons-party">🏆 ⭐ 🎈 ✨ 🥳</p>

        <form action="send-result-email.php" method="POST" class="email-form" id="emailForm">
            <h3>Recevoir le résultat par email</h3>

            <input type="hidden" name="score" value="<?= htmlspecialchars($score) ?>">
            <input type="hidden" name="niveau" value="<?= htmlspecialchars($niveau) ?>">
            <input type="hidden" name="message" value="<?= htmlspecialchars($message) ?>">

            <input 
                type="email" 
                name="email" 
                id="emailInput" 
                placeholder="Entrez votre email" 
             
            >

            <div class="email-error" id="emailError">
                Veuillez saisir une adresse email valide.
            </div>

            <br>

            <button type="submit" class="btn-send">
                Envoyer par email
            </button>
        </form>

        <div style="margin-top: 2rem;">
            <a href="front-quiz.php" class="btn btn-secondary">Retour aux quiz</a>
            <a href="front-quiz-detail.php?id=<?= $idQuiz ?>" class="btn btn-primary">Refaire le quiz</a>
        </div>
    </div>

    <script>
        function randomColor() {
            const colors = ["#ef4444", "#3b82f6", "#22c55e", "#f59e0b", "#a855f7", "#ec4899", "#14b8a6"];
            return colors[Math.floor(Math.random() * colors.length)];
        }

        function createLight() {
            const light = document.createElement("div");
            light.classList.add("party-light");

            const color = randomColor();
            light.style.background = color;
            light.style.color = color;

            light.style.left = Math.random() * window.innerWidth + "px";
            light.style.top = Math.random() * window.innerHeight + "px";

            document.body.appendChild(light);

            setTimeout(function () {
                light.remove();
            }, 2000);
        }

        function createConfetti() {
            const confetti = document.createElement("div");
            confetti.classList.add("confetti-piece");

            confetti.style.background = randomColor();
            confetti.style.left = Math.random() * window.innerWidth + "px";
            confetti.style.animationDuration = (Math.random() * 2 + 3) + "s";

            document.body.appendChild(confetti);

            setTimeout(function () {
                confetti.remove();
            }, 5000);
        }

        function createExplosion() {
            const centerX = window.innerWidth / 2;
            const centerY = window.innerHeight / 2;

            for (let i = 0; i < 80; i++) {
                const piece = document.createElement("div");
                piece.classList.add("explosion-piece");

                const angle = Math.random() * Math.PI * 2;
                const distance = Math.random() * 320 + 80;

                const x = Math.cos(angle) * distance + "px";
                const y = Math.sin(angle) * distance + "px";

                const color = randomColor();

                piece.style.left = centerX + "px";
                piece.style.top = centerY + "px";
                piece.style.background = color;
                piece.style.color = color;
                piece.style.setProperty("--x", x);
                piece.style.setProperty("--y", y);

                document.body.appendChild(piece);

                setTimeout(function () {
                    piece.remove();
                }, 1500);
            }
        }

        window.addEventListener("load", function () {
            createExplosion();

            setInterval(createLight, 250);
            setInterval(createConfetti, 120);

            const sound = document.getElementById("felicitationSound");
        if (sound) {
    document.addEventListener("click", function playSoundOnce() {
        sound.play();
        document.removeEventListener("click", playSoundOnce);
    });
}

        });

        const emailForm = document.getElementById("emailForm");
        const emailInput = document.getElementById("emailInput");
        const emailError = document.getElementById("emailError");

        function validateEmail(email) {
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return regex.test(email);
        }

        emailInput.addEventListener("input", function () {
            const emailValue = emailInput.value.trim();

            if (emailValue === "") {
                emailInput.classList.remove("valid", "invalid");
                emailError.style.display = "none";
                return;
            }

            if (validateEmail(emailValue)) {
                emailInput.classList.add("valid");
                emailInput.classList.remove("invalid");
                emailError.style.display = "none";
            } else {
                emailInput.classList.add("invalid");
                emailInput.classList.remove("valid");
                emailError.style.display = "block";
            }
        });

        emailForm.addEventListener("submit", function (event) {
            const emailValue = emailInput.value.trim();

            if (!validateEmail(emailValue)) {
                event.preventDefault();
                emailInput.classList.add("invalid");
                emailInput.classList.remove("valid");
                emailError.style.display = "block";
                emailInput.focus();
            }
        });



    emailForm.addEventListener("submit", function (event) {
    const emailValue = emailInput.value.trim();

    if (!validateEmail(emailValue)) {
        event.preventDefault();
        emailInput.classList.add("invalid");
        emailInput.classList.remove("valid");
        emailError.style.display = "block";
        emailInput.focus();
    }
});
    </script>

</body>
</html>