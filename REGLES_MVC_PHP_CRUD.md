# CAHIER DE CHARGE — RÈGLES PHP / MVC / CRUD

Source : Projet Technologies Web 2A (Partie III PHP)

---

# 1. ARCHITECTURE MVC (OBLIGATOIRE)

## Structure du projet

- Model/
- View/
- Controller/

## Organisation

- Model : classes de données
- View : interfaces utilisateur (FrontOffice / BackOffice)
- Controller : logique métier + liaison Model/View

---

# 2. MODÈLE (MODEL)

## Classe

- définir tous les attributs
- créer méthode show()

## Version avancée

- attributs privés
- constructeur paramétré
- getters / setters

---

# 3. AFFICHAGE

- créer un fichier View
- créer un objet
- afficher avec :
  - var_dump()
  - show()

---

# 4. CONTROLLER

## Classe

- créer dans Controller

## Méthode

- showObject($object)

---

# 5. FORMULAIRE

- méthode POST
- envoi vers fichier traitement

## Traitement

- récupérer données
- créer objet
- afficher :
  - var_dump()
  - controller

---

# 6. BASE DE DONNÉES

- créer base
- créer table
- id clé primaire
- insérer données initiales

---

# 7. CONFIGURATION PDO

## config.php

- classe config
- méthode getConnexion()

## Obligatoire

- PDO
- ERRMODE_EXCEPTION
- FETCH_ASSOC

---

# 8. CRUD

## READ

- listObjects()

## DELETE

- deleteObject($id)
- lien delete
- redirection

## CREATE

- addObject($object)
- redirection

## UPDATE

- bouton update
- updateObject.php
- modifier + enregistrer

---

# 9. RÈGLES

- respecter MVC
- utiliser POST
- utiliser PDO
- redirection après CRUD
- utiliser objets

---

# FIN
