<?php
/**
 * Model · Portfolio
 * Digit Advisory — Portfolio Module
 *
 * Classe entité représentant la table `portfolio`
 * (informations principales du profil consultant)
 */
class Portfolio
{
    private ?int    $id_portfolio;
    private int     $user_id;
    private string  $full_name;
    private string  $professional_title;
    private string  $experience_level;
    private string  $availability;
    private ?string $preferred_industry;
    private ?string $location;
    private ?string $bio;
    private ?string $created_at;
    private ?string $updated_at;

    public function __construct(
        ?int    $id_portfolio,
        int     $user_id,
        string  $full_name,
        string  $professional_title,
        string  $experience_level  = 'junior',
        string  $availability      = 'immediate',
        ?string $preferred_industry = null,
        ?string $location           = null,
        ?string $bio                = null,
        ?string $created_at         = null,
        ?string $updated_at         = null
    ) {
        $this->id_portfolio       = $id_portfolio;
        $this->user_id            = $user_id;
        $this->full_name          = $full_name;
        $this->professional_title = $professional_title;
        $this->experience_level   = $experience_level;
        $this->availability       = $availability;
        $this->preferred_industry = $preferred_industry;
        $this->location           = $location;
        $this->bio                = $bio;
        $this->created_at         = $created_at;
        $this->updated_at         = $updated_at;
    }

    /* ── Getters ── */
    public function getIdPortfolio(): ?int       { return $this->id_portfolio; }
    public function getUserId(): int             { return $this->user_id; }
    public function getFullName(): string        { return $this->full_name; }
    public function getProfessionalTitle(): string { return $this->professional_title; }
    public function getExperienceLevel(): string { return $this->experience_level; }
    public function getAvailability(): string    { return $this->availability; }
    public function getPreferredIndustry(): ?string { return $this->preferred_industry; }
    public function getLocation(): ?string       { return $this->location; }
    public function getBio(): ?string            { return $this->bio; }
    public function getCreatedAt(): ?string      { return $this->created_at; }
    public function getUpdatedAt(): ?string      { return $this->updated_at; }

    /* ── Setters ── */
    public function setIdPortfolio(?int $v): void       { $this->id_portfolio = $v; }
    public function setUserId(int $v): void             { $this->user_id = $v; }
    public function setFullName(string $v): void        { $this->full_name = $v; }
    public function setProfessionalTitle(string $v): void { $this->professional_title = $v; }
    public function setExperienceLevel(string $v): void { $this->experience_level = $v; }
    public function setAvailability(string $v): void    { $this->availability = $v; }
    public function setPreferredIndustry(?string $v): void { $this->preferred_industry = $v; }
    public function setLocation(?string $v): void       { $this->location = $v; }
    public function setBio(?string $v): void            { $this->bio = $v; }

    public function show(): void { var_dump($this); }
}
?>
