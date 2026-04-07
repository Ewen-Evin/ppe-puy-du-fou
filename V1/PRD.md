# PRD — PPE Puy du Fou V1

**Projet** : SIO2 SLAM — AP3 « Visite au Puy du Fou »
**Client** : Groupe OXAM / Parc Puy du Fou
**Version** : 1.0 (refonte propre de V0)
**Date** : 2026-04-07

---

## 1. Contexte & motivation de la refonte

La V0 a été développée comme un site web PHP « format téléphone » simulant une appli mobile. Cette approche ne respecte pas l'esprit du sujet (deux cibles distinctes : visiteur mobile + gestionnaire web) et mélange les responsabilités. La V1 sépare proprement :

- une **API REST** centrale (source de vérité, MVC, sécurisée),
- une **application Android native** (Android Studio / Kotlin) pour le visiteur,
- un **back-office web** (PHP MVC) pour le gestionnaire.

Le sujet, le MCD et les diagrammes restent ceux de `V0/ppe_info/`. Aucun changement métier.

## 2. Objectifs

1. Respecter strictement les contraintes du sujet (`Mission Puy du fou.docx.pdf`) : MVC, MCD Merise, normes de dev PHP, doc complète, tests, Agile.
2. Offrir au **visiteur** une vraie appli Android pour choisir ses spectacles et obtenir des parcours optimisés.
3. Offrir au **gestionnaire** un back-office web pour gérer parc, spectacles, séances et programme journalier.
4. Centraliser toute la logique et la donnée derrière une API unique, partagée par les deux clients.

## 3. Périmètre fonctionnel (rappel cahier des charges)

### Acteurs
- **Visiteur** (mobile Android) : authentification, saisie vitesse de marche, sélection des spectacles, génération et consultation des parcours possibles, historique de visites.
- **Gestionnaire** (back-office web) : authentification, CRUD lieux + distances, CRUD spectacles, CRUD séances, gestion des jours d'ouverture du parc.

### Règles métier clés
- Un spectacle = libellé + durée spectacle + durée d'attente + lieu.
- Distances stockées entre certains lieux ; les autres sont déduites par **transitivité** (plus court chemin — Dijkstra sur le graphe des allées).
- Temps de parcours = distance / vitesse de marche du visiteur.
- Un parcours = ordonnancement de séances choisies, compatible avec horaires de séances + horaires d'ouverture du parc + temps de trajet.
- Le moteur doit proposer **plusieurs parcours possibles** (complets ou partiels), avec calcul des temps d'attente.

## 4. Architecture cible

```
┌──────────────────────┐        ┌──────────────────────┐
│  Android (Kotlin)    │        │  Back-office Web     │
│  Visiteur            │        │  PHP MVC (Gestion.)  │
└──────────┬───────────┘        └──────────┬───────────┘
           │  HTTPS / JSON                 │  HTTPS / JSON
           │  JWT                          │  Session + JWT interne
           └──────────────┬────────────────┘
                          ▼
              ┌────────────────────────┐
              │  API REST PHP (MVC)    │
              │  Auth, métier, moteur  │
              │  de calcul de parcours │
              └───────────┬────────────┘
                          ▼
                   ┌─────────────┐
                   │  MySQL      │
                   │  (MCD V0)   │
                   └─────────────┘
```

### 4.1 API REST (PHP, MVC)
- **Stack** : PHP 8.x sous Laragon, MySQL, architecture MVC maison conforme aux normes de dev fournies.
- **Format** : JSON, codes HTTP standards.
- **Sécurité** : authentification JWT, mots de passe hashés (`password_hash`), HTTPS, validation de toutes les entrées, requêtes préparées PDO (anti-injection), CORS restreint.
- **Responsabilités** : authentification, CRUD métier, **moteur de calcul des parcours** (Dijkstra + ordonnancement par séances).

#### Endpoints principaux (v1)
| Méthode | Route | Acteur | Description |
|---|---|---|---|
| POST | `/api/auth/register` | Visiteur | Création compte visiteur |
| POST | `/api/auth/login` | Tous | Auth, retourne JWT |
| GET  | `/api/spectacles` | Tous | Liste des spectacles |
| GET  | `/api/spectacles/{id}` | Tous | Détail spectacle |
| GET  | `/api/seances?date=` | Tous | Séances d'une date |
| GET  | `/api/jours` | Tous | Jours d'ouverture |
| POST | `/api/visites` | Visiteur | Créer une visite (date, vitesse, spectacles choisis) |
| GET  | `/api/visites/{id}/parcours` | Visiteur | Parcours calculés pour cette visite |
| GET  | `/api/visites` | Visiteur | Historique du visiteur |
| CRUD | `/api/admin/spectacles` | Gestionnaire | Gestion spectacles |
| CRUD | `/api/admin/lieux` | Gestionnaire | Gestion lieux |
| CRUD | `/api/admin/distances` | Gestionnaire | Gestion arêtes du graphe |
| CRUD | `/api/admin/seances` | Gestionnaire | Gestion séances |
| CRUD | `/api/admin/jours` | Gestionnaire | Gestion jours d'ouverture |

### 4.2 Application Android (Visiteur)
- **Stack** : Android Studio, **Java**, MVVM, Retrofit (HTTP), ExecutorService/LiveData, Room (cache local optionnel), Material 3.
- **SDK min** : Android 8.0 (API 26).
- **Écrans** :
  1. Splash / Login / Register
  2. Profil (vitesse de marche)
  3. Liste des spectacles (filtrable par jour)
  4. Détail spectacle
  5. Sélection des spectacles + date de visite
  6. Résultats : liste des parcours possibles (avec horaires, temps de trajet, temps d'attente, marqueur « complet / partiel »)
  7. Détail d'un parcours (timeline)
  8. Historique des visites
- **Sécurité** : stockage du JWT dans `EncryptedSharedPreferences`, communications HTTPS uniquement.

### 4.3 Back-office Web (Gestionnaire)
- **Stack** : PHP MVC (même socle que l'API, ou app séparée consommant l'API en interne), Twig ou templates PHP, Bootstrap pour l'ergonomie.
- **Écrans** : login, dashboard, gestion spectacles, lieux, distances (avec visualisation du graphe), séances, jours d'ouverture, gestion des comptes gestionnaires.
- **Sécurité** : session PHP + CSRF tokens, accès restreint au profil `gestionnaire`.

## 5. Modèle de données

Le **MCD de V0 est conservé** (`V0/ppe_info/bdd/mcd/ppe_puy_du_fou.png`, script `V0/ppe_info/bdd/sql/bdd.sql`). Tables :
`Utilisateur`, `Lieu`, `Jours`, `Visite`, `Spectacle`, `Seance`, `Parcours`, `Etape`, `choisir`, `distance`.

Ajustements V1 :
- `distance` : ajouter colonne `distance_metres INT NOT NULL` (manquante en V0).
- `Utilisateur.type_profil` : restreindre à ENUM(`visiteur`, `gestionnaire`).
- `Utilisateur.mot_de_passe` : hash bcrypt (déjà 255 chars).
- Ajouter index sur clés étrangères et sur `Seance.heure_debut`.

## 6. Moteur de calcul des parcours (cœur métier)

**Entrée** : `visite { date, vitesse_marche, spectacles_choisis[] }`
**Étapes** :
1. Construire le graphe pondéré des lieux (distances + transitivité via Dijkstra).
2. Récupérer toutes les séances des spectacles choisis pour la date.
3. Générer les ordonnancements compatibles (backtracking) :
   - chaque séance choisie une seule fois,
   - la fin d'une séance + temps de trajet ≤ début de la suivante,
   - tout doit tenir entre `heure_ouverture` et `heure_fermeture` du jour.
4. Pour chaque parcours candidat : calculer temps total, temps d'attente, marquer complet/partiel.
5. Retourner les N meilleurs parcours (tri : complet d'abord, puis temps d'attente minimal).

À implémenter dans un service dédié `ParcoursService` côté API, **testé unitairement**.

## 7. Sécurité

- HTTPS obligatoire.
- JWT signé (HS256), expiration courte + refresh.
- Hash mot de passe bcrypt (`password_hash` / `PASSWORD_DEFAULT`).
- Validation/sanitization stricte de toutes les entrées API.
- Requêtes préparées PDO partout (anti SQLi).
- CSRF token sur le back-office web.
- Headers de sécurité (CSP, X-Frame-Options, etc.).
- Logs des accès admin.

## 8. Tests

Conformément au sujet (test métier, fonctionnel, unitaire) :
- **Unitaires** (PHPUnit) : `ParcoursService` (Dijkstra, ordonnancement), validateurs, services auth.
- **Fonctionnels API** : Postman / collection automatisée sur tous les endpoints.
- **Métier** : jeux de données cohérents (cf. `V0/ppe_info/bdd/sql/insert_data.sql`) + scénarios visiteur réels.
- **Android** : tests instrumentés sur les écrans clés (login, sélection, affichage parcours).
- **Plans de tests documentés** dans `V1/docs/tests/`.

## 9. Documentation à livrer

Reprise stricte de la liste du sujet, dans `V1/docs/` :
1. Tableau **Trello** (Agile SCRUM/KANBAN) — captures + détail des tâches par étudiant.
2. Diagrammes des **cas d'utilisation** (réutiliser/mettre à jour `V0/ppe_info/diagramme_cu/`).
3. **Schéma de navigation** mobile + web (avec variables d'aiguillage et contrôleurs).
4. **Maquettage** (Figma) mobile + back-office.
5. **MCD + règles de gestion** (mis à jour V1).
6. **Plans de tests** + jeux de données.
7. **Documentation utilisateur** (visiteur Android + gestionnaire web).
8. **Documentation technique API** (Swagger/OpenAPI).
9. Document final paginé avec sommaire.

## 10. Arborescence cible du dépôt V1

```
V1/
├── PRD.md                       (ce document)
├── api/                         API REST PHP MVC
│   ├── public/                  point d'entrée index.php
│   ├── app/
│   │   ├── Controllers/
│   │   ├── Models/
│   │   ├── Services/            ParcoursService, AuthService...
│   │   ├── Middlewares/         JWT, CORS
│   │   └── Routes/
│   ├── config/
│   ├── tests/                   PHPUnit
│   └── composer.json
├── backoffice/                  App web gestionnaire (PHP MVC)
│   ├── public/
│   ├── app/
│   │   ├── Controllers/
│   │   ├── Models/
│   │   └── Views/
│   └── config/
├── android/                     Projet Android Studio (Java)
│   └── app/src/main/...
├── db/
│   ├── schema.sql               schéma V1 (MCD V0 + ajustements)
│   └── seed.sql                 jeux de données de test
└── docs/
    ├── cu/
    ├── navigation/
    ├── maquettes/
    ├── mcd/
    ├── tests/
    ├── user/
    └── api/                     OpenAPI / Swagger
```

## 11. Méthodologie & planning

- **Méthode** : Agile KANBAN sur Trello, daily async, séances physiques aux dates fixées par le sujet (3/10, 10/10, 7/11, 14/11, 21/11), oral 28/11.
- **Versionning** : Git (GitHub), une branche par feature, PR + revue croisée.
- **Définition of Done** : code respectant les normes de dev, testé, documenté, mergé.

## 12. Étapes de réalisation proposées

1. **Setup** : repo Git, structure V1, schéma MySQL V1, Trello.
2. **API socle** : routeur MVC, PDO, auth JWT, endpoints lecture publics.
3. **Back-office** : login gestionnaire, CRUD spectacles / lieux / distances / séances / jours.
4. **Moteur de parcours** + tests unitaires.
5. **Endpoints visite/parcours** + tests fonctionnels.
6. **App Android (Java)** : auth, liste spectacles, sélection, affichage parcours, historique.
7. **Tests transverses** + jeux de données + documentation finale.
8. **Préparation oral** 28/11.

## 13. Hors périmètre V1

- Paiement / billetterie.
- Géolocalisation temps réel dans le parc.
- Notifications push.
- Version iOS.
- Multilingue (FR uniquement).
