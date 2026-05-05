# PPE Puy du Fou — V1

**Projet** : SIO2 SLAM — AP3 « Visite au Puy du Fou »  
**Client** : Groupe OXAM / Parc Puy du Fou  
**Version** : 1.0

Application de planification de visite pour le Parc du Puy du Fou. Le visiteur sélectionne ses spectacles sur Android ; le moteur calcule automatiquement les parcours optimisés en tenant compte des horaires, des distances et de sa vitesse de marche. Le gestionnaire administre le parc via un back-office web.

---

## Architecture

```
┌────────────────────────┐        ┌────────────────────────┐
│  Application Android   │        │  Back-office Web       │
│  (Java — Visiteur)     │        │  (PHP MVC — Gestionnaire)
└───────────┬────────────┘        └───────────┬────────────┘
            │  HTTP/JSON + JWT               │  HTTP/JSON + Session
            └──────────────┬─────────────────┘
                           ▼
               ┌───────────────────────┐
               │  API REST PHP (MVC)   │
               │  Auth · CRUD · Moteur │
               │  de calcul parcours   │
               └───────────┬───────────┘
                           ▼
                    ┌─────────────┐
                    │   MySQL     │
                    └─────────────┘
```

| Brique | Technologie | Rôle |
|---|---|---|
| **API REST** | PHP 8.x · MVC maison · PDO · JWT HS256 | Source de vérité, moteur de calcul |
| **Back-office** | PHP 8.x · MVC maison · cURL · Sessions | Interface de gestion (gestionnaire) |
| **Android** | Java · Gradle · OSMDroid · EncryptedSharedPreferences | Application visiteur |
| **Base de données** | MySQL 8 · utf8mb4 | Persistance des données |

---

## Prérequis

### Backend & Back-office
- [Laragon](https://laragon.org/) (Apache + PHP 8.x + MySQL 8) **ou** tout stack LAMP/WAMP équivalent
- Extensions PHP : `pdo_mysql`, `curl`, `openssl`, `mbstring`

### Android
- Android Studio Hedgehog+
- Android SDK API 24+ (Android 8.0 minimum)
- JDK 11

---

## Installation

### 1. Base de données

```sql
-- Depuis MySQL Workbench, phpMyAdmin ou le terminal MySQL :
SOURCE /chemin/vers/V1/db/ppe_puy_du_fou.sql;
```

Le fichier SQL crée la base `ppe_puy_du_fou` avec le schéma complet et les données de test (11 spectacles, 40 lieux, 4 jours d'ouverture, 36 séances, comptes visiteur + gestionnaire).

> **Comptes de test**
> | Rôle | Email | Mot de passe |
> |---|---|---|
> | Visiteur | `visiteur@test.com` | `password123` |
> | Gestionnaire | `admin@test.com` | `admin123` |

### 2. API REST

Placer le dossier `V1/` dans la racine web de Laragon (`C:\laragon\www\`).

Vérifier/ajuster `api/config/config.php` :

```php
'db' => [
    'host'     => '127.0.0.1',
    'port'     => 3306,
    'database' => 'ppe_puy_du_fou',
    'username' => 'root',
    'password' => '',          // mot de passe MySQL Laragon (vide par défaut)
],
'jwt' => [
    'secret' => 'change-me-in-production-please',   // changer en prod
    'expires_in' => 86400,     // 24 h
],
```

URL de l'API : `http://localhost/ppe-puy-du-fou/V1/api/public`

Tester : `GET http://localhost/ppe-puy-du-fou/V1/api/public/health` → `{"status":"ok"}`

### 3. Back-office Web

URL : `http://localhost/ppe-puy-du-fou/V1/backoffice/public`

La configuration pointe automatiquement vers l'API locale (`backoffice/config/config.php`). Se connecter avec le compte `gestionnaire` ci-dessus.

### 4. Application Android

1. Ouvrir le dossier `V1/android/` dans Android Studio.
2. Laisser Gradle synchroniser les dépendances.
3. Vérifier l'URL de l'API dans `app/src/main/res/values/strings.xml` :
   ```xml
   <!-- Émulateur (localhost → 10.0.2.2) -->
   <string name="api_base_url">http://10.0.2.2/ppe-puy-du-fou/V1/api/public</string>
   <!-- Appareil physique → remplacer par l'IP LAN de votre machine -->
   ```
4. Lancer sur émulateur (API 24+) ou appareil physique.

---

## Endpoints API

### Publics (sans authentification)

| Méthode | Route | Description |
|---|---|---|
| `GET` | `/health` | Santé de l'API |
| `POST` | `/auth/register` | Créer un compte visiteur |
| `POST` | `/auth/login` | Authentification → JWT |
| `GET` | `/spectacles` | Liste des spectacles |
| `GET` | `/spectacles/{id}` | Détail d'un spectacle |
| `GET` | `/spectacles/{id}/seances` | Séances d'un spectacle |
| `GET` | `/lieux` | Liste des lieux |
| `GET` | `/lieux/{id}` | Détail d'un lieu |
| `GET` | `/distances` | Graphe des distances |
| `GET` | `/seances` | Séances (filtrable par date) |
| `GET` | `/jours` | Jours d'ouverture du parc |

### Visiteur (JWT requis — rôle `visiteur`)

| Méthode | Route | Description |
|---|---|---|
| `GET` | `/auth/me` | Profil de l'utilisateur connecté |
| `PUT` | `/auth/vitesse` | Mettre à jour la vitesse de marche |
| `POST` | `/parcours/preview` | Calculer des parcours (sans sauvegarder) |
| `POST` | `/visites` | Créer une visite + calculer et sauvegarder les parcours |
| `GET` | `/visites` | Historique des visites |
| `GET` | `/visites/{id}/parcours` | Parcours d'une visite |
| `GET` | `/visites/{id}/carte` | Points GPS du parcours favori |
| `PUT` | `/visites/{id}/favori` | Marquer un parcours comme favori |
| `DELETE` | `/visites/{id}` | Supprimer une visite |

### Gestionnaire (JWT requis — rôle `gestionnaire`)

| Méthode | Route | Description |
|---|---|---|
| `POST/PUT/DELETE` | `/admin/spectacles[/{id}]` | CRUD spectacles |
| `POST/PUT/DELETE` | `/admin/lieux[/{id}]` | CRUD lieux |
| `POST/DELETE` | `/admin/distances[/{a}/{b}]` | Gestion du graphe de distances |
| `POST/PUT/DELETE` | `/admin/seances[/{id}]` | CRUD séances |
| `POST/DELETE` | `/admin/jours[/{date}]` | Gestion jours d'ouverture |

---

## Moteur de calcul des parcours

Service central `ParcoursService` implémentant :

1. **Floyd-Warshall** sur le graphe des distances → plus courts chemins entre tous les lieux
2. **Backtracking récursif** → génère tous les ordonnancements valides de séances :
   - Chaque spectacle choisi apparaît exactement une fois
   - Temps de trajet entre deux séances pris en compte
   - Respect des horaires d'ouverture du parc
3. **Tri des résultats** : parcours complets d'abord, puis par temps d'attente minimal
4. Retour des **10 meilleurs parcours**

---

## Modèle de données

```
utilisateur ──── visite ──── choisir ──── spectacle
                   │                          │
                parcours                    seance
                   │                          │
                 etape ─────────────────── seance
                                              │
                                            lieu
                                              │
                                          distance
```

**10 tables** : `utilisateur`, `lieu`, `spectacle`, `seance`, `jours`, `visite`, `choisir`, `parcours`, `etape`, `distance`

---

## Sécurité

- **Authentification** : JWT HS256, expiration 24 h
- **Mots de passe** : hashés bcrypt (`password_hash` / `PASSWORD_BCRYPT`)
- **Base de données** : requêtes préparées PDO partout (anti-injection SQL)
- **Back-office** : tokens CSRF sur tous les formulaires POST
- **Android** : JWT stocké dans `EncryptedSharedPreferences`
- **CORS** : restreindre les origines autorisées en production
- **HTTPS** : obligatoire en production (désactivé pour localhost uniquement)

---

## Structure du dépôt

```
V1/
├── PRD.md                  Cahier des charges complet
├── README.md               Ce fichier
├── api/                    API REST PHP MVC
│   ├── public/index.php    Point d'entrée
│   ├── app/
│   │   ├── Controllers/    AuthController, SpectacleController…
│   │   ├── Core/           Router, Database (PDO), Jwt, Controller
│   │   ├── Middlewares/    AuthMiddleware
│   │   ├── Models/         UtilisateurModel, SpectacleModel…
│   │   ├── Routes/         routes.php
│   │   └── Services/       ParcoursService (moteur de calcul)
│   └── config/config.php   Configuration BDD + JWT
├── backoffice/             Interface web gestionnaire
│   ├── public/index.php    Point d'entrée
│   ├── app/
│   │   ├── Controllers/    AuthController, SpectaclesController…
│   │   ├── Core/           Router, ApiClient (cURL), Controller
│   │   ├── Routes/         routes.php
│   │   └── Views/          Templates PHP (layout, auth, CRUD)
│   └── config/config.php   URL de l'API
├── android/                Application Android (Java)
│   └── app/src/main/
│       ├── java/.../
│       │   ├── api/        ApiClient, ApiResponse
│       │   ├── model/      Spectacle, Parcours, CatalogueItem
│       │   ├── adapter/    RecyclerView adapters
│       │   └── util/       Session, NavHelper
│       └── res/            Layouts XML, strings, drawables
└── db/
    └── ppe_puy_du_fou.sql  Schéma + données de test
```

---

## Documentation

Le dossier `docs/` regroupe la documentation projet :

| Fichier | Contenu |
|---|---|
| `docs/README.md` | Index de la documentation |

Documents prévus (sujet SIO2) : cas d'utilisation, schéma de navigation, maquettes Figma, MCD + règles de gestion, plans de tests, guide utilisateur, spécification OpenAPI.

---

## Auteurs

Projet réalisé dans le cadre du BTS SIO2 SLAM — AP3 « Visite au Puy du Fou ».
