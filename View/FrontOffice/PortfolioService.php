<?php
/**
 * Service · PortfolioService
 * Digit Advisory · Portfolio Module
 *
 * Business logic layer: orchestrates Model calls,
 * validates input data, and returns clean result arrays.
 */
class PortfolioService {

    private ExpertPortfolioModel $model;

    public function __construct(ExpertPortfolioModel $model) {
        $this->model = $model;
    }

    // ──────────────────────────────────────────
    //  Validation helpers
    // ──────────────────────────────────────────

    private function validateCore(array $data): array {
        $errors = [];
        if (empty(trim($data['full_name'] ?? '')))
            $errors[] = 'Full name is required.';
        if (empty(trim($data['professional_title'] ?? '')))
            $errors[] = 'Professional title is required.';
        $validLevels = ['junior','mid','senior','expert'];
        if (!in_array($data['experience_level'] ?? '', $validLevels))
            $errors[] = 'Invalid experience level.';
        $validObjectives = ['employment','freelance','consulting','open'];
        if (!in_array($data['career_objective'] ?? '', $validObjectives))
            $errors[] = 'Invalid career objective.';
        $validAvailability = ['immediate','one_month','three_months','unavailable'];
        if (!in_array($data['availability'] ?? '', $validAvailability))
            $errors[] = 'Invalid availability value.';
        return $errors;
    }

    private function sanitizeCore(array $raw): array {
        return [
            'user_id'            => (int) ($raw['user_id'] ?? 1),
            'full_name'          => trim(strip_tags($raw['full_name'])),
            'professional_title' => trim(strip_tags($raw['professional_title'])),
            'experience_level'   => $raw['experience_level'],
            'career_objective'   => $raw['career_objective'],
            'preferred_industry' => trim(strip_tags($raw['preferred_industry'] ?? '')),
            'location'           => trim(strip_tags($raw['location'] ?? '')),
            'remote_option'      => isset($raw['remote_option']) ? 1 : 0,
            'availability'       => $raw['availability'],
            'bio'                => trim(strip_tags($raw['bio'] ?? '')),
            'linkedin_url'       => filter_var($raw['linkedin_url'] ?? '', FILTER_SANITIZE_URL) ?: null,
            'github_url'         => filter_var($raw['github_url']   ?? '', FILTER_SANITIZE_URL) ?: null,
            'website_url'        => filter_var($raw['website_url']  ?? '', FILTER_SANITIZE_URL) ?: null,
        ];
    }

    private function parseSkills(array $raw): array {
        $names  = $raw['skill_name']  ?? [];
        $types  = $raw['skill_type']  ?? [];
        $levels = $raw['skill_level'] ?? [];
        $skills = [];
        foreach ($names as $i => $name) {
            if (empty(trim($name))) continue;
            $skills[] = [
                'skill_name'  => trim(strip_tags($name)),
                'skill_type'  => $types[$i]  ?? 'technical',
                'skill_level' => $levels[$i] ?? 'intermediate',
            ];
        }
        return $skills;
    }

    private function parseCertifications(array $raw): array {
        $names   = $raw['cert_name']    ?? [];
        $issuers = $raw['cert_issuer']  ?? [];
        $dates   = $raw['cert_issue']   ?? [];
        $expiry  = $raw['cert_expiry']  ?? [];
        $urls    = $raw['cert_url']     ?? [];
        $certs   = [];
        foreach ($names as $i => $name) {
            if (empty(trim($name))) continue;
            $certs[] = [
                'cert_name'   => trim(strip_tags($name)),
                'issuer'      => trim(strip_tags($issuers[$i] ?? '')),
                'issue_date'  => !empty($dates[$i])  ? $dates[$i]  : null,
                'expiry_date' => !empty($expiry[$i]) ? $expiry[$i] : null,
                'cert_url'    => filter_var($urls[$i] ?? '', FILTER_SANITIZE_URL) ?: null,
            ];
        }
        return $certs;
    }

    private function parseExperiences(array $raw): array {
        $titles    = $raw['exp_job_title']   ?? [];
        $companies = $raw['exp_company']     ?? [];
        $starts    = $raw['exp_start']       ?? [];
        $ends      = $raw['exp_end']         ?? [];
        $currents  = $raw['exp_current']     ?? [];
        $descs     = $raw['exp_description'] ?? [];
        $locs      = $raw['exp_location']    ?? [];
        $exps      = [];
        foreach ($titles as $i => $title) {
            if (empty(trim($title))) continue;
            $exps[] = [
                'job_title'   => trim(strip_tags($title)),
                'company'     => trim(strip_tags($companies[$i] ?? '')),
                'start_date'  => $starts[$i] ?? date('Y-m-d'),
                'end_date'    => $ends[$i]   ?? null,
                'is_current'  => isset($currents[$i]) ? 1 : 0,
                'description' => trim(strip_tags($descs[$i] ?? '')),
                'location'    => trim(strip_tags($locs[$i]  ?? '')),
            ];
        }
        return $exps;
    }

    // ──────────────────────────────────────────
    //  Public service methods
    // ──────────────────────────────────────────

    public function getAllPortfolios(int $userId): array {
        $portfolios = $this->model->getAllByUser($userId);
        // Attach skills/certs/exps counts for card display
        foreach ($portfolios as &$p) {
            $p['skills_count'] = count($this->model->getSkills($p['id_portfolio']));
            $p['certs_count']  = count($this->model->getCertifications($p['id_portfolio']));
            $p['exp_count']    = count($this->model->getExperiences($p['id_portfolio']));
        }
        return $portfolios;
    }

    public function getPortfolio(int $id): ?array {
        return $this->model->getById($id);
    }

    /** @return array{success:bool, id?:int, errors?:string[]} */
    public function createPortfolio(array $postData): array {
        $errors = $this->validateCore($postData);
        if (!empty($errors)) return ['success' => false, 'errors' => $errors];

        $data = $this->sanitizeCore($postData);
        $id   = $this->model->create($data);

        $this->model->replaceSkills($id,          $this->parseSkills($postData));
        $this->model->replaceCertifications($id,  $this->parseCertifications($postData));
        $this->model->replaceExperiences($id,      $this->parseExperiences($postData));

        return ['success' => true, 'id' => $id];
    }

    /** @return array{success:bool, errors?:string[]} */
    public function updatePortfolio(int $id, array $postData): array {
        $errors = $this->validateCore($postData);
        if (!empty($errors)) return ['success' => false, 'errors' => $errors];

        $data = $this->sanitizeCore($postData);
        $this->model->update($id, $data);

        $this->model->replaceSkills($id,         $this->parseSkills($postData));
        $this->model->replaceCertifications($id, $this->parseCertifications($postData));
        $this->model->replaceExperiences($id,    $this->parseExperiences($postData));

        return ['success' => true];
    }

    public function deletePortfolio(int $id): bool {
        return $this->model->delete($id);
    }

    // ──────────────────────────────────────────
    //  Label helpers (used in views)
    // ──────────────────────────────────────────

    public static function levelLabel(string $v): string {
        return match($v) {
            'junior'  => 'Junior',
            'mid'     => 'Mid-level',
            'senior'  => 'Senior',
            'expert'  => 'Expert',
            default   => ucfirst($v),
        };
    }

    public static function objectiveLabel(string $v): string {
        return match($v) {
            'employment' => 'Full-time Employment',
            'freelance'  => 'Freelance',
            'consulting' => 'Consulting Missions',
            'open'       => 'Open to All',
            default      => ucfirst($v),
        };
    }

    public static function availabilityLabel(string $v): string {
        return match($v) {
            'immediate'     => 'Immediately Available',
            'one_month'     => 'Available in 1 Month',
            'three_months'  => 'Available in 3 Months',
            'unavailable'   => 'Not Available',
            default         => ucfirst($v),
        };
    }
}
