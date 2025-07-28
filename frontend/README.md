# Frontend Unescopilot

Interface utilisateur pour l'application Unescopilot - Exploration du patrimoine mondial UNESCO.

## Structure du projet

```
frontend/
├── index.html              # Page d'accueil
├── components/             # Composants réutilisables
│   ├── header.html        # En-tête avec navigation
│   └── footer.html        # Pied de page
├── pages/                 # Pages de l'application
│   ├── login.html         # Page de connexion
│   ├── register.html      # Page d'inscription
│   ├── sites.html         # Liste des sites UNESCO
│   ├── site-detail.html   # Détail d'un site
│   └── profile.html       # Profil utilisateur
└── assets/
    └── js/
        └── app.js         # Logique JavaScript globale
```

## Technologies utilisées

- **HTML5** - Structure sémantique
- **Tailwind CSS** (via CDN) - Framework CSS utilitaire
- **Alpine.js** (via CDN) - Framework JavaScript réactif
- **JavaScript ES6+** - Logique métier côté client

## Fonctionnalités

### Pages publiques
- **Page d'accueil** (`index.html`) - Présentation de l'application
- **Liste des sites** (`pages/sites.html`) - Exploration des sites UNESCO avec recherche et filtrage
- **Détail d'un site** (`pages/site-detail.html`) - Informations complètes sur un site

### Authentification
- **Connexion** (`pages/login.html`) - Authentification utilisateur
- **Inscription** (`pages/register.html`) - Création de compte

### Espace utilisateur (authentifié)
- **Profil** (`pages/profile.html`) - Gestion du profil et statistiques personnelles
- **Suivi des visites** - Marquer les sites comme "visités" ou "à visiter"

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

## Authentification

- Système de tokens Bearer stockés dans `localStorage`
- Gestion automatique de l'expiration et de la déconnexion
- Navigation conditionnelle selon l'état d'authentification

## Développement local

1. Servir les fichiers via un serveur HTTP local :
   ```bash
   # Avec Python
   python -m http.server 8080
   
   # Avec Node.js (http-server)
   npx http-server -p 8080
   ```

2. Accéder à l'application : `http://localhost:8080`

3. S'assurer que le backend Symfony est démarré sur `http://localhost:8000`

## Design mobile-first

L'interface est conçue avec une approche mobile-first utilisant les classes responsives de Tailwind CSS :

- Navigation adaptative avec menu burger sur mobile
- Grilles flexibles (`grid-cols-1 md:grid-cols-2 lg:grid-cols-3`)
- Espacements et tailles adaptés selon la taille d'écran

## Architecture MPA (Multi-Page Application)

- Chaque page est un fichier HTML distinct
- Navigation via liens standards (rechargement de page)
- Composants partagés (header, footer) chargés dynamiquement via Alpine.js
- État global géré via `window.appState`

## Composants réutilisables

Les composants sont chargés dynamiquement via la fonction `loadComponent()` :

```javascript
await window.components.loadComponent('header', '../components/header.html');
```

## État global

L'application maintient un état global accessible via `window.appState` :

```javascript
window.appState = {
  isAuthenticated: false,
  user: null,
  token: null,
  mobileMenuOpen: false
};
```

## Prochaines améliorations

- Système de notifications toast
- Mise en cache des données
- Mode hors-ligne basique
- Optimisation des images
- Tests unitaires