<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/CritereController.php';

class GenerateTemplateController
{
    private CritereController $critereController;

    public function __construct()
    {
        $this->critereController = new CritereController();
    }

    public function generateForCritere(int $critereId): array
    {
        $critere = $this->critereController->getCritere($critereId);
        if (!$critere) {
            return ['success' => false, 'message' => 'Critere introuvable.'];
        }

        $apiKey = config::getOpenAIKey();
        if ($apiKey === null) {
            return ['success' => false, 'message' => 'Cle OpenAI absente. Configurez OPENAI_API_KEY dans config.local.php ou dans les variables d environnement.'];
        }

        $structuredDocument = $this->requestTemplateFromOpenAI($critere, $apiKey);
        if (!$structuredDocument['success']) {
            return $structuredDocument;
        }

        $relativePath = $this->saveTemplateAsHtml($critere->getNom(), $structuredDocument['document']);
        $this->critereController->updateCritereTemplate($critereId, $relativePath);

        return [
            'success' => true,
            'message' => 'Modele genere avec succes.',
            'path' => $relativePath,
        ];
    }

    public function generateForCritereAjax(int $critereId): array
    {
        $critere = $this->critereController->getCritere($critereId);
        if (!$critere) {
            return ['success' => false, 'message' => 'Critere introuvable.'];
        }

        $apiKey = config::getOpenAIKey();
        if ($apiKey === null) {
            return ['success' => false, 'message' => 'Cle OpenAI absente. Configurez OPENAI_API_KEY dans config.local.php.'];
        }

        $structuredDocument = $this->requestTemplateFromOpenAI($critere, $apiKey);
        if (!$structuredDocument['success']) {
            return $structuredDocument;
        }

        $htmlContent = $this->renderHtmlTemplate($structuredDocument['document']);

        return [
            'success' => true,
            'html' => $htmlContent,
            'critereName' => $critere->getNom()
        ];
    }

    public function generateRoadmapAjax(string $certTitre, array $missingCriteres): array
    {
        $apiKey = config::getOpenAIKey();
        if ($apiKey === null) {
            return ['success' => false, 'message' => 'Clé OpenAI absente.'];
        }

        if (empty($missingCriteres)) {
            $html = "<h1>Plan d'Action - $certTitre</h1><p>Félicitations, vous répondez déjà à tous les critères !</p>";
            return ['success' => true, 'html' => $html, 'certTitre' => $certTitre];
        }

        $prompt = "Tu es un auditeur ISO expert. L'entreprise souhaite obtenir la certification : $certTitre.\n";
        $prompt .= "Il lui manque actuellement ces critères d'évaluation :\n";
        foreach ($missingCriteres as $crit) {
            $prompt .= "- " . $crit . "\n";
        }
        $prompt .= "\nGénère un plan d'action structuré (Roadmap) pour aider l'entreprise à se conformer à ces critères manquants.\n";
        $prompt .= "Formatte ta réponse EXCLUSIVEMENT en HTML valide, avec des balises <h1>, <h2>, <ul>, <li>, <p>, <strong>. Ne mets ni de block markdown ```html ni de doctype, commence directement par <h1>Plan d'Action...</h1>.";

        $postData = [
            'model' => 'gpt-4o-mini',
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'temperature' => 0.7,
            'max_tokens' => 1500
        ];

        $ch = curl_init("https://api.openai.com/v1/chat/completions");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 45);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return ['success' => false, 'message' => 'Erreur cURL : ' . $err];
        }

        $responseData = json_decode($response, true);
        if ($httpCode === 200 && isset($responseData['choices'][0]['message']['content'])) {
            $htmlContent = $responseData['choices'][0]['message']['content'];
            // Nettoyage markdown éventuel
            $htmlContent = preg_replace('/```html\n?/', '', $htmlContent);
            $htmlContent = preg_replace('/```/', '', $htmlContent);
            return ['success' => true, 'html' => trim($htmlContent), 'certTitre' => $certTitre];
        }

        return ['success' => false, 'message' => 'Erreur API OpenAI.'];
    }

    public function saveTemplateAjax(int $critereId, array $file = []): array
    {
        $critere = $this->critereController->getCritere($critereId);
        if (!$critere) {
            return ['success' => false, 'message' => 'Critere introuvable.'];
        }

        if (empty($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Aucun fichier valide reçu.'];
        }

        $uploadDir = __DIR__ . '/../uploads/templates/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $slug = $this->slugify($critere->getNom());
        $fileName = $slug . '-' . date('Ymd-His') . '.docx';
        $absolutePath = $uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
            return ['success' => false, 'message' => 'Erreur lors de l enregistrement du fichier sur le serveur.'];
        }

        $relativePath = '../../uploads/templates/' . $fileName;

        $this->critereController->updateCritereTemplate($critereId, $relativePath);

        return [
            'success' => true,
            'message' => 'Document DOCX enregistré avec succès !',
            'path' => $relativePath
        ];
    }

    private function requestTemplateFromOpenAI(Critere $critere, string $apiKey): array
    {
        $payload = [
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => "Tu generes des modeles de documents ISO professionnels en francais. Le document doit etre concret, reutilisable, clair, et adapte a un audit ou une preparation d audit. Tu dois obligatoirement respecter le format JSON demande.",
                ],
                [
                    'role' => 'user',
                    'content' => $this->buildUserPrompt($critere),
                ],
            ],
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'iso_document_template',
                    'strict' => true,
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'title' => ['type' => 'string'],
                            'objective' => ['type' => 'string'],
                            'usage_notes' => ['type' => 'string'],
                            'sections' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'heading' => ['type' => 'string'],
                                        'content' => ['type' => 'string'],
                                    ],
                                    'required' => ['heading', 'content'],
                                    'additionalProperties' => false,
                                ],
                            ],
                            'checklist' => [
                                'type' => 'array',
                                'items' => ['type' => 'string'],
                            ],
                        ],
                        'required' => ['title', 'objective', 'usage_notes', 'sections', 'checklist'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['success' => false, 'message' => 'Erreur reseau OpenAI: ' . $curlError];
        }

        $responseData = json_decode($response, true);
        if (!is_array($responseData)) {
            return ['success' => false, 'message' => 'Reponse OpenAI invalide.'];
        }

        if ($httpCode >= 400) {
            $apiMessage = $responseData['error']['message'] ?? ('HTTP ' . $httpCode);
            return ['success' => false, 'message' => 'OpenAI a retourne une erreur: ' . $apiMessage];
        }

        $jsonText = $this->extractOutputText($responseData);
        if ($jsonText === null) {
            return ['success' => false, 'message' => 'Impossible de lire le contenu genere par OpenAI.'];
        }

        $document = json_decode($jsonText, true);
        if (!is_array($document)) {
            return ['success' => false, 'message' => 'Le contenu genere n est pas un JSON exploitable.'];
        }

        return ['success' => true, 'document' => $document];
    }

    private function buildUserPrompt(Critere $critere): string
    {
        $obligatoire = $critere->getEstObligatoire() ? 'Oui' : 'Non';

        return "Genere un modele de document ISO pret a etre telecharge et adapte par une entreprise.\n"
            . "Critere: " . $critere->getNom() . "\n"
            . "Categorie: " . $critere->getCategorie() . "\n"
            . "Description: " . ($critere->getDescription() ?? 'Aucune description complementaire.') . "\n"
            . "Preuve attendue: " . ($critere->getMoyenPreuve() ?? 'Aucune preuve precisee.') . "\n"
            . "Difficulte: " . $critere->getDifficulte() . "\n"
            . "Obligatoire: " . $obligatoire . "\n"
            . "Contraintes:\n"
            . "- Redige en francais\n"
            . "- Style professionnel et operationnel\n"
            . "- Pas de blabla marketing\n"
            . "- Sections reutilisables par une entreprise reelle\n"
            . "- Le contenu doit pouvoir servir de base immediate a un document interne";
    }

    private function extractOutputText(array $responseData): ?string
    {
        if (isset($responseData['choices'][0]['message']['content'])) {
            return $responseData['choices'][0]['message']['content'];
        }

        return null;
    }

    private function saveTemplateAsHtml(string $critereName, array $document): string
    {
        $uploadDir = __DIR__ . '/../uploads/templates/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $slug = $this->slugify($critereName);
        $fileName = $slug . '-' . date('Ymd-His') . '.html';
        $absolutePath = $uploadDir . $fileName;

        file_put_contents($absolutePath, $this->renderHtmlTemplate($document));

        return '../../uploads/templates/' . $fileName;
    }

    private function renderHtmlTemplate(array $document): string
    {
        $title = htmlspecialchars((string) ($document['title'] ?? 'Modele ISO'), ENT_QUOTES, 'UTF-8');
        $objective = nl2br(htmlspecialchars((string) ($document['objective'] ?? ''), ENT_QUOTES, 'UTF-8'));
        $usageNotes = nl2br(htmlspecialchars((string) ($document['usage_notes'] ?? ''), ENT_QUOTES, 'UTF-8'));

        $sectionsHtml = '';
        foreach (($document['sections'] ?? []) as $section) {
            $heading = htmlspecialchars((string) ($section['heading'] ?? ''), ENT_QUOTES, 'UTF-8');
            $content = nl2br(htmlspecialchars((string) ($section['content'] ?? ''), ENT_QUOTES, 'UTF-8'));
            $sectionsHtml .= "<section><h2>{$heading}</h2><p>{$content}</p></section>";
        }

        $checklistItems = '';
        foreach (($document['checklist'] ?? []) as $item) {
            $checklistItems .= '<li>' . htmlspecialchars((string) $item, ENT_QUOTES, 'UTF-8') . '</li>';
        }

        return '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $title . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; color: #1f2937; line-height: 1.6; }
        header { border-bottom: 2px solid #2563eb; margin-bottom: 24px; padding-bottom: 16px; }
        h1 { margin: 0 0 8px; color: #0f172a; }
        h2 { margin-top: 24px; color: #1d4ed8; }
        .card { background: #f8fafc; border: 1px solid #dbeafe; border-radius: 12px; padding: 16px; margin: 20px 0; }
        ul { padding-left: 20px; }
        li { margin-bottom: 8px; }
    </style>
</head>
<body>
    <header>
        <h1>' . $title . '</h1>
        <p><strong>Objectif :</strong><br>' . $objective . '</p>
    </header>
    <div class="card">
        <strong>Conseils d utilisation</strong><br>' . $usageNotes . '
    </div>
    ' . $sectionsHtml . '
    <section>
        <h2>Checklist de finalisation</h2>
        <ul>' . $checklistItems . '</ul>
    </section>
</body>
</html>';
    }

    private function slugify(string $value): string
    {
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($slug === false) {
            $slug = $value;
        }

        $slug = strtolower($slug);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim((string) $slug, '-');

        return $slug !== '' ? $slug : 'modele-iso';
    }
}

if (basename($_SERVER['PHP_SELF']) === 'GenerateTemplateController.php') {
    $generator = new GenerateTemplateController();
    $action = $_POST['action'] ?? ($_GET['action'] ?? '');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($action === 'generate_critere_template') {
            $critereId = isset($_POST['id']) ? (int) $_POST['id'] : 0;
            $result = $generator->generateForCritere($critereId);

            if ($result['success']) {
                header('Location: ../View/BackOffice/back-certification.php?success=generate_template&tab=criteres');
                exit;
            }

            $errorMessage = urlencode($result['message']);
            header('Location: ../View/BackOffice/back-certification.php?error=' . $errorMessage . '&tab=criteres');
            exit;
        }

        if ($action === 'ajax_generate_template') {
            header('Content-Type: application/json');
            $critereId = isset($_POST['id']) ? (int) $_POST['id'] : 0;
            $result = $generator->generateForCritereAjax($critereId);
            echo json_encode($result);
            exit;
        }

        if ($action === 'ajax_save_template') {
            header('Content-Type: application/json');
            $critereId = isset($_POST['id']) ? (int) $_POST['id'] : 0;
            $file = $_FILES['template_file'] ?? [];
            $result = $generator->saveTemplateAjax($critereId, $file);
            echo json_encode($result);
            exit;
        }

        if ($action === 'ajax_generate_roadmap') {
            header('Content-Type: application/json');
            $missingCriteres = isset($_POST['missing_criteres']) ? json_decode($_POST['missing_criteres'], true) : [];
            $certTitre = $_POST['cert_titre'] ?? 'Certification';
            $result = $generator->generateRoadmapAjax($certTitre, $missingCriteres);
            echo json_encode($result);
            exit;
        }
    }

    header('Location: ../View/BackOffice/back-certification.php?tab=criteres');
    exit;
}
