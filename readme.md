#  Conception du projet CRM Tornis

## 1. Présentation du projet
- TP de la formation en dévéloppement PHP.  

Le projet **CRM Tornis** consiste à développer une application web de gestion commerciale permettant à la startup Tornis de :

- gérer ses clients ;
- gérer ses produits ;
- enregistrer les commandes ;
- consulter l’historique des achats ;
- suivre les montants générés.

Pour sécuriser l’application CRM, on ajoute une gestion des utilisateurs avec authentification.
Avec cette partie, les étudiants apprennent :
- la gestion des utilisateurs
- la sécurité des mots de passe
- le fonctionnement des sessions
- la protection des routes/pages
- les bases de l’authentification web

Ce projet est destiné à consolider les compétences des apprenants en PHP et MySQL.



## 2. Objectifs pédagogiques

À travers ce projet, les étudiants apprendront à :

- concevoir une base de données relationnelle ;
- créer des tables avec clés primaires et étrangères ;
- réaliser des opérations CRUD ;
- manipuler des formulaires HTML ;
- utiliser PHP pour traiter les données ;
- écrire des requêtes SQL avec jointures ;
- structurer un projet web.


## 3. Règles de gestion
- Un email client doit être unique
- Un produit ne peut pas avoir un prix négatif
- Une commande doit contenir au moins une ligne
- La quantité doit être supérieure à 0
- Le total = somme des lignes
- Le stock diminue après commande


## 4. Modèle de données

### Table `users`

Cette table permet de gérer les comptes des utilisateurs (administrateurs du CRM).

| Champ       | Type             | Description |
|------------|------------------|------------|
| id         | INT (PK)         | Identifiant |
| fistname       | VARCHAR(100)     | Prenom |
| lastname       | VARCHAR(100)     | Nom |
| email      | VARCHAR(150)     | Email (unique) |
| password   | VARCHAR(255)     | Mot de passe (hashé) |
| role       | VARCHAR(50)      | Rôle (admin, user) |
| avatar_url | VARCHAR(255)     | url de l'avatar |
| created_at | DATETIME         | Date de création |


### Table `customers`

| Champ       | Type             | Description |
|------------|------------------|------------|
| id         | INT (PK)         | Identifiant unique |
| name       | VARCHAR(100)     | Nom du client |
| email      | VARCHAR(150)     | Email unique |
| tel        | VARCHAR(20)      | Téléphone |
| address    | VARCHAR(255)     | Adresse |
| is_active  | BOOLEAN          | Activité |
| created_at | DATETIME         | Date de création |


### Table `products`

| Champ       | Type             | Description |
|------------|------------------|------------|
| id         | INT (PK)         | Identifiant |
| name       | VARCHAR(150)     | Nom du produit |
| description| TEXT             | Description |
| price      | DECIMAL(10,2)    | Prix |
| stock      | INT              | Quantité disponible |
| created_at | DATETIME         | Date d’ajout |


### Table `orders`

| Champ         | Type             | Description |
|--------------|------------------|------------|
| id           | INT (PK)         | Identifiant |
| customer_id  | INT (FK)         | Client |
| order_date   | DATETIME         | Date commande |
| status       | VARCHAR(30)      | Statut |
| total_amount | DECIMAL(10,2)    | Total |
| created_at   | DATETIME         | Création |


### Table `order_items`

| Champ       | Type             | Description |
|------------|------------------|------------|
| id         | INT (PK)         | Identifiant |
| order_id   | INT (FK)         | Commande |
| product_id | INT (FK)         | Produit |
| quantity   | INT              | Quantité |
| unit_price | DECIMAL(10,2)    | Prix au moment |
| line_total | DECIMAL(10,2)    | Total ligne |



## 5. Relations

- Un client → plusieurs commandes
- Une commande → plusieurs lignes
- Un produit → plusieurs lignes de commande



## 6. Fonctionnalités attendues
### Clients
- Ajouter un nouveau client
- Modifier les infos d'un client
- Supprimer un client
- Lister les clients

### Produits
- Ajouter un nouveau produit
- Modifier un produit
- Supprimer un produit
- Lister les produits

### Commandes
- Créer une commande
- Ajouter plusieurs produits
- Calculer total
- Afficher historique
- Voir détail

### Fonctionnalités avancées
- Recherche client
- Filtre commandes
- Dashboard
- Gestion du stock
- Messages de succès


## 7. Structure du projet
```
crm/
│
├── auth/
│   ├── login.php
│   ├── authenticate.php
│   ├── logout.php
|
├── config/ # Connexion à la base de données
│ └── db.php
│
├── customers/ # Gestion des clients
│ ├── index.php
│ ├── create.php
│ ├── store.php
│ ├── edit.php
│ ├── update.php
│ └── delete.php
|
├── users/ # Gestion des utilisateurs
│ ├── index.php
│ ├── create.php
│ ├── store.php
│ ├── edit.php
│ ├── update.php
│ └── delete.php
│
├── products/ # Gestion des produits
│ ├── index.php
│ ├── create.php
│ ├── store.php
│ ├── edit.php
│ ├── update.php
│ └── delete.php
│
├── orders/ # Gestion des commandes
│ ├── index.php
│ ├── create.php
│ ├── store.php
│ ├── show.php
│ ├── update_status.php
│ └── delete.php
│
├── partials/ # Header / Footer / Navbar
│ ├── header.php
│ ├── footer.php
│ └── navbar.php
│
├── stats/  # Statisques
| └──stats_global.php
│
├── assets/ # CSS / JS / images
│ ├── css/
│ ├── js/
│ └── images/
│
└── index.php # Page d'accueil
```
