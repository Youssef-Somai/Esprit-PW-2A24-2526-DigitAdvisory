<?php
/**
 * Classe Model : Critere
 * Représente un critère d'évaluation global dans la plateforme DigitAdvisory avec des exigences détaillées.
 */
class Critere
{
    private ?int    $id;
    private string  $nom;
    private string  $categorie;
    private ?string $description;
    private ?string $moyen_preuve;
    private int     $est_obligatoire;
    private string  $difficulte;
    private ?string $document_template;
    
    // Propriété additionnelle hydratée SEULEMENT lors de la liaison Many-to-Many
    public ?int $poids_specifique;

    // ─── Constructeur paramétré ───
    public function __construct(
        ?int    $id                = null,
        string  $nom               = '',
        string  $categorie         = 'Général',
        ?string $description       = null,
        ?string $moyen_preuve      = null,
        int     $est_obligatoire   = 1,
        string  $difficulte        = 'Moyen',
        ?string $document_template = null
    ) {
        $this->id                = $id;
        $this->nom               = $nom;
        $this->categorie         = $categorie;
        $this->description       = $description;
        $this->moyen_preuve      = $moyen_preuve;
        $this->est_obligatoire   = $est_obligatoire;
        $this->difficulte        = $difficulte;
        $this->document_template = $document_template;
        $this->poids_specifique  = null;
    }

    // ─── Getters ───
    public function getId():               ?int    { return $this->id; }
    public function getNom():              string  { return $this->nom; }
    public function getCategorie():        string  { return $this->categorie; }
    public function getDescription():      ?string { return $this->description; }
    public function getMoyenPreuve():      ?string { return $this->moyen_preuve; }
    public function getEstObligatoire():   int     { return $this->est_obligatoire; }
    public function getDifficulte():       string  { return $this->difficulte; }
    public function getDocumentTemplate(): ?string { return $this->document_template; }

    // ─── Setters ───
    public function setId(?int $id):                    void { $this->id                = $id; }
    public function setNom(string $nom):                 void { $this->nom               = $nom; }
    public function setCategorie(string $categorie):     void { $this->categorie         = $categorie; }
    public function setDescription(?string $desc):       void { $this->description       = $desc; }
    public function setMoyenPreuve(?string $preuve):     void { $this->moyen_preuve      = $preuve; }
    public function setEstObligatoire(int $ob):          void { $this->est_obligatoire   = $ob; }
    public function setDifficulte(string $diff):         void { $this->difficulte        = $diff; }
    public function setDocumentTemplate(?string $tpl):   void { $this->document_template = $tpl; }

    // ─── Méthode show() ───
    public function show(): string
    {
        return "[$this->id] [$this->categorie] $this->nom";
    }
}
?>
