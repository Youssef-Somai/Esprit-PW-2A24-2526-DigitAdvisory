<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurer Face ID | Digit Advisory</title>
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
        <h2><i class="fa-solid fa-face-smile"></i> Configuration Face ID</h2>
        <p>Enregistrez votre visage pour vous connecter sans mot de passe.</p>

        <div id="status">Chargement des modèles AI, veuillez patienter...</div>
        
        <div id="video-container">
            <video id="video" autoplay muted></video>
        </div>

        <button id="capture-btn" class="btn" disabled>Capturer mon visage</button>
        <br><br>
        <a href="javascript:history.back()" style="color: var(--gray); text-decoration: none;">Retour</a>
    </div>
</div>

<script>
    const video = document.getElementById('video');
    const container = document.getElementById('video-container');
    const status = document.getElementById('status');
    const captureBtn = document.getElementById('capture-btn');

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
            status.textContent = "Veuillez regarder la caméra et cliquer sur capturer.";
            captureBtn.disabled = false;
        }).catch(err => {
            console.error(err);
            status.textContent = "Impossible d'accéder à la caméra.";
        });
    }

    captureBtn.addEventListener('click', async () => {
        status.textContent = "Analyse en cours...";
        captureBtn.disabled = true;

        const detections = await faceapi.detectSingleFace(video).withFaceLandmarks().withFaceDescriptor();

        if (!detections) {
            status.textContent = "Aucun visage détecté. Veuillez réessayer.";
            captureBtn.disabled = false;
            return;
        }

        status.textContent = "Visage détecté ! Enregistrement...";

        const descriptor = Array.from(detections.descriptor);

        fetch('../traitement/setup_face_idTraitement.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ descriptor: descriptor })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                status.innerHTML = "<span style='color: green;'>Face ID enregistré avec succès !</span>";
                setTimeout(() => {
                    window.location.href = '../FrontOffice/login.php';
                }, 2000);
            } else {
                status.textContent = "Erreur : " + data.message;
                captureBtn.disabled = false;
            }
        })
        .catch(err => {
            console.error(err);
            status.textContent = "Erreur serveur.";
            captureBtn.disabled = false;
        });
    });
</script>
</body>
</html>
