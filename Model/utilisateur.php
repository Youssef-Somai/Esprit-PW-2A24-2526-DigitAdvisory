<?php

class Utilisateur
{
    private ?int $id_user;
    private string $email;
    private string $password;
    private string $role;
    private string $statut_compte;

    private ?string $nom_entreprise;
    private ?string $secteur_activite;
    private ?string $adresse;
    private ?string $telephone;

    private ?string $nom;
    private ?string $prenom;
    private ?string $domaine;
    private ?string $niveau_experience;
    private ?float $tarif_journalier;

    public function __construct(
        ?int $id_user,
        string $email,
        string $password,
        string $role,
        string $statut_compte = 'actif',
        ?string $nom_entreprise = null,
        ?string $secteur_activite = null,
        ?string $adresse = null,
        ?string $telephone = null,
        ?string $nom = null,
        ?string $prenom = null,
        ?string $domaine = null,
        ?string $niveau_experience = null,
        ?float $tarif_journalier = null
    ) {
        $this->id_user = $id_user;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->statut_compte = $statut_compte;
        $this->nom_entreprise = $nom_entreprise;
        $this->secteur_activite = $secteur_activite;
        $this->adresse = $adresse;
        $this->telephone = $telephone;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->domaine = $domaine;
        $this->niveau_experience = $niveau_experience;
        $this->tarif_journalier = $tarif_journalier;
    }

    public function getIdUser(): ?int { return $this->id_user; }
    public function getEmail(): string { return $this->email; }
    public function getPassword(): string { return $this->password; }
    public function getRole(): string { return $this->role; }
    public function getStatutCompte(): string { return $this->statut_compte; }

    public function getNomEntreprise(): ?string { return $this->nom_entreprise; }
    public function getSecteurActivite(): ?string { return $this->secteur_activite; }
    public function getAdresse(): ?string { return $this->adresse; }
    public function getTelephone(): ?string { return $this->telephone; }

    public function getNom(): ?string { return $this->nom; }
    public function getPrenom(): ?string { return $this->prenom; }
    public function getDomaine(): ?string { return $this->domaine; }
    public function getNiveauExperience(): ?string { return $this->niveau_experience; }
    public function getTarifJournalier(): ?float { return $this->tarif_journalier; }

    public function show(): void
    {
        echo "<pre>";
        var_dump($this);
        echo "</pre>";
    }
}