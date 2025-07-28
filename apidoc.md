# API Documentation - UnescopilotGemini

Documentation technique de l'API REST pour le développement frontend.

## Authentification

L'API utilise une authentification par token Bearer. 

**Headers requis pour les endpoints protégés :**
```
Authorization: Bearer <token>
Content-Type: application/json
```

---

## Endpoints d'authentification

### POST `/api/security/register`
Inscription d'un nouvel utilisateur.

**Body :**
```json
{
  "email": "user@example.com",
  "pseudo": "username",
  "password": "password123"
}
```

**Réponse succès (201) :**
```json
{
  "message": "User registered successfully",
  "user": {
    "id": 1,
    "email": "user@example.com",
    "pseudo": "username"
  }
}
```

**Erreurs :**
- `409` : Email déjà utilisé
- `400` : Erreurs de validation

### POST `/api/security/login`
Connexion utilisateur.

**Body :**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Réponse succès (200) :**
```json
{
  "token": "abc123...",
  "created_at": "2024-01-15T10:30:00+00:00",
  "expires_at": "2024-02-14T10:30:00+00:00",
  "user": {
    "id": 1,
    "email": "user@example.com",
    "pseudo": "username"
  }
}
```

**Erreurs :**
- `401` : Identifiants invalides

### POST `/api/security/logout` 🔒
Déconnexion utilisateur.

**Réponse (200) :**
```json
{
  "message": "Logged out successfully"
}
```

---

## Endpoints utilisateur

### GET `/api/me` 🔒
Récupère les informations de l'utilisateur connecté.

**Réponse (200) :**
```json
{
  "user": {
    "id": 1,
    "email": "user@example.com",
    "pseudo": "username"
  }
}
```

---

## Endpoints des sites UNESCO

### GET `/api/sites`
Liste des sites UNESCO avec filtrage optionnel.

**Paramètres de requête (optionnels) :**
- `q` : Recherche générale dans nom et pays
- `name` : Recherche par nom
- `country` : Filtrage par pays
- `category` : Filtrage par catégorie ("Cultural", "Natural", "Mixed")
- `page` : Numéro de page (défaut: 1)
- `limit` : Nombre d'éléments par page (défaut: 20)
- `minLat`, `maxLat`, `minLon`, `maxLon` : Délimitation géographique

**Exemples :** 
- `/api/sites?q=paris` : Recherche "paris" dans nom et pays
- `/api/sites?name=tour&category=Cultural&page=1&limit=10`

**Réponse (200) :**
```json
[
  {
    "id": 83,
    "name": "Palace and Park of Versailles",
    "category": "Cultural",
    "shortDescription": "Description du site...",
    "httpUrl": "https://whc.unesco.org/en/list/83/",
    "imageUrl": "https://whc.unesco.org/uploads/sites/site_83.jpg",
    "latitude": 48.8049,
    "longitude": 2.1204,
    "dateInscribed": 1979,
    "states": "France",
    "transboundary": false,
    "criteriaTxt": "(i)(ii)(vi)",
    "isoCode": "FR",
    "location": "Île-de-France",
    "region": "Europe and North America"
  }
]
```

### GET `/api/sites/{id}`
Détail d'un site spécifique.

**Réponse (200) :** Même structure qu'un élément de la liste
**Erreurs :**
- `404` : Site non trouvé

---

## Endpoints de gestion des visites

🔒 **Tous les endpoints des visites nécessitent une authentification**

### GET `/api/visits`
Liste toutes les visites de l'utilisateur connecté.

**Tri :** Par date de visite décroissante, puis par ID décroissant

**Réponse (200) :**
```json
[
  {
    "id": 1,
    "user": { "id": 1, "pseudo": "username" },
    "site": {
      "id": 83,
      "name": "Palace and Park of Versailles",
      "category": "Cultural"
    },
    "type": "visited",
    "visitedAt": 2023
  }
]
```

### GET `/api/visits/wishlist`
Liste des sites en liste de souhaits de l'utilisateur.

**Réponse (200) :** Même structure que `/api/visits` mais filtrée sur `type: "wishlist"`

### GET `/api/visits/visited`
Liste des sites visités par l'utilisateur.

**Réponse (200) :** Même structure que `/api/visits` mais filtrée sur `type: "visited"`

### POST `/api/visits`
Créer une nouvelle visite.

**Body :**
```json
{
  "siteId": 83,
  "type": "wishlist",
  "visitedAt": 2023
}
```

**Champs :**
- `siteId` (obligatoire) : ID du site UNESCO
- `type` (obligatoire) : "visited" ou "wishlist"
- `visitedAt` (optionnel) : Année de visite (1900-2100)

**Réponse succès (201) :**
```json
{
  "id": 1,
  "user": { "id": 1, "pseudo": "username" },
  "site": { "id": 83, "name": "Palace and Park of Versailles" },
  "type": "wishlist",
  "visitedAt": 2023
}
```

**Erreurs :**
- `404` : Site non trouvé
- `409` : Visite déjà existante pour ce site

### GET `/api/visits/{id}`
Détail d'une visite spécifique.

**Réponse (200) :** Structure complète de la visite
**Erreurs :**
- `403` : Accès refusé (visite d'un autre utilisateur)
- `404` : Visite non trouvée

### PUT `/api/visits/{id}`
Modifier une visite existante.

**Body :**
```json
{
  "type": "visited",
  "visitedAt": 2024
}
```

**Champs (tous optionnels) :**
- `type` : "visited" ou "wishlist"  
- `visitedAt` : Année de visite

**Réponse (200) :** Structure complète de la visite mise à jour
**Erreurs :**
- `403` : Accès refusé
- `404` : Visite non trouvée

### DELETE `/api/visits/{id}`
Supprimer une visite.

**Réponse (204) :** Aucun contenu
**Erreurs :**
- `403` : Accès refusé
- `404` : Visite non trouvée

### GET `/api/visits/site/{id}`
Récupérer la visite de l'utilisateur pour un site spécifique.

**Réponse (200) :** Structure complète de la visite
**Erreurs :**
- `404` : Aucune visite trouvée pour ce site

---

## Types de données

### VisitType
```typescript
type VisitType = "visited" | "wishlist"
```

### Site
```typescript
interface Site {
  id: number
  name: string
  category: "Cultural" | "Natural" | "Mixed"
  shortDescription?: string
  httpUrl: string
  imageUrl: string
  latitude: number
  longitude: number
  dateInscribed: number
  states: string
  transboundary: boolean
  criteriaTxt?: string
  isoCode?: string
  location?: string
  region?: string
}
```

### Visit
```typescript
interface Visit {
  id: number
  user: {
    id: number
    pseudo: string
  }
  site: Site
  type: VisitType
  visitedAt?: number  // Année
}
```

### User
```typescript
interface User {
  id: number
  email: string
  pseudo: string
}
```

---

## Codes d'erreur

| Code | Description                           |
|------|---------------------------------------|
| 200  | Succès                                |
| 201  | Créé avec succès                      |
| 204  | Suppression réussie (pas de contenu)  |
| 400  | Erreur de validation                  |
| 401  | Non authentifié                       |
| 403  | Accès refusé                          |
| 404  | Ressource non trouvée                 |
| 409  | Conflit (ressource déjà existante)    |
| 500  | Erreur serveur                        |
