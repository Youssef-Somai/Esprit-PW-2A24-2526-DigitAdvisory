<?php
class Portfolio {
    private ?int $id_portfolio;
    private ?string $titre_portfolio;
    private ?string $description_portfolio;
    private ?string $date_creation;
    private ?string $date_modification;

    public function __construct(?int $id_portfolio, ?string $titre_portfolio, ?string $description_portfolio, ?string $date_creation = null, ?string $date_modification = null) {
        $this->id_portfolio = $id_portfolio;
        $this->titre_portfolio = $titre_portfolio;
        $this->description_portfolio = $description_portfolio;
        $this->date_creation = $date_creation;
        $this->date_modification = $date_modification;
    }

    public function getIdPortfolio(): ?int { return $this->id_portfolio; }
    public function getTitrePortfolio(): ?string { return $this->titre_portfolio; }
    public function getDescriptionPortfolio(): ?string { return $this->description_portfolio; }
    public function getDateCreation(): ?string { return $this->date_creation; }
    public function getDateModification(): ?string { return $this->date_modification; }

    public function setIdPortfolio(?int $id_portfolio): void { $this->id_portfolio = $id_portfolio; }
    public function setTitrePortfolio(?string $titre_portfolio): void { $this->titre_portfolio = $titre_portfolio; }
    public function setDescriptionPortfolio(?string $description_portfolio): void { $this->description_portfolio = $description_portfolio; }
    public function setDateCreation(?string $date_creation): void { $this->date_creation = $date_creation; }
    public function setDateModification(?string $date_modification): void { $this->date_modification = $date_modification; }

    public function show(): void {
        var_dump($this);
    }
}
?>
