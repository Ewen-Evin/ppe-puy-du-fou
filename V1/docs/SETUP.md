# Guide d'installation — PPE Puy du Fou V1

Setup rapide pour lancer le projet en local avec **Laragon**.

Le projet a 3 briques :
- **API REST** (PHP) — `V1/api/`
- **Back-office web** (PHP) — `V1/backoffice/`
- **App Android** (Java) — `V1/android/`

---

## 1. Pré-requis

- **Laragon** (dernière version) — fournit Apache + PHP 8.1+ + MySQL/MariaDB
- **Android Studio** (Iguana ou +) + **JDK 11 ou 17** — pour l'app Android
- **Git**

> Laragon inclut déjà tout ce qu'il faut côté serveur (PHP, MySQL, Apache, phpMyAdmin). Rien d'autre à installer.

---

## 2. Cloner le projet

```bash
git clone <url-du-repo> ppe_puy_du_fou
cd ppe_puy_du_fou
```

Le code est dans `V1/`.

---

## 3. Base de données

### 3.1 Importer la base de données

Ouvrir **phpMyAdmin** depuis Laragon (clic droit sur l'icône Laragon → phpMyAdmin), puis :

1. Aller dans l'onglet **Importer**
2. Choisir le fichier `V1/db/ppe_puy_du_fou.sql` → cliquer **Exécuter**

Le fichier crée automatiquement la base `ppe_puy_du_fou`, toutes les tables et les données de test.

**Alternative en terminal** (depuis le dossier du projet) :
```bash
mysql -u root < V1/db/ppe_puy_du_fou.sql
```

> Pas besoin de `-p` avec Laragon, le mot de passe root est vide par défaut.

### 3.2 Comptes créés par le seed

| Rôle | Email | Mot de passe |
|---|---|---|
| Gestionnaire | `admin@puydufou.fr` | `password` |
| Visiteur | `jean.dupont@email.fr` | `password` |
| Visiteur | `marie.curie@email.fr` | `password` |

---

## 4. Lancer l'API

### 4.1 Config BDD

Le fichier `V1/api/config/config.php` est déjà configuré pour Laragon (root, sans mot de passe, port 3306). Rien à changer en local.

### 4.2 Accès à l'API

**Le plus simple — sans vhost** : le projet est déjà dans `C:/laragon/www/`, donc l'API est accessible directement à :
```
http://localhost/ppe-puy-du-fou/V1/api/public/api/health
```

**Avec un vhost (plus propre)** :
1. Dans Laragon : **Menu → Apache → sites-enabled** → créer `auto.ppe-api.test.conf` :

```apache
<VirtualHost *:80>
    DocumentRoot "C:/laragon/www/ppe_puy_du_fou/V1/api/public"
    ServerName ppe-api.test
    <Directory "C:/laragon/www/ppe_puy_du_fou/V1/api/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

2. Reload Apache (clic droit Laragon → Apache → Reload)
3. Tester : `http://ppe-api.test/api/health` → `{"status":"ok"}`

### 4.3 Vérifier que ça marche

Dans le navigateur :
```
http://ppe-api.test/api/health
```
Réponse attendue : `{"status":"ok"}`

---

## 5. Lancer le back-office

### 5.1 Créer le vhost

Créer `auto.ppe-bo.test.conf` dans le même dossier :

```apache
<VirtualHost *:80>
    DocumentRoot "C:/laragon/www/ppe_puy_du_fou/V1/backoffice/public"
    ServerName ppe-bo.test
    <Directory "C:/laragon/www/ppe_puy_du_fou/V1/backoffice/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Reload Apache.

### 5.2 Configurer l'URL de l'API

Éditer `V1/backoffice/config/config.php` et mettre l'URL qui correspond à ton setup :

```php
'api_base_url' => 'http://ppe-api.test',
// ou si sans vhost : 'http://localhost/ppe_puy_du_fou/V1/api/public'
```

### 5.3 Tester

Aller sur `http://ppe-bo.test/login` et se connecter avec `admin@puydufou.fr` / `password`.

---

## 6. Lancer l'app Android

### 6.1 Ouvrir le projet

1. Android Studio → **File → Open** → sélectionner `V1/android/`
2. Attendre le **Gradle Sync** (quelques minutes la 1re fois)

### 6.2 Configurer l'URL de l'API

Éditer `V1/android/app/src/main/res/values/strings.xml` :

```xml
<string name="api_base_url">http://10.0.2.2/ppe-puy-du-fou/V1/api/public</string>
```

> `10.0.2.2` = alias spécial de l'émulateur Android pour accéder au localhost du PC. Attention à bien utiliser des **tirets** (`ppe-puy-du-fou`) et non des underscores.

| Situation | URL |
|---|---|
| Émulateur Android | `http://10.0.2.2/ppe-puy-du-fou/V1/api/public` |
| Téléphone physique (même Wi-Fi) | `http://<IP_du_PC>/ppe-puy-du-fou/V1/api/public` |

### 6.3 Lancer

1. Démarrer un émulateur ou brancher un téléphone (mode développeur activé)
2. Cliquer **Run ▶**
3. Se connecter avec `jean.dupont@email.fr` / `password`

---

## 7. Vérifier que tout fonctionne

1. **API** : `http://ppe-api.test/api/health` → `{"status":"ok"}`
2. **Back-office** : login gestionnaire → créer un spectacle, ajouter une séance
3. **Android** : login visiteur → voir le spectacle → calculer un parcours

Si les trois marchent, c'est bon.

---

## 8. Dépannage

| Problème | Solution |
|---|---|
| `404` sur les routes API | Vérifier que mod_rewrite est activé et que le DocumentRoot pointe sur `public/` |
| `DB connection failed` | Vérifier que MySQL tourne dans Laragon et que `config/config.php` a les bons credentials |
| Back-office : "Connexion impossible" | Vérifier que l'API tourne (`/api/health`) et que `api_base_url` est correct |
| Android : `Connection refused` | Utiliser `10.0.2.2` au lieu de `localhost` dans `strings.xml` |
| Android : crash sur HTTP | Vérifier que `usesCleartextTraffic="true"` est dans le `AndroidManifest.xml` (déjà le cas par défaut) |
| `Invalid or expired token` | Se reconnecter (token expire après 24h) |

---

## 9. Déploiement en production

### Hébergement mutualisé (OVH, Infomaniak, Hostinger…)

1. Créer la base MySQL dans le panel de l'hébergeur
2. Importer `ppe_puy_du_fou.sql` via phpMyAdmin (crée tables + données en un seul import)
3. Uploader `V1/api/` et `V1/backoffice/` via FTP
4. Configurer les DocumentRoot sur les dossiers `public/`
5. Modifier `api/config/config.php` (credentials BDD + **secret JWT fort** : `openssl rand -hex 32`)
6. Modifier `backoffice/config/config.php` (`api_base_url` = URL publique de l'API)
7. Activer HTTPS (Let's Encrypt, souvent 1-clic chez l'hébergeur)

### APK Android

1. Android Studio → **Build → Generate Signed Bundle / APK → APK**
2. Mettre l'URL de prod dans `strings.xml` et retirer `usesCleartextTraffic="true"`
3. Distribuer l'APK (drive, lien direct, ou Google Play)

---

## 10. Checklist avant rendu

- [ ] Base importée (`ppe_puy_du_fou.sql`)
- [ ] API répond sur `/api/health`
- [ ] Login gestionnaire OK sur le back-office
- [ ] Login visiteur OK sur l'app Android
- [ ] CRUD testé sur le back-office
- [ ] Calcul de parcours testé sur Android
- [ ] APK signé généré
