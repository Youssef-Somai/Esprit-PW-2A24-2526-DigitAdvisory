# Module 5 — Gestion des Missions & Projets
## Architecture MVC | PDO | PHP

---

## 📁 Structure du projet

```
module5/
├── index.php                   ← Router principal (Front Controller)
├── config/
│   ├── database.php            ← Connexion PDO (Singleton)
│   └── schema.sql              ← Script SQL à importer
├── Model/
│   ├── Mission.php             ← Modèle Mission (CRUD PDO)
│   └── Livrable.php            ← Modèle Livrable (CRUD PDO)
├── Controller/
│   └── MissionController.php  ← Contrôleur + validation serveur
└── View/
    └── mission/
        ├── front_list.php      ← Liste des missions (Front Office)
        ├── front_detail.php    ← Détail mission + livrables
        ├── back_list.php       ← Dashboard admin (Back Office)
        └── back_form.php       ← Formulaire créer/modifier
```

---

## 🚀 Installation

### 1. Base de données
```sql
-- Dans phpMyAdmin ou MySQL CLI :
SOURCE /path/to/module5/config/schema.sql;
```

### 2. Configuration PDO
Éditez `config/database.php` avec vos identifiants :
```php
private $host = 'localhost';
private $db   = 'consulting_db';
private $user = 'root';
private $pass = '';
```

### 3. Template Eduleb
Copiez le dossier `assets/` de votre template Eduleb dans `module5/`.

### 4. Démarrer
Placez le dossier `module5/` dans votre `htdocs/` (XAMPP) ou `www/` (WAMP), puis accédez à :
```
http://localhost/module5/
```

---

## 🌐 URLs disponibles

| URL | Description |
|-----|-------------|
| `index.php` | Front Office — liste des missions |
| `index.php?action=front_list` | Liste des missions |
| `index.php?action=front_detail&id=1` | Détail + livrables d'une mission |
| `index.php?action=back_list` | Back Office — dashboard admin |
| `index.php?action=back_create` | Créer une nouvelle mission |
| `index.php?action=back_edit&id=1` | Modifier une mission |
| `index.php?action=back_delete&id=1` | Supprimer une mission |

---

## ✅ Contraintes respectées

- ✅ **MVC** : Model / View / Controller séparés
- ✅ **PDO** : Toutes les requêtes utilisent PDO avec requêtes préparées
- ✅ **POO** : Classes Mission, Livrable, MissionController, Database
- ✅ **Validation JS** : Aucun attribut HTML5 (required, pattern, type=email...) — tout est validé en JavaScript
- ✅ **Validation PHP** : Double validation côté serveur
- ✅ **Templates** : Front Office (Eduleb) + Back Office (Dashboard)
- ✅ **CRUD complet** : Create, Read, Update, Delete sur Mission + Livrable
