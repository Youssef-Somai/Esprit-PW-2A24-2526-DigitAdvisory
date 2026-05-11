<?php
/**
 * Model · ExpertPortfolio
 * Digit Advisory · Portfolio Module
 *
 * Handles all DB interactions for the expert_portfolio table
 * and its child tables (skills, certifications, experiences).
 */
class ExpertPortfolioModel {

    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    // ──────────────────────────────────────────
    //  Portfolio CRUD
    // ──────────────────────────────────────────

    /** Fetch all portfolios for a given user */
    public function getAllByUser(int $userId): array {
        $stmt = $this->db->prepare(
            'SELECT * FROM expert_portfolio WHERE user_id = :uid ORDER BY created_at DESC'
        );
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    /** Fetch a single portfolio with its nested data */
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare('SELECT * FROM expert_portfolio WHERE id_portfolio = :id');
        $stmt->execute([':id' => $id]);
        $portfolio = $stmt->fetch();
        if (!$portfolio) return null;

        $portfolio['skills']           = $this->getSkills($id);
        $portfolio['certifications']   = $this->getCertifications($id);
        $portfolio['experiences']      = $this->getExperiences($id);
        return $portfolio;
    }

    /** Insert a new portfolio and return its new ID */
    public function create(array $data): int {
        $sql = '
            INSERT INTO expert_portfolio
                (user_id, full_name, professional_title, experience_level,
                 career_objective, preferred_industry, location, remote_option,
                 availability, bio, linkedin_url, github_url, website_url)
            VALUES
                (:user_id, :full_name, :professional_title, :experience_level,
                 :career_objective, :preferred_industry, :location, :remote_option,
                 :availability, :bio, :linkedin_url, :github_url, :website_url)
        ';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id'            => $data['user_id']            ?? 1,
            ':full_name'          => $data['full_name'],
            ':professional_title' => $data['professional_title'],
            ':experience_level'   => $data['experience_level'],
            ':career_objective'   => $data['career_objective'],
            ':preferred_industry' => $data['preferred_industry'] ?? null,
            ':location'           => $data['location']           ?? null,
            ':remote_option'      => $data['remote_option']      ?? 0,
            ':availability'       => $data['availability'],
            ':bio'                => $data['bio']                ?? null,
            ':linkedin_url'       => $data['linkedin_url']       ?? null,
            ':github_url'         => $data['github_url']         ?? null,
            ':website_url'        => $data['website_url']        ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /** Update an existing portfolio */
    public function update(int $id, array $data): bool {
        $sql = '
            UPDATE expert_portfolio SET
                full_name          = :full_name,
                professional_title = :professional_title,
                experience_level   = :experience_level,
                career_objective   = :career_objective,
                preferred_industry = :preferred_industry,
                location           = :location,
                remote_option      = :remote_option,
                availability       = :availability,
                bio                = :bio,
                linkedin_url       = :linkedin_url,
                github_url         = :github_url,
                website_url        = :website_url
            WHERE id_portfolio = :id
        ';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':full_name'          => $data['full_name'],
            ':professional_title' => $data['professional_title'],
            ':experience_level'   => $data['experience_level'],
            ':career_objective'   => $data['career_objective'],
            ':preferred_industry' => $data['preferred_industry'] ?? null,
            ':location'           => $data['location']           ?? null,
            ':remote_option'      => $data['remote_option']      ?? 0,
            ':availability'       => $data['availability'],
            ':bio'                => $data['bio']                ?? null,
            ':linkedin_url'       => $data['linkedin_url']       ?? null,
            ':github_url'         => $data['github_url']         ?? null,
            ':website_url'        => $data['website_url']        ?? null,
            ':id'                 => $id,
        ]);
    }

    /** Delete a portfolio (cascades to skills, certs, experiences) */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM expert_portfolio WHERE id_portfolio = :id');
        return $stmt->execute([':id' => $id]);
    }

    // ──────────────────────────────────────────
    //  Skills CRUD
    // ──────────────────────────────────────────

    public function getSkills(int $portfolioId): array {
        $stmt = $this->db->prepare('SELECT * FROM expert_skills WHERE id_portfolio = :id');
        $stmt->execute([':id' => $portfolioId]);
        return $stmt->fetchAll();
    }

    public function replaceSkills(int $portfolioId, array $skills): void {
        $this->db->prepare('DELETE FROM expert_skills WHERE id_portfolio = :id')
                 ->execute([':id' => $portfolioId]);

        $stmt = $this->db->prepare(
            'INSERT INTO expert_skills (id_portfolio, skill_name, skill_type, skill_level)
             VALUES (:id, :name, :type, :level)'
        );
        foreach ($skills as $skill) {
            $stmt->execute([
                ':id'    => $portfolioId,
                ':name'  => $skill['skill_name'],
                ':type'  => $skill['skill_type']  ?? 'technical',
                ':level' => $skill['skill_level'] ?? 'intermediate',
            ]);
        }
    }

    // ──────────────────────────────────────────
    //  Certifications CRUD
    // ──────────────────────────────────────────

    public function getCertifications(int $portfolioId): array {
        $stmt = $this->db->prepare('SELECT * FROM expert_certifications WHERE id_portfolio = :id');
        $stmt->execute([':id' => $portfolioId]);
        return $stmt->fetchAll();
    }

    public function replaceCertifications(int $portfolioId, array $certs): void {
        $this->db->prepare('DELETE FROM expert_certifications WHERE id_portfolio = :id')
                 ->execute([':id' => $portfolioId]);

        $stmt = $this->db->prepare(
            'INSERT INTO expert_certifications (id_portfolio, cert_name, issuer, issue_date, expiry_date, cert_url)
             VALUES (:id, :name, :issuer, :issue_date, :expiry_date, :cert_url)'
        );
        foreach ($certs as $cert) {
            $stmt->execute([
                ':id'          => $portfolioId,
                ':name'        => $cert['cert_name'],
                ':issuer'      => $cert['issuer']      ?? null,
                ':issue_date'  => $cert['issue_date']  ?? null,
                ':expiry_date' => $cert['expiry_date'] ?? null,
                ':cert_url'    => $cert['cert_url']    ?? null,
            ]);
        }
    }

    // ──────────────────────────────────────────
    //  Experiences CRUD
    // ──────────────────────────────────────────

    public function getExperiences(int $portfolioId): array {
        $stmt = $this->db->prepare(
            'SELECT * FROM expert_experiences WHERE id_portfolio = :id ORDER BY start_date DESC'
        );
        $stmt->execute([':id' => $portfolioId]);
        return $stmt->fetchAll();
    }

    public function replaceExperiences(int $portfolioId, array $exps): void {
        $this->db->prepare('DELETE FROM expert_experiences WHERE id_portfolio = :id')
                 ->execute([':id' => $portfolioId]);

        $stmt = $this->db->prepare(
            'INSERT INTO expert_experiences
                (id_portfolio, job_title, company, start_date, end_date, is_current, description, location)
             VALUES (:id, :job_title, :company, :start_date, :end_date, :is_current, :description, :location)'
        );
        foreach ($exps as $exp) {
            $stmt->execute([
                ':id'          => $portfolioId,
                ':job_title'   => $exp['job_title'],
                ':company'     => $exp['company'],
                ':start_date'  => $exp['start_date'],
                ':end_date'    => (!empty($exp['is_current'])) ? null : ($exp['end_date'] ?? null),
                ':is_current'  => $exp['is_current']  ?? 0,
                ':description' => $exp['description'] ?? null,
                ':location'    => $exp['location']    ?? null,
            ]);
        }
    }
}
