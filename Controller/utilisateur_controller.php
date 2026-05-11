<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/utilisateur.php';

class UtilisateurController
{
    public function afficherObject(Utilisateur $u): void
    {
        $u->show();
    }

    public function ajouterUtilisateur(Utilisateur $u): bool
    {
        try {
            $db = config::getConnexion();

            $sql = "INSERT INTO user (
                        email, password, role, statut_compte,
                        nom_entreprise, secteur_activite, adresse, telephone,
                        nom, prenom, domaine, niveau_experience, tarif_journalier
                    ) VALUES (
                        :email, :password, :role, :statut_compte,
                        :nom_entreprise, :secteur_activite, :adresse, :telephone,
                        :nom, :prenom, :domaine, :niveau_experience, :tarif_journalier
                    )";

            $query = $db->prepare($sql);

            return $query->execute([
                'email' => $u->getEmail(),
                'password' => $u->getPassword(),
                'role' => $u->getRole(),
                'statut_compte' => $u->getStatutCompte(),
                'nom_entreprise' => $u->getNomEntreprise(),
                'secteur_activite' => $u->getSecteurActivite(),
                'adresse' => $u->getAdresse(),
                'telephone' => $u->getTelephone(),
                'nom' => $u->getNom(),
                'prenom' => $u->getPrenom(),
                'domaine' => $u->getDomaine(),
                'niveau_experience' => $u->getNiveauExperience(),
                'tarif_journalier' => $u->getTarifJournalier()
            ]);
        } catch (Exception $e) {
            die('Erreur ajout : ' . $e->getMessage());
        }
    }

    public function listeUsers(): array
    {
        try {
            $db = config::getConnexion();
            $sql = "SELECT * FROM user ORDER BY id_user DESC";
            return $db->query($sql)->fetchAll();
        } catch (Exception $e) {
            die('Erreur liste : ' . $e->getMessage());
        }
    }

    public function getUserById(int $id): ?array
    {
        try {
            $db = config::getConnexion();
            $sql = "SELECT * FROM user WHERE id_user = :id";
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            $user = $query->fetch();
            return $user ?: null;
        } catch (Exception $e) {
            die('Erreur récupération : ' . $e->getMessage());
        }
    }

    public function getUserByEmail(string $email): ?array
    {
        try {
            $db = config::getConnexion();
            $sql = "SELECT * FROM user WHERE email = :email";
            $query = $db->prepare($sql);
            $query->execute(['email' => $email]);
            $user = $query->fetch();
            return $user ?: null;
        } catch (Exception $e) {
            die('Erreur récupération : ' . $e->getMessage());
        }
    }

    public function modifierUtilisateur(Utilisateur $u): bool
    {
        try {
            $db = config::getConnexion();

            $sql = "UPDATE user SET
                        email = :email,
                        password = :password,
                        role = :role,
                        statut_compte = :statut_compte,
                        nom_entreprise = :nom_entreprise,
                        secteur_activite = :secteur_activite,
                        adresse = :adresse,
                        telephone = :telephone,
                        nom = :nom,
                        prenom = :prenom,
                        domaine = :domaine,
                        niveau_experience = :niveau_experience,
                        tarif_journalier = :tarif_journalier
                    WHERE id_user = :id_user";

            $query = $db->prepare($sql);

            return $query->execute([
                'id_user' => $u->getIdUser(),
                'email' => $u->getEmail(),
                'password' => $u->getPassword(),
                'role' => $u->getRole(),
                'statut_compte' => $u->getStatutCompte(),
                'nom_entreprise' => $u->getNomEntreprise(),
                'secteur_activite' => $u->getSecteurActivite(),
                'adresse' => $u->getAdresse(),
                'telephone' => $u->getTelephone(),
                'nom' => $u->getNom(),
                'prenom' => $u->getPrenom(),
                'domaine' => $u->getDomaine(),
                'niveau_experience' => $u->getNiveauExperience(),
                'tarif_journalier' => $u->getTarifJournalier()
            ]);
        } catch (Exception $e) {
            die('Erreur modification : ' . $e->getMessage());
        }
    }

    public function supprimerUtilisateur(int $id): bool
    {
        try {
            $db = config::getConnexion();
            $sql = "DELETE FROM user WHERE id_user = :id";
            $query = $db->prepare($sql);
            return $query->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Erreur suppression : ' . $e->getMessage());
        }
    }
}