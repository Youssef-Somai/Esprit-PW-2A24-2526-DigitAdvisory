<?php
class ElementPortfolio {
    private ?int $id_element;
    private ?int $id_portfolio;
    private ?string $type_element;
    private ?string $titre;
    private ?string $description;
    private ?string $niveau;
    private ?string $statut;
    private ?string $date_ajout;

    public function __construct(
        ?int $id_element,
        ?int $id_portfolio,
        ?string $type_element,
        ?string $titre,
        ?string $description,
        ?string $niveau = null,
        ?string $statut = null,
        ?string $date_ajout = null
    ) {
        $this->id_element = $id_element;
        $this->id_portfolio = $id_portfolio;
        $this->type_element = $type_element;
        $this->titre = $titre;
        $this->description = $description;
        $this->niveau = $niveau;
        $this->statut = $statut;
        $this->date_ajout = $date_ajout;
    }

    // Getters
    public function getIdElement(): ?int { return $this->id_element; }
    public function getIdPortfolio(): ?int { return $this->id_portfolio; }
    public function getTypeElement(): ?string { return $this->type_element; }
    public function getTitre(): ?string { return $this->titre; }
    public function getDescription(): ?string { return $this->description; }
    public function getNiveau(): ?string { return $this->niveau; }
    public function getStatut(): ?string { return $this->statut; }
    public function getDateAjout(): ?string { return $this->date_ajout; }

    // Setters
    public function setIdElement(?int $id_element): void { $this->id_element = $id_element; }
    public function setIdPortfolio(?int $id_portfolio): void { $this->id_portfolio = $id_portfolio; }
    public function setTypeElement(?string $type_element): void { $this->type_element = $type_element; }
    public function setTitre(?string $titre): void { $this->titre = $titre; }
    public function setDescription(?string $description): void { $this->description = $description; }
    public function setNiveau(?string $niveau): void { $this->niveau = $niveau; }
    public function setStatut(?string $statut): void { $this->statut = $statut; }
    public function setDateAjout(?string $date_ajout): void { $this->date_ajout = $date_ajout; }

    public function show(): void {
        var_dump($this);
    }
}
?>
