<?php
/**
 * Model · ElementPortfolio
 * Digit Advisory — Portfolio Module
 *
 * Classe entité représentant la table `element_portfolio`
 * (compétences, expériences, certifications liées à un portfolio)
 *
 * type_element : 'skill' | 'experience' | 'certification'
 * titre        : nom de la compétence / intitulé du poste / nom de la certification
 * description  : entreprise (pour expérience) / organisme (pour certification)
 * date_debut   : date de début (pour expérience)
 * date_fin     : date de fin  (pour expérience, null = en cours)
 * niveau       : niveau de la compétence (pour skill)
 */
class ElementPortfolio
{
    private ?int    $id_element;
    private int     $id_portfolio;
    private string  $type_element;
    private string  $titre;
    private ?string $description;
    private ?string $date_debut;
    private ?string $date_fin;
    private ?string $niveau;

    public function __construct(
        ?int    $id_element,
        int     $id_portfolio,
        string  $type_element,
        string  $titre,
        ?string $description = null,
        ?string $date_debut  = null,
        ?string $date_fin    = null,
        ?string $niveau      = null
    ) {
        $this->id_element   = $id_element;
        $this->id_portfolio = $id_portfolio;
        $this->type_element = $type_element;
        $this->titre        = $titre;
        $this->description  = $description;
        $this->date_debut   = $date_debut;
        $this->date_fin     = $date_fin;
        $this->niveau       = $niveau;
    }

    /* ── Getters ── */
    public function getIdElement(): ?int     { return $this->id_element; }
    public function getIdPortfolio(): int    { return $this->id_portfolio; }
    public function getTypeElement(): string { return $this->type_element; }
    public function getTitre(): string       { return $this->titre; }
    public function getDescription(): ?string { return $this->description; }
    public function getDateDebut(): ?string  { return $this->date_debut; }
    public function getDateFin(): ?string    { return $this->date_fin; }
    public function getNiveau(): ?string     { return $this->niveau; }

    /* ── Setters ── */
    public function setIdElement(?int $v): void     { $this->id_element = $v; }
    public function setIdPortfolio(int $v): void    { $this->id_portfolio = $v; }
    public function setTypeElement(string $v): void { $this->type_element = $v; }
    public function setTitre(string $v): void       { $this->titre = $v; }
    public function setDescription(?string $v): void { $this->description = $v; }
    public function setDateDebut(?string $v): void  { $this->date_debut = $v; }
    public function setDateFin(?string $v): void    { $this->date_fin = $v; }
    public function setNiveau(?string $v): void     { $this->niveau = $v; }

    public function show(): void { var_dump($this); }
}
?>
