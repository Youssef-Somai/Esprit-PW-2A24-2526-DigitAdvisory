<?php
/**
 * Controller · AIController
 * Digit Advisory — AI Module powered by OpenAI ChatGPT API
 *
 * Endpoints (appelés via fetch() depuis le JS) :
 *   ?action=generate_bio       → génère une bio professionnelle
 *   ?action=analyze_profile    → analyse complète + score IA
 *   ?action=skill_gap          → lacunes de compétences par secteur
 *   ?action=career_path        → trajectoire de carrière personnalisée
 *   ?action=salary_estimate    → estimation de salaire avec justification
 */

require_once __DIR__ . '/../config.php';

// ─── Configuration OpenAI ─────────────────────────────────────────────────────
// La clé API est chargée depuis config.local.php (gitignored)
$_localConfig = __DIR__ . '/../config.local.php';
if (!file_exists($_localConfig)) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error'   => 'Fichier config.local.php introuvable. Créez-le à la racine du projet avec votre clé OpenAI.'
    ]);
    exit;
}
require_once $_localConfig;

if (!defined('OPENAI_API_KEY') || str_contains(OPENAI_API_KEY, 'METTEZ_VOTRE_CLE')) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error'   => 'Clé API OpenAI non configurée. Ouvrez config.local.php et remplacez METTEZ_VOTRE_CLE_ICI par votre vraie clé.'
    ]);
    exit;
}

define('OPENAI_API_URL', 'https://api.openai.com/v1/chat/completions');
// ─────────────────────────────────────────────────────────────────────────────

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

class AIController
{
    /* ─────────────────────────────────────────────────────────
       ROUTER
    ───────────────────────────────────────────────────────── */
    public function handleRequest(): void
    {
        $action = $_GET['action'] ?? $_POST['action'] ?? '';

        switch ($action) {
            case 'generate_bio':    $this->generateBio();       break;
            case 'analyze_profile': $this->analyzeProfile();    break;
            case 'skill_gap':       $this->skillGapAnalysis();  break;
            case 'career_path':     $this->careerPath();        break;
            case 'salary_estimate': $this->salaryEstimate();    break;
            default:                $this->sendError('Action IA non reconnue : ' . $action);
        }
    }

    /* ─────────────────────────────────────────────────────────
       1) GÉNÉRER UNE BIO PROFESSIONNELLE
    ───────────────────────────────────────────────────────── */
    private function generateBio(): void
    {
        $data = $this->getJsonInput();

        $name         = $data['full_name']          ?? 'Consultant';
        $title        = $data['professional_title']  ?? 'Consultant';
        $level        = $data['experience_level']    ?? 'junior';
        $industry     = $data['preferred_industry']  ?? 'Conseil';
        $location     = $data['location']            ?? '';
        $skills       = array_column($data['skills']          ?? [], 'skill_name');
        $experiences  = array_column($data['experiences']     ?? [], 'job_title');
        $certifications = array_column($data['certifications'] ?? [], 'cert_name');

        $skillsList = implode(', ', array_slice($skills, 0, 8)) ?: 'non précisées';
        $expList    = implode(', ', array_slice($experiences, 0, 3)) ?: 'non précisées';
        $certList   = implode(', ', array_slice($certifications, 0, 3)) ?: 'aucune';
        $levelLabel = ['junior' => 'Débutant (0-3 ans)', 'mid' => 'Intermédiaire (3-6 ans)', 'senior' => 'Senior (6-12 ans)', 'expert' => 'Expert (12+ ans)'][$level] ?? $level;

        $prompt = <<<PROMPT
Tu es un expert en rédaction de profils professionnels pour des consultants.

Génère une bio professionnelle en français pour un consultant avec les informations suivantes :
- Nom : {$name}
- Titre : {$title}
- Niveau d'expérience : {$levelLabel}
- Secteur préféré : {$industry}
- Localisation : {$location}
- Compétences clés : {$skillsList}
- Expériences : {$expList}
- Certifications : {$certList}

La bio doit :
- Être rédigée à la première personne
- Faire entre 80 et 130 mots
- Être professionnelle, dynamique et percutante
- Mettre en avant les points forts du profil
- Ne pas répéter mot pour mot les informations données, mais les synthétiser intelligemment

Retourne UNIQUEMENT le texte de la bio, sans titre ni introduction.
PROMPT;

        $response = $this->callOpenAI($prompt);
        echo json_encode(['success' => true, 'bio' => $response]);
    }

    /* ─────────────────────────────────────────────────────────
       2) ANALYSE COMPLÈTE DU PROFIL
    ───────────────────────────────────────────────────────── */
    private function analyzeProfile(): void
    {
        $data = $this->getJsonInput();

        $name     = $data['full_name']           ?? 'Consultant';
        $title    = $data['professional_title']   ?? 'Consultant';
        $level    = $data['experience_level']     ?? 'junior';
        $industry = $data['preferred_industry']   ?? 'Conseil';
        $bio      = $data['bio']                  ?? '';

        $skills       = array_column($data['skills']          ?? [], 'skill_name');
        $experiences  = $data['experiences']                  ?? [];
        $certifications = array_column($data['certifications'] ?? [], 'cert_name');

        $nbSkills  = count($skills);
        $nbExp     = count($experiences);
        $nbCerts   = count($certifications);
        $hasBio    = strlen(trim($bio)) > 30 ? 'Oui' : 'Non';
        $hasLinkedIn = !empty($data['linkedin_url']) ? 'Oui' : 'Non';

        $skillsList = implode(', ', $skills) ?: 'aucune';
        $expDesc    = array_map(fn($e) => ($e['job_title'] ?? '') . ' chez ' . ($e['company'] ?? ''), $experiences);
        $expList    = implode('; ', array_slice($expDesc, 0, 3)) ?: 'aucune';
        $certList   = implode(', ', $certifications) ?: 'aucune';

        $prompt = <<<PROMPT
Tu es un expert en évaluation de profils professionnels de consultants.

Analyse ce profil de consultant et retourne une réponse JSON UNIQUEMENT (sans markdown, sans texte avant ni après) avec exactement cette structure :

{
  "score_global": <nombre entre 0 et 100>,
  "score_competences": <nombre entre 0 et 100>,
  "score_experience": <nombre entre 0 et 100>,
  "score_certifications": <nombre entre 0 et 100>,
  "score_coherence": <nombre entre 0 et 100>,
  "niveau": "<Insuffisant|À améliorer|Bon|Excellent>",
  "recommandations": [
    {"priorite": "haute", "icone": "fa-pen-fancy", "texte": "..."},
    {"priorite": "haute", "icone": "fa-code", "texte": "..."},
    {"priorite": "moyenne", "icone": "fa-award", "texte": "..."}
  ],
  "points_forts": ["...", "...", "..."],
  "compatibilite_marche": {
    "Finance": <0-100>,
    "IT": <0-100>,
    "Consulting": <0-100>,
    "Healthcare": <0-100>,
    "Digital": <0-100>
  }
}

Profil à analyser :
- Nom : {$name}
- Titre : {$title}
- Niveau : {$level}
- Secteur préféré : {$industry}
- Bio présente : {$hasBio}
- LinkedIn : {$hasLinkedIn}
- Nombre de compétences : {$nbSkills} ({$skillsList})
- Nombre d'expériences : {$nbExp} ({$expList})
- Nombre de certifications : {$nbCerts} ({$certList})

Évalue honnêtement et retourne UNIQUEMENT le JSON.
PROMPT;

        $response = $this->callOpenAI($prompt, 1200);

        // Nettoyer la réponse pour extraire uniquement le JSON
        $response = preg_replace('/```json\s*/i', '', $response);
        $response = preg_replace('/```\s*/i', '', $response);
        $response = trim($response);

        $decoded = json_decode($response, true);
        if (!$decoded) {
            $this->sendError('Réponse IA invalide. Réessayez.');
        }

        echo json_encode(['success' => true, 'analysis' => $decoded]);
    }

    /* ─────────────────────────────────────────────────────────
       3) SKILL GAP ANALYSIS
    ───────────────────────────────────────────────────────── */
    private function skillGapAnalysis(): void
    {
        $data = $this->getJsonInput();

        $industry  = $data['preferred_industry'] ?? 'Conseil';
        $level     = $data['experience_level']   ?? 'junior';
        $skills    = array_column($data['skills'] ?? [], 'skill_name');
        $skillsList = implode(', ', $skills) ?: 'aucune compétence listée';

        $prompt = <<<PROMPT
Tu es un expert RH et recrutement dans le secteur {$industry}.

Un consultant de niveau "{$level}" possède les compétences suivantes : {$skillsList}

Effectue une analyse des lacunes de compétences (skill gap analysis) pour le secteur "{$industry}" et retourne UNIQUEMENT un JSON valide avec exactement cette structure :

{
  "secteur_principal": {
    "nom": "{$industry}",
    "competences_possedees": ["skill1", "skill2"],
    "competences_manquantes": ["skill3", "skill4"],
    "couverture_pct": <0-100>,
    "priorite_acquisition": ["skill_critique1", "skill_critique2"]
  },
  "secteurs_alternatifs": [
    {
      "nom": "Nom du secteur",
      "couverture_pct": <0-100>,
      "competences_cles_requises": ["skill1", "skill2"],
      "effort_transition": "<faible|moyen|élevé>"
    }
  ],
  "plan_formation": [
    {"competence": "...", "ressource": "...", "duree_estimee": "...", "priorite": "<haute|moyenne|faible>"}
  ]
}

Retourne UNIQUEMENT le JSON, sans aucun texte avant ou après.
PROMPT;

        $response = $this->callOpenAI($prompt, 1500);
        $response = preg_replace('/```json\s*/i', '', $response);
        $response = preg_replace('/```\s*/i', '', $response);
        $response = trim($response);

        $decoded = json_decode($response, true);
        if (!$decoded) {
            $this->sendError('Réponse IA invalide pour skill gap.');
        }

        echo json_encode(['success' => true, 'skill_gap' => $decoded]);
    }

    /* ─────────────────────────────────────────────────────────
       4) TRAJECTOIRE DE CARRIÈRE
    ───────────────────────────────────────────────────────── */
    private function careerPath(): void
    {
        $data = $this->getJsonInput();

        $title    = $data['professional_title']  ?? 'Consultant';
        $level    = $data['experience_level']    ?? 'junior';
        $industry = $data['preferred_industry']  ?? 'Conseil';
        $skills   = array_column($data['skills'] ?? [], 'skill_name');
        $certs    = array_column($data['certifications'] ?? [], 'cert_name');

        $skillsList = implode(', ', array_slice($skills, 0, 6)) ?: 'non précisées';
        $certsList  = implode(', ', $certs) ?: 'aucune';

        $prompt = <<<PROMPT
Tu es un coach de carrière expert en conseil et digital.

Génère une trajectoire de carrière personnalisée pour un consultant avec ce profil :
- Titre actuel : {$title}
- Niveau actuel : {$level}
- Secteur : {$industry}
- Compétences : {$skillsList}
- Certifications : {$certsList}

Retourne UNIQUEMENT un JSON valide avec cette structure exacte :

{
  "etapes": [
    {
      "role": "Titre du poste",
      "niveau": "junior|mid|senior|expert|director",
      "statut": "passé|actuel|futur",
      "duree": "X-Y ans d'expérience",
      "fourchette_salaire": "XX-YYK€",
      "competences_requises": ["skill1", "skill2"],
      "conseil": "Un conseil personnalisé pour atteindre cette étape"
    }
  ],
  "message_motivation": "Un message motivant et personnalisé pour ce consultant"
}

Génère exactement 5 étapes de carrière logiques et cohérentes avec le profil.
Retourne UNIQUEMENT le JSON.
PROMPT;

        $response = $this->callOpenAI($prompt, 1500);
        $response = preg_replace('/```json\s*/i', '', $response);
        $response = preg_replace('/```\s*/i', '', $response);
        $response = trim($response);

        $decoded = json_decode($response, true);
        if (!$decoded) {
            $this->sendError('Réponse IA invalide pour la trajectoire.');
        }

        echo json_encode(['success' => true, 'career_path' => $decoded]);
    }

    /* ─────────────────────────────────────────────────────────
       5) ESTIMATION DE SALAIRE
    ───────────────────────────────────────────────────────── */
    private function salaryEstimate(): void
    {
        $data = $this->getJsonInput();

        $title    = $data['professional_title']  ?? 'Consultant';
        $level    = $data['experience_level']    ?? 'junior';
        $industry = $data['preferred_industry']  ?? 'Conseil';
        $location = $data['location']            ?? 'France';
        $nbSkills = count($data['skills']        ?? []);
        $nbCerts  = count($data['certifications'] ?? []);

        $prompt = <<<PROMPT
Tu es un expert en rémunération des consultants en France.

Estime le salaire annuel brut pour un consultant avec ce profil :
- Titre : {$title}
- Niveau : {$level}
- Secteur : {$industry}
- Localisation : {$location}
- Nombre de compétences : {$nbSkills}
- Nombre de certifications : {$nbCerts}

Retourne UNIQUEMENT un JSON valide avec cette structure :

{
  "salaire_min": <entier en euros>,
  "salaire_median": <entier en euros>,
  "salaire_max": <entier en euros>,
  "devise": "EUR",
  "periode": "annuel brut",
  "facteurs": {
    "secteur": "<impact positif|neutre|négatif>",
    "niveau": "<impact positif|neutre|négatif>",
    "localisation": "<impact positif|neutre|négatif>",
    "certifications": "<impact positif|neutre|négatif>"
  },
  "justification": "Explication courte des facteurs qui influencent cette estimation",
  "tendance_marche": "<en hausse|stable|en baisse>"
}

Retourne UNIQUEMENT le JSON, sans texte avant ou après.
PROMPT;

        $response = $this->callOpenAI($prompt, 800);
        $response = preg_replace('/```json\s*/i', '', $response);
        $response = preg_replace('/```\s*/i', '', $response);
        $response = trim($response);

        $decoded = json_decode($response, true);
        if (!$decoded) {
            $this->sendError('Réponse IA invalide pour le salaire.');
        }

        echo json_encode(['success' => true, 'salary' => $decoded]);
    }

    /* ─────────────────────────────────────────────────────────
       APPEL API OPENAI (cURL)
    ───────────────────────────────────────────────────────── */
    private function callOpenAI(string $prompt, int $maxTokens = 600): string
    {
        $payload = json_encode([
            'model'       => OPENAI_MODEL,
            'messages'    => [
                [
                    'role'    => 'system',
                    'content' => 'Tu es un assistant expert en ressources humaines, recrutement et développement de carrière pour des consultants professionnels. Tu réponds toujours en français.'
                ],
                [
                    'role'    => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens'  => $maxTokens,
            'temperature' => 0.7,
        ]);

        $ch = curl_init(OPENAI_API_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . OPENAI_API_KEY,
            ],
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false, // Pour XAMPP local
        ]);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            $this->sendError('Erreur réseau : ' . $curlError);
        }

        $decoded = json_decode($result, true);

        if ($httpCode !== 200) {
            $errorMsg = $decoded['error']['message'] ?? 'Erreur OpenAI inconnue (HTTP ' . $httpCode . ')';
            $this->sendError('OpenAI API : ' . $errorMsg);
        }

        $content = $decoded['choices'][0]['message']['content'] ?? '';
        if (!$content) {
            $this->sendError('Réponse vide de l\'API OpenAI.');
        }

        return trim($content);
    }

    /* ─────────────────────────────────────────────────────────
       HELPERS
    ───────────────────────────────────────────────────────── */
    private function getJsonInput(): array
    {
        $raw  = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            // Fallback sur POST
            $data = $_POST;
        }
        if (!is_array($data)) {
            $this->sendError('Payload JSON invalide.');
        }
        return $data;
    }

    private function sendError(string $msg, int $code = 400): void
    {
        http_response_code($code);
        echo json_encode(['success' => false, 'error' => $msg]);
        exit;
    }
}

// Point d'entrée
$controller = new AIController();
$controller->handleRequest();
