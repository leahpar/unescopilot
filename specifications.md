# Cahier des Charges Général de "Unescopilot"

## 1. Introduction

Ce document présente le cahier des charges général de l'application web "Unescopilot". L'objectif est de créer une application légère et conviviale pour recenser, explorer et suivre les sites du patrimoine mondial de l'UNESCO. L'application est destinée à un usage non commercial et met l'accent sur la simplicité de développement et la facilité d'utilisation, avec une approche "entre amis".

## 2. Objectifs de l'Application

- **Recenser les sites UNESCO :** Permettre la consultation d'une base de données des sites du patrimoine mondial.

- **Suivi personnalisé :** Offrir aux utilisateurs la possibilité d'enregistrer les sites qu'ils ont visités ou qu'ils souhaitent visiter.

- **Fonctionnalités sociales (futures) :** Prévoir l'intégration ultérieure de classements, partages et connexions entre amis.


## 3. Architecture Technique

Afin de maintenir la simplicité et d'exploiter les compétences existantes, l'architecture sera divisée en deux composants principaux : un backend API et un frontend léger.

### 3.1. Backend

- **Technologie principale :** **Symfony**

- **Langue :** **Anglais** pour le code, commentaires et documentation.

- **Rôle :** Exposer une **API RESTful** pour la gestion des données (sites, utilisateurs, listes de souhaits/visités) et la logique métier.

- **Format de données :** **JSON** pour toutes les communications API.

- - **Authentification :**
    - Utilisation d'un **simple token** pour l'authentification.
    - Lors de la connexion, le backend générera un token unique et persistant pour l'utilisateur.
    - Ce token devra être inclus dans l'en-tête `Authorization: Bearer <token>` pour toutes les requêtes authentifiées.
    - L'API restera stateless ; la validité du token sera vérifiée à chaque requête.

- **Qualité du code :**
    - Mise en place de **tests unitaires** pour la logique métier et les composants.
    - Rédaction de **tests d'intégration/endpoints API** pour vérifier le bon fonctionnement des routes.
    - Utilisation de **PHPStan** pour l'analyse statique du code.

### 3.2. Frontend

- **Technologie principale :** HTML, CSS, JavaScript vanille.

- **Langue :** **Français** pour l'interface utilisateur.

- **Framework CSS :** **Tailwind CSS** sera utilisé pour la stylisation de l'interface utilisateur. L'intégration se fera via le CDN pour maintenir la simplicité et éviter un processus de build CSS complexe.

- **Interactivité :** **Alpine.js** pour ajouter du dynamisme et de l'interactivité.

- **Structure des pages :** Application **Multipage (MPA) hybride**.

    - Chaque "page" principale de l'application (ex: `login.html`, `register.html`, `sites.html`, `site-detail.html`, `profile.html`) sera un fichier HTML distinct.
    - La navigation entre ces pages se fera via des liens HTML standards, entraînant un rechargement complet de la page.

- **Éléments réutilisables (Header, Menu, Footer) :**

    - Ces éléments seront gérés via des **inclusions JavaScript côté client en utilisant Alpine.js**.
    - Des fichiers HTML distincts (ex: `/components/header.html`, `/components/menu.html`) contiendront le markup de ces composants.
    - Chaque page HTML principale utilisera une directive Alpine.js (`x-init` avec `fetch`) pour injecter le contenu de ces fichiers dans des placeholders dédiés.

- **Communication API :** Utilisation de l'API `fetch` pour toutes les requêtes HTTP vers le backend.

- **Stockage du Token :** Le token d'authentification sera stocké dans le `localStorage` du navigateur.

## 4. Fonctionnalités Initiales (MVP - Minimum Viable Product)

- **Gestion des utilisateurs :**

    - Inscription d'un nouvel utilisateur.
    - Connexion / Déconnexion.
    - Consultation et modification basique du profil utilisateur.

- **Consultation des sites UNESCO :**

    - Affichage d'une liste de tous les sites.
    - Détail d'un site (nom, description, pays, etc.).
    - Recherche et filtrage des sites (par nom, pays, catégorie).

- **Gestion des listes personnelles :**

    - Marquer un site comme "Visité".
    - Marquer un site comme "À visiter".
    - Afficher la liste des sites "Visités" par l'utilisateur.
    - Afficher la liste des sites "À visiter" par l'utilisateur.

## 5. Modèles de Données (Schémas JSON)

- **Site UNESCO (Site) :** Définition de la structure des données pour un site du patrimoine mondial.
- **Utilisateur (User) :** Définition de la structure des données pour un utilisateur de l'application.
- **Statut de Visite (VisitStatus) :** Définition de la structure des données pour le suivi des sites visités ou à visiter par un utilisateur.

_(Ces schémas seront détaillés dans une phase ultérieure des spécifications techniques.)_

## 5.1 Importation des Données UNESCO

- Un **traitement d'importation initial** devra être mis en place côté backend.
- Ce traitement sera chargé de parser les données XML des sites UNESCO. Le lien vers les données sera fourni.

## 6. Points d'API (Endpoints)

- **Authentification :** Routes pour l'inscription et la connexion des utilisateurs.
- **Sites UNESCO :** Routes pour récupérer la liste et les détails des sites.
- **Profil Utilisateur :** Routes pour consulter et modifier le profil de l'utilisateur connecté.
- **Statuts de Visite (Listes personnelles) :** Routes pour gérer les listes de sites visités et à visiter.

_(Ces points d'API seront détaillés avec leurs requêtes et réponses spécifiques dans une phase ultérieure des spécifications techniques.)_

## 7. Authentification API

Utilisation d'un **simple token** pour sécuriser les requêtes des utilisateurs authentifiés. Le token sera envoyé dans l'en-tête `Authorization`.

## 8. Considérations Futures

- **Gamification :** Classements, badges, points.
- **Partage social :** Partage de listes ou de sites visités.
- **Amis :** Connexion avec d'autres utilisateurs, voir leurs listes.

## 9.Liste des endpoints API

## 1. Authentification

- `POST /api/register`
- `POST /api/login`

## 2. Sites UNESCO

- `GET /api/sites`
- `GET /api/sites/{id}`

## 3. Profil Utilisateur

- `GET /api/me`
- `PUT /api/me`

## 4. Statuts de Visite (Listes personnelles)

- `GET /api/me/visit`
- `POST /api/me/visit`
- `DELETE /api/me/visit/{site_id}`

## 10. Données Initiales 

Exemple de données fournies pour l'importation initiale des sites UNESCO.
(cf https://whc.unesco.org/fr/list/xml/)

```xml
<query>
    <row>
        <category>Natural</category>
        <criteria_txt>(ix)</criteria_txt>
        <danger/>
        <date_inscribed>2007</date_inscribed>
        <extension>0</extension>
        <http_url>https://whc.unesco.org/fr/list/1133</http_url>
        <id_number>1133</id_number>
        <image_url>https://whc.unesco.org/uploads/sites/site_1133.jpg</image_url>
        <iso_code>al,at,be,ba,bg,hr,cz,fr,de,it,pl,ro,sk,si,es,ch,mk,ua</iso_code>
        <justification/>
        <latitude>48.9000000000</latitude>
        <location/>
        <longitude>22.1833333333</longitude>
        <region>Europe et Amérique du Nord</region>
        <revision>0</revision>
        <secondary_dates>2011,2017,2021</secondary_dates>
        <short_description><p>Ce bien transnational, composé de 93 éléments constitutifs, s’étend sur 18 pays. Depuis la fin de la dernière période glaciaire, le hêtre d’Europe s’est répandu à partir de quelques refuges isolés dans les Alpes, les Carpates, les Dinarides, la Méditerranée et les Pyrénées, en l’espace de quelques milliers d’années, un processus qui se poursuit encore aujourd’hui. Le succès de la progression du hêtre s’explique par son adaptabilité et sa tolérance à différentes conditions climatiques, géographiques et physiques.</p></short_description>
        <site>Forêts primaires et anciennes de hêtres des Carpates et d’autres régions d’Europe</site>
        <states>Albanie,Autriche,Belgique,Bosnie-Herzégovine,Bulgarie,Croatie,Tchéquie,France,Allemagne,Italie,Pologne,Roumanie,Slovaquie,Slovénie,Espagne,Suisse,Macédoine du Nord,Ukraine</states>
        <transboundary>1</transboundary>
        <unique_number>2513</unique_number>
    </row>
    <row>...</row>
    <row>...</row>
    <row>...</row>
</query>
```
