<?php
$score = $_GET['score'] ?? 0;
$niveau = $_GET['niveau'] ?? '';
$message = $_GET['message'] ?? '';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="color-scheme" content="light">
<title>Résultat du Quiz</title>

<style>
@page {
    margin: 20mm;
}

body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #f1f5f9;
    padding: 40px;
    color: #0f172a;
}

.result-card {
    max-width: 800px;
    margin: auto;
    background: white;
    border-radius: 22px;
    padding: 45px;
    text-align: center;
    box-shadow: 0 15px 40px rgba(15, 23, 42, 0.10);
    border-top: 6px solid #2563eb;
}

.logo-box {
    width: 70px;
    height: 70px;
    margin: 0 auto 15px;
    border-radius: 20px;
    background: linear-gradient(135deg, #2563eb, #60a5fa);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
}

h1 {
    color: #1e293b;
    margin-bottom: 8px;
    font-size: 30px;
}

.subtitle {
    color: #64748b;
    margin-bottom: 25px;
}

.score {
    width: 165px;
    height: 165px;
    border-radius: 50%;
    margin: 25px auto;
    background: linear-gradient(135deg, #2563eb, #60a5fa);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 38px;
    font-weight: bold;
    box-shadow: 0 12px 25px rgba(37, 99, 235, 0.30);
}

.badge {
    display: inline-block;
    padding: 11px 22px;
    background: #eff6ff;
    color: #2563eb;
    border-radius: 999px;
    font-weight: bold;
    margin: 10px 0 18px;
}

.message {
    font-size: 16px;
    color: #334155;
    line-height: 1.7;
    max-width: 650px;
    margin: 0 auto 25px;
}

.info-box {
    margin-top: 25px;
    padding: 18px;
    border-radius: 16px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #475569;
}

.btn-print {
    margin-top: 30px;
    padding: 13px 22px;
    background: linear-gradient(135deg, #2563eb, #60a5fa);
    color: white;
    border-radius: 999px;
    border: none;
    cursor: pointer;
    font-weight: bold;
    font-size: 15px;
}

.btn-print:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 18px rgba(37, 99, 235, 0.25);
}

@media print {
    body {
        background: white;
        padding: 0;
    }

    .result-card {
        box-shadow: none;
        border-radius: 0;
        width: 100%;
        max-width: 100%;
        padding: 20px;
        border-top: 6px solid #2563eb;
    }

    .btn-print {
        display: none;
    }

    .score,
    .logo-box,
    .badge {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    h1 {
        font-size: 26px;
    }

    .message {
        font-size: 14px;
    }
}
.btn-print {
    margin-top: 30px;
    padding: 14px 26px;
    background: linear-gradient(135deg, #2563eb, #60a5fa);
    color: white;
    border-radius: 999px;
    border: none;
    cursor: pointer;
    font-weight: bold;
    font-size: 15px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(37, 99, 235, 0.28);
    transition: all 0.3s ease;
}

.btn-print::before {
    content: "";
    position: absolute;
    top: 0;
    left: -80%;
    width: 55%;
    height: 100%;
    background: rgba(255, 255, 255, 0.35);
    transform: skewX(-25deg);
    transition: 0.6s;
}

.btn-print:hover::before {
    left: 130%;
}

.btn-print:hover {
    transform: translateY(-3px) scale(1.04);
    box-shadow: 0 16px 35px rgba(37, 99, 235, 0.42);
}

.btn-print:active {
    transform: scale(0.95);
}

.btn-icon {
    display: inline-flex;
    animation: downloadMove 1.2s infinite ease-in-out;
}

@keyframes downloadMove {
    0%, 100% {
        transform: translateY(-2px);
    }
    50% {
        transform: translateY(3px);
    }
}
</style>
</head>

<body>

<div class="result-card">

    <div class="logo-box">✓</div>

    <h1>Résultat du questionnaire</h1>
    <p class="subtitle">Rapport de maturité numérique</p>

    <div class="score">
        <?= htmlspecialchars($score) ?>%
    </div>

    <div class="badge">
        Niveau : <?= htmlspecialchars($niveau) ?>
    </div>

    <p class="message">
        <?= htmlspecialchars($message) ?>
    </p>

    <div class="info-box">
        Ce document peut être imprimé ou enregistré en PDF depuis votre navigateur.
    </div>

    
      <button class="btn-print" onclick="window.print()">
    <span class="btn-icon">⬇</span>
    Télécharger le rapport
</button>
    </button>

</div>

</body>
</html>