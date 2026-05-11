<?php
/**
 * Classe Model : Certificat
 * Représente une certification ISO dans la plateforme DigitAdvisory.
 */
class Certificat
{
    private ?int    $id;
    private string  $norme;
    private string  $titre;
    private string  $version;
    private string  $statut;
    private int     $duree_validite;
    private ?string $description;
    private ?string $organisme;
    private ?string $logo_url;
    private ?string $date_ajout;

    // ─── Constructeur paramétré ───
    public function __construct(
        ?int    $id              = null,
        string  $norme           = '',
        string  $titre           = '',
        string  $version         = '2022',
        string  $statut          = 'Actif',
        int     $duree_validite  = 36,
        ?string $description     = null,
        ?string $organisme       = null,
        ?string $logo_url        = null,
        ?string $date_ajout      = null
    ) {
        $this->id             = $id;
        $this->norme          = $norme;
        $this->titre          = $titre;
        $this->version        = $version;
        $this->statut         = $statut;
        $this->duree_validite = $duree_validite;
        $this->description    = $description;
        $this->organisme      = $organisme;
        $this->logo_url       = $logo_url;
        $this->date_ajout     = $date_ajout;
    }

    // ─── Getters ───
    public function getId():            ?int    { return $this->id; }
    public function getNorme():         string  { return $this->norme; }
    public function getTitre():         string  { return $this->titre; }
    public function getVersion():       string  { return $this->version; }
    public function getStatut():        string  { return $this->statut; }
    public function getDureeValidite(): int     { return $this->duree_validite; }
    public function getDescription():   ?string { return $this->description; }
    public function getOrganisme():     ?string { return $this->organisme; }
    public function getLogoUrl():       ?string { return $this->logo_url; }
    public function getDateAjout():     ?string { return $this->date_ajout; }

    // ─── Setters ───
    public function setId(?int $id):                    void { $this->id             = $id; }
    public function setNorme(string $norme):            void { $this->norme          = $norme; }
    public function setTitre(string $titre):            void { $this->titre          = $titre; }
    public function setVersion(string $version):        void { $this->version        = $version; }
    public function setStatut(string $statut):          void { $this->statut         = $statut; }
    public function setDureeValidite(int $duree):       void { $this->duree_validite = $duree; }
    public function setDescription(?string $desc):      void { $this->description    = $desc; }
    public function setOrganisme(?string $org):         void { $this->organisme      = $org; }
    public function setLogoUrl(?string $logo):          void { $this->logo_url       = $logo; }
    public function setDateAjout(?string $date):        void { $this->date_ajout     = $date; }

    // ─── Méthode show() ───
    public function show(): string
    {
        return "[$this->id] $this->norme ($this->version) — $this->titre | Statut: $this->statut";
    }
}
?>
