<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Face ID | Digit Advisory</title>
    <!-- Add face-api.js -->
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--light) 0%, #e0e7ff 100%);
            padding: 2rem;
            font-family: 'Inter', sans-serif;
        }
        .card {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            padding: 2rem;
            text-align: center;
            max-width: 600px;
            width: 100%;
        }
        #video-container {
            position: relative;
            margin: 1.5rem auto;
            width: 400px;
            height: 300px;
            background: #000;
            border-radius: 10px;
            overflow: hidden;
            display: none;
        }
        video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        canvas {
            position: absolute;
            top: 0;
            left: 0;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius);
            font-weight: 500;
            cursor: pointer;
            font-size: 1rem;
        }
        .btn:disabled {
            background: var(--gray);
            cursor: not-allowed;
        }
        #status {
            margin: 1rem 0;
            color: var(--gray);
            font-weight: 500;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <h2><i class="fa-solid fa-face-viewfinder"></i> Connexion avec Face ID</h2>
        <p>Scannez votre visage pour vous connecter.</p>

        <div id="status">Chargement des modèles AI, veuillez patienter...</div>
        
        <div id="video-container">
            <video id="video" autoplay muted></video>
        </div>

        <button id="scan-btn" class="btn" disabled>S'authentifier</button>
        <br><br>
        <a href="login.php" style="color: var(--gray); text-decoration: none;">Retour à la connexion classique</a>
    </div>
</div>

<script>
    const video = document.getElementById('video');
    const container = document.getElementById('video-container');
    const status = document.getElementById('status');
    const scanBtn = document.getElementById('scan-btn');

    Promise.all([
        faceapi.nets.ssdMobilenetv1.loadFromUri('/Esprit-PW-2A24-2526-DigitAdvisory/models/weights'),
        faceapi.nets.faceLandmark68Net.loadFromUri('/Esprit-PW-2A24-2526-DigitAdvisory/models/weights'),
        faceapi.nets.faceRecognitionNet.loadFromUri('/Esprit-PW-2A24-2526-DigitAdvisory/models/weights')
    ]).then(startVideo).catch(err => {
        console.error(err);
        status.textContent = "Erreur lors du chargement des modèles Face API.";
    });

    function startVideo() {
        status.textContent = "Accès à la caméra...";
        navigator.mediaDevices.getUserMedia(
            { video: {} }
        ).then(stream => {
            video.srcObject = stream;
            container.style.display = 'block';
            status.textContent = "Veuillez regarder la caméra et cliquer sur s'authentifier.";
            scanBtn.disabled = false;
        }).catch(err => {
            console.error(err);
            status.textContent = "Impossible d'accéder à la caméra.";
        });
    }

    scanBtn.addEventListener('click', async () => {
        status.textContent = "Analyse du visage en cours...";
        scanBtn.disabled = true;

        const detections = await faceapi.detectSingleFace(video).withFaceLandmarks().withFaceDescriptor();

        if (!detections) {
            status.textContent = "Aucun visage détecté. Veuillez réessayer.";
            scanBtn.disabled = false;
            return;
        }

        status.textContent = "Visage détecté ! Vérification de l'identité...";

        const descriptor = Array.from(detections.descriptor);

        fetch('../traitement/loginFaceIdTraitement.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ descriptor: descriptor })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                status.innerHTML = `<span style='color: green;'>Authentification réussie, bienvenue ${data.nom} !</span>`;
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1500);
            } else {
                status.textContent = "Erreur : " + data.message;
                scanBtn.disabled = false;
            }
        })
        .catch(err => {
            console.error(err);
            status.textContent = "Erreur serveur.";
            scanBtn.disabled = false;
        });
    });
</script>
</body>
</html>
