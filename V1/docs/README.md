# Documentation — PPE Puy du Fou V1

Ce dossier regroupe toute la documentation projet exigée par le sujet SIO2 SLAM.

## Sommaire

| Document | Contenu |
|---|---|
| [SETUP.md](SETUP.md) | **Installation et hébergement** : pré-requis, lancement local des 3 briques (API, back-office, Android), déploiement en production, dépannage |
| [ARCHITECTURE.md](ARCHITECTURE.md) | Vue d'ensemble technique des 3 briques et de leurs interactions |
| [API.md](API.md) | Documentation des endpoints REST (à compléter au fur et à mesure / OpenAPI) |
| [TESTS.md](TESTS.md) | Plans de tests métier, fonctionnels, unitaires + jeux de données |
| [USER_GUIDE.md](USER_GUIDE.md) | Documentation utilisateur (visiteur Android + gestionnaire web) |

## Documents à produire (rappel sujet)

- [x] PRD (`V1/PRD.md`)
- [x] Schéma SQL + seed (`V1/db/`)
- [x] Guide d'installation et d'hébergement (`SETUP.md`)
- [ ] Tableau Trello (captures + détail des tâches par étudiant)
- [ ] Diagrammes des cas d'utilisation (réutiliser/mettre à jour `V0/ppe_info/diagramme_cu/`)
- [ ] Schéma de navigation (mobile + web, avec variables d'aiguillage et contrôleurs)
- [ ] Maquettage (Figma)
- [ ] MCD V1 + règles de gestion
- [ ] Plans de tests + jeux de données cohérents
- [ ] Documentation utilisateur
- [ ] Documentation finale paginée avec sommaire

## Arborescence du projet V1

```
V1/
├── PRD.md                  Cahier des charges détaillé
├── api/                    API REST PHP MVC (JWT, moteur de parcours)
├── backoffice/             App web gestionnaire (PHP MVC)
├── android/                App mobile visiteur (Java)
├── db/
│   ├── schema.sql          Création des tables MySQL
│   └── seed.sql            Données de test (10 spectacles, 40 lieux…)
└── docs/                   Documentation projet (ce dossier)
```
