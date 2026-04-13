<?php
/**
 * Classe Model : Certificat
 * Représente une certification ISO dans la plateforme DigitAdvisory.
 * Attributs privés + constructeur paramétré + getters / setters
 */
class Certificat
{
    private ?int    $id;
    private string  $norme;
    private string  $titre;
    private ?string $description;
    private ?string $organisme;
    private ?string $date_ajout;

    // ─── Constructeur paramétré ───
    public function __construct(
        ?int    $id          = null,
        string  $norme       = '',
        string  $titre       = '',
        ?string $description = null,
        ?string $organisme   = null,
        ?string $date_ajout  = null
    ) {
        $this->id          = $id;
        $this->norme       = $norme;
        $this->titre       = $titre;
        $this->description = $description;
        $this->organisme   = $organisme;
        $this->date_ajout  = $date_ajout;
    }

    // ─── Getters ───
    public function getId():          ?int    { return $this->id; }
    public function getNorme():       string  { return $this->norme; }
    public function getTitre():       string  { return $this->titre; }
    public function getDescription(): ?string { return $this->description; }
    public function getOrganisme():   ?string { return $this->organisme; }
    public function getDateAjout():   ?string { return $this->date_ajout; }

    // ─── Setters ───
    public function setId(?int $id):                void { $this->id          = $id; }
    public function setNorme(string $norme):         void { $this->norme       = $norme; }
    public function setTitre(string $titre):         void { $this->titre       = $titre; }
    public function setDescription(?string $desc):   void { $this->description = $desc; }
    public function setOrganisme(?string $org):      void { $this->organisme   = $org; }
    public function setDateAjout(?string $date):     void { $this->date_ajout  = $date; }

    // ─── Méthode show() ───
    public function show(): string
    {
        return "[$this->id] $this->norme — $this->titre | Organisme: $this->organisme | Ajouté le: $this->date_ajout";
    }
}
?>
