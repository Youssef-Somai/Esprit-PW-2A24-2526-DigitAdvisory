<?php
/**
 * Controller : ChatbotController
 * Gère les requêtes vers l'API OpenAI pour le chatbot dynamique DigitBot.
 * - Mode standard       : assistant ISO + questions générales
 * - Mode recommendation : diagnostic consulting (JSON structuré)
 * - Mode action         : le bot peut déclencher des actions UI via JSON
 */

// ─── CORS : restreindre à localhost en développement ───
$allowedOrigins = ['http://localhost', 'http://127.0.0.1', 'http://localhost:8080'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins) || empty($origin)) {
    header('Access-Control-Allow-Origin: ' . ($origin ?: 'http://localhost'));
} else {
    header('Access-Control-Allow-Origin: http://localhost');
}
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ─── CLÉ API OPENAI ───
require_once __DIR__ . '/../config.php';
$openai_API_Key = config::getOpenAIKey();
if ($openai_API_Key === null) {
    echo json_encode(['reply' => '⚠️ Clé API OpenAI non configurée. Veuillez configurer OPENAI_API_KEY dans config.local.php.']);
    exit;
}

require_once __DIR__ . '/CertificatController.php';
require_once __DIR__ . '/CritereController.php';

$certifController = new CertificatController();
$critereController = new CritereController();

$certificats = $certifController->listCertificats();
$criteres = $critereController->listCriteres();

$nbCertifs   = count($certificats);
$nbCriteres  = count($criteres);
$nbActives   = count(array_filter($certificats, fn($c) => $c->getStatut() === 'Actif'));
$nomsCertifs = implode(', ', array_map(fn($c) => $c->getNorme(), $certificats));

$contextData = "ÉTAT ACTUEL DE LA BASE DE DONNÉES DIGITADVISORY :\n"
    . "- $nbCertifs certifications ISO enregistrées (dont $nbActives au statut 'Actif').\n"
    . "- Normes enregistrées : $nomsCertifs.\n"
    . "- $nbCriteres critères d'audit enregistrés globalement.\n\n";

// ─── Lecture de l'input ───
$input = file_get_contents('php://input');
$data  = json_decode($input, true);

$userMessage       = trim($data['message']        ?? '');
$clientContext     = $data['clientContext']        ?? '';
$recommendationMode = (bool)($data['recommendation_mode'] ?? false);
$incomingHistory   = $data['conversationHistory']  ?? [];
$diagnosticContext = $data['diagnosticContext']    ?? null; // résultats du quiz injectés

if (empty($userMessage)) {
    echo json_encode(['reply' => 'Veuillez envoyer un message.']);
    exit;
}

// ─── API OpenAI ───
$model = 'gpt-4o-mini';
$url   = 'https://api.openai.com/v1/chat/completions';

// ─────────────────────────────────────────────────────────────────────────────
// MODE RECOMMENDATION : Diagnostic Consulting (JSON structuré)
// ─────────────────────────────────────────────────────────────────────────────
if ($recommendationMode) {
    $systemInstruction  = "Tu es un consultant expert en audit, qualité, sécurité et transformation digitale.\n";
    $systemInstruction .= "Analyse le profil de l'entreprise fourni par l'utilisateur et génère un rapport de diagnostic de type Consulting.\n";
    $systemInstruction .= "TU DOIS OBLIGATOIREMENT RETOURNER UN OBJET JSON STRICT respectant EXACTEMENT cette structure :\n";
    $systemInstruction .= "{\n";
    $systemInstruction .= "  \"maturity_score\": 45,\n";
    $systemInstruction .= "  \"risk_level\": \"Élevé\",\n";
    $systemInstruction .= "  \"top_vulnerabilities\": [\"Faille 1\", \"Faille 2\", \"Faille 3\"],\n";
    $systemInstruction .= "  \"roadmap\": [\n";
    $systemInstruction .= "    {\"step\": 1, \"title\": \"Phase 1\", \"description\": \"Détail 1\"},\n";
    $systemInstruction .= "    {\"step\": 2, \"title\": \"Phase 2\", \"description\": \"Détail 2\"},\n";
    $systemInstruction .= "    {\"step\": 3, \"title\": \"Phase 3\", \"description\": \"Détail 3\"},\n";
    $systemInstruction .= "    {\"step\": 4, \"title\": \"Phase 4\", \"description\": \"Détail 4\"},\n";
    $systemInstruction .= "    {\"step\": 5, \"title\": \"Phase 5\", \"description\": \"Détail 5\"}\n";
    $systemInstruction .= "  ],\n";
    $systemInstruction .= "  \"frameworks_suggested\": [\"ISO 27001\", \"RGPD\"],\n";
    $systemInstruction .= "  \"quick_wins\": [\"Action rapide 1\", \"Action rapide 2\", \"Action rapide 3\"],\n";
    $systemInstruction .= "  \"estimated_months\": 6\n";
    $systemInstruction .= "}\n";
    $systemInstruction .= "Remplace les valeurs d'exemple par ton analyse réelle. "
        . "maturity_score sur 100, risk_level parmi Faible/Modéré/Élevé/Critique, "
        . "quick_wins = 3 actions concrètes réalisables en moins de 30 jours, "
        . "estimated_months = durée estimée pour obtenir la première certification. "
        . "Ne retourne rien d'autre que le JSON valide.";

// ─────────────────────────────────────────────────────────────────────────────
// MODE STANDARD : Assistant polyvalent avec actions UI déclenchables
// ─────────────────────────────────────────────────────────────────────────────
} else {
    // Injecter le contexte page et diagnostic si disponible
    if (!empty($clientContext)) {
        $contextData .= "CONTEXTE EN TEMPS RÉEL :\n" . $clientContext . "\n\n";
    }
    if (!empty($diagnosticContext)) {
        $contextData .= "RÉSULTATS DU DIAGNOSTIC IA DE L'UTILISATEUR :\n" . json_encode($diagnosticContext, JSON_UNESCAPED_UNICODE) . "\n\n";
    }

    $systemInstruction = $contextData . <<<PROMPT
Tu es DigitBot, un assistant intelligent intégré à la plateforme DigitAdvisory.

Tu peux répondre à TOUT TYPE de question, pas seulement les certifications ISO.
Si la question est liée aux certifications ISO, tu es expert.
Si la question est une demande générale (calcul, rédaction, conseils business, etc.), réponds normalement et utilement.

Tes réponses doivent être :
- Concises (max 4 phrases pour les réponses courtes, plus longues si nécessaire)
- Professionnelles mais amicales
- Formatées en HTML simple (<strong>, <br>, <em>, <ul>, <li> si nécessaire)
- En français (sauf si l'utilisateur écrit dans une autre langue)

Tu es expert dans :
- Les normes ISO (27001, 9001, 14001, 45001, 22301, 50001, etc.)
- Le processus de certification (audit initial, suivi, renouvellement)
- Les critères d'évaluation et moyens de preuve
- La conformité RGPD, SOC2, CMMI, NIST
- Les conseils business et stratégie d'entreprise
- La rédaction de documents professionnels
- Les calculs et analyses de données

CAPACITÉS SPÉCIALES — Actions UI déclenchables :
Si l'utilisateur dit "lancer le quiz", "faire le diagnostic", "commencer l'évaluation", "voir les normes", "catalogue", ou toute demande similaire,
tu PEUX inclure une action dans ta réponse en ajoutant ce JSON à la fin de ta réponse HTML :
<action>{"type":"navigate","target":"quiz"}</action>
ou <action>{"type":"navigate","target":"manual"}</action>
ou <action>{"type":"highlight","certId":"1"}</action>

Si l'utilisateur mentionne les résultats de son diagnostic (maturity_score, roadmap, etc.),
utilise le RÉSULTAT DU DIAGNOSTIC IA s'il est fourni dans le contexte pour personnaliser ta réponse.

Pour les administrateurs : aide à la gestion des certifications et statistiques.
Pour les entreprises : guide dans le choix de certification, l'audit, et toute autre demande.
PROMPT;
}

// ─── Construction des messages avec historique ───
$messages = [['role' => 'system', 'content' => $systemInstruction]];

// Injecter l'historique (max 10 messages)
if (is_array($incomingHistory) && !empty($incomingHistory)) {
    $historySlice = array_slice($incomingHistory, -10);
    foreach ($historySlice as $msg) {
        if (isset($msg['role'], $msg['content']) && in_array($msg['role'], ['user', 'assistant'])) {
            $messages[] = [
                'role'    => $msg['role'],
                // Tronquer les messages assistant (longs) mais pas les messages user
                'content' => $msg['role'] === 'assistant'
                    ? substr((string)$msg['content'], 0, 600)
                    : substr((string)$msg['content'], 0, 1000)
            ];
        }
    }
}

// ─── TOUJOURS ajouter le message actuel de l'utilisateur en dernier ───
$messages[] = ['role' => 'user', 'content' => $userMessage];

// ─── Paramètres de l'appel ───
$postData = [
    'model'       => $model,
    'messages'    => $messages,
    'temperature' => $recommendationMode ? 0.5 : 0.75,
    'max_tokens'  => $recommendationMode ? 1500 : 700,
    'top_p'       => 0.9
];

if ($recommendationMode) {
    $postData['response_format'] = ['type' => 'json_object'];
}

// ─── Appel cURL avec retry + backoff exponentiel ───
function callOpenAI(string $url, array $postData, string $apiKey, int $maxRetries = 2): array
{
    for ($attempt = 0; $attempt <= $maxRetries; $attempt++) {
        $retryDelay = (int) pow(2, $attempt); // 1s, 2s, 4s

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData, JSON_UNESCAPED_UNICODE));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        // SSL : désactivé uniquement en dev local (XAMPP)
        $isLocal = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1', '::1']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !$isLocal);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $isLocal ? 0 : 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 35);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err      = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return ['error' => true, 'message' => 'Erreur de connexion : ' . $err];
        }

        $responseData = json_decode($response, true);

        if ($httpCode === 200 && isset($responseData['choices'][0]['message']['content'])) {
            return ['error' => false, 'reply' => $responseData['choices'][0]['message']['content']];
        }

        if (($httpCode === 429 || $httpCode >= 500) && $attempt < $maxRetries) {
            sleep($retryDelay);
            continue;
        }

        if ($httpCode === 401) {
            return ['error' => true, 'message' => 'Clé API OpenAI invalide ou non configurée.'];
        }

        $errorMsg = $responseData['error']['message'] ?? 'Erreur inconnue (HTTP ' . $httpCode . ')';
        return ['error' => true, 'message' => $errorMsg];
    }

    return ['error' => true, 'message' => 'Quota API dépassé ou service indisponible. Veuillez réessayer dans quelques instants.'];
}

$result = callOpenAI($url, $postData, $openai_API_Key);

if ($result['error']) {
    echo json_encode(['reply' => '⚠️ ' . $result['message']]);
} else {
    // ─── Extraction d'une éventuelle action UI encodée dans la réponse ───
    $reply  = $result['reply'];
    $action = null;

    if (!$recommendationMode && preg_match('/<action>(.*?)<\/action>/s', $reply, $matches)) {
        $actionJson = trim($matches[1]);
        $action     = json_decode($actionJson, true);
        $reply      = preg_replace('/<action>.*?<\/action>/s', '', $reply);
        $reply      = trim($reply);
    }

    $output = ['reply' => $reply];
    if ($action !== null) {
        $output['action'] = $action;
    }
    echo json_encode($output);
}
?>