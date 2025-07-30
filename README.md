# Unescopilot

Unescopilot est une application web légère et conviviale pour recenser, explorer et suivre les sites du patrimoine mondial de l'UNESCO.

## ✨ Fonctionnalités

- **Gestion des utilisateurs :** Inscription, connexion, et gestion du profil.
- **Exploration des sites UNESCO :** Affichage de la liste complète, détails par site, et fonctionnalités de recherche.
- **Listes personnelles :** Marquez les sites comme "visités" ou "à visiter" pour suivre vos découvertes.

## 🚀 Stack Technique

- **Backend :** [Symfony](https://symfony.com/) 7.3+ (PHP 8.2+)
- **Frontend :** JavaScript vanilla avec [Alpine.js](https://alpinejs.dev/) pour l'interactivité.
- **Styling :** [Tailwind CSS](https://tailwindcss.com/).

## API Backend

L'application communique avec un backend Symfony via une API REST :

- `POST /api/login` - Connexion
- `POST /api/register` - Inscription
- `GET /api/me` - Profil utilisateur
- `PUT /api/me` - Mise à jour du profil
- `GET /api/sites` - Liste des sites (avec filtres)
- `GET /api/sites/{id}` - Détail d'un site
- `GET /api/me/visit` - Visites de l'utilisateur
- `POST /api/me/visit` - Ajouter une visite
- `DELETE /api/me/visit/{site_id}` - Supprimer une visite

## Documentation

Le dossier `doc/` contient les documents suivants :

- [`apidoc.md`](doc/apidoc.md) : Documentation de l'API.
- [`owasp.md`](doc/owasp.md) : Audit de sécurité basé sur l'OWASP Top 10.
- [`specifications.md`](doc/specifications.md) : Spécifications techniques du projet.


## 🛠️ Installation et Lancement

1.  **Clonez le dépôt :**
    ```bash
    git clone https://github.com/votre-utilisateur/unescopilot.git
    cd unescopilot
    ```

2.  **Installez les dépendances PHP :**
    ```bash
    composer install
    ```

3.  **Configurez l'environnement :**
    - Copiez `.env` vers `.env.local` : `cp .env .env.local`
    - Modifiez `.env.local` pour configurer votre base de données (ex: `DATABASE_URL`).

4.  **Base de données et données initiales :**
    ```bash
    # Créez la base de données et appliquez les migrations
    php bin/console doctrine:database:create
    php bin/console doctrine:schema:update --force

    # Importez la liste des sites UNESCO
    php bin/console app:import-sites
    ```

5.  **Lancez le serveur local :**
    ```bash
    symfony server:start
    ```

L'application sera accessible à l'adresse `https://127.0.0.1:8000`.

## ✅ Qualité du Code

Pour garantir la qualité et la stabilité du code, utilisez les commandes suivantes :

- **Lancer les tests unitaires et d'intégration :**
  ```bash
  make tests
  ```

- **Lancer l'analyse statique avec PHPStan :**
  ```bash
  make stan
  ```
  
