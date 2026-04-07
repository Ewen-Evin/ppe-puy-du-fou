# Guide d'installation & déploiement — PPE Puy du Fou V1

Ce document explique **étape par étape** comment installer, lancer en local, puis héberger les trois briques du projet :

1. **API REST** (PHP MVC, MySQL) — `V1/api/`
2. **Back-office web** (PHP MVC, gestionnaire) — `V1/backoffice/`
3. **Application Android** (Java) — `V1/android/`

---

## 1. Pré-requis

| Outil | Version | Pour quoi faire |
|---|---|---|
| **Laragon** (ou WAMP/XAMPP) | dernière | Apache + PHP 8.1+ + MySQL/MariaDB |
| **PHP** | ≥ 8.1 | API et back-office. Extensions requises : `pdo_mysql`, `curl`, `json`, `mbstring`, `openssl` |
| **MySQL / MariaDB** | ≥ 10.4 | Base de données |
| **Android Studio** | Iguana ou plus récent | Compiler / lancer l'app Android |
| **JDK** | 11 ou 17 | Compilation Java pour Android |
| **Git** | n'importe quelle version | Versionning |
| Navigateur | n'importe lequel | Tester l'API et utiliser le back-office |

> **Conseil** : sur Windows, **Laragon** est le plus simple — il fournit Apache + PHP + MySQL en un seul installeur, et active automatiquement les vhosts.

---

## 2. Cloner le projet

```bash
git clone <url-du-repo> ppe_puy_du_fou
cd ppe_puy_du_fou
```

Le code se trouve dans `V1/`. Ne pas toucher à `V0/` (ancienne version, archivée).

---

## 3. Mise en place de la base de données

### 3.1 Créer la base et les tables

Avec **HeidiSQL** (livré avec Laragon), **phpMyAdmin** ou en ligne de commande :

```bash
mysql -u root -p < V1/db/schema.sql
```

Cela crée la base `ppe_puy_du_fou` et toutes les tables.

### 3.2 Insérer le jeu de données de test

```bash
mysql -u root -p < V1/db/seed.sql
```

Cela insère :
- 3 utilisateurs (cf. ci-dessous)
- 40 lieux du parc, 65+ distances
- 11 spectacles, ~30 séances pour le 11 avril 2026
- 4 jours d'ouverture
- 1 visite de démonstration

### 3.3 Comptes créés par le seed

| Rôle | Email | Mot de passe |
|---|---|---|
| Gestionnaire | `admin@puydufou.fr` | `password` |
| Visiteur | `jean.dupont@email.fr` | `password` |
| Visiteur | `marie.curie@email.fr` | `password` |

> Les hashs bcrypt du seed correspondent au mot de passe `password`. À changer immédiatement en production.

---

## 4. Lancer l'API REST en local

### 4.1 Configurer la connexion BDD

Ouvrir `V1/api/config/config.php` et adapter si besoin :

```php
'db' => [
    'host'     => '127.0.0.1',
    'port'     => 3306,
    'database' => 'ppe_puy_du_fou',
    'username' => 'root',
    'password' => '',          // mot de passe MySQL
    'charset'  => 'utf8mb4',
],
'jwt' => [
    'secret' => 'change-me-in-production-please', // À CHANGER
    ...
],
```

> ⚠️ **Important** : générer un secret JWT solide en production (`openssl rand -hex 32`).

### 4.2 Configurer un vhost Laragon

**Option A — Auto vhost (recommandé)** :
1. Copier le dossier `V1/api/` dans `C:/laragon/www/ppe-api/` (ou créer un lien symbolique).
2. Laragon détecte automatiquement et expose `http://ppe-api.test/`.
3. Le `DocumentRoot` doit pointer sur `public/`. Pour cela, créer un fichier `C:/laragon/etc/apache2/sites-enabled/auto.ppe-api.test.conf` :

```apache
<VirtualHost *:80>
    DocumentRoot "C:/laragon/www/ppe-api/public"
    ServerName ppe-api.test
    <Directory "C:/laragon/www/ppe-api/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Puis dans Laragon : **Menu → Apache → Reload**.

**Option B — Sans vhost** : laisser le projet sous `C:/laragon/www/ppe_puy_du_fou/V1/api/` et utiliser l'URL `http://localhost/ppe_puy_du_fou/V1/api/public/`. Dans ce cas, mettre à jour les URLs dans le back-office et Android (voir plus bas).

### 4.3 Tester l'API

Ouvrir dans le navigateur :
```
http://ppe-api.test/api/health
```
Réponse attendue :
```json
{"status":"ok"}
```

Tester l'auth avec **curl**, **Postman** ou **Insomnia** :
```bash
curl -X POST http://ppe-api.test/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"jean.dupont@email.fr","mot_de_passe":"password"}'
```

Tu dois recevoir un objet contenant un `token` JWT.

---

## 5. Lancer le back-office en local

### 5.1 Pointer Apache sur le back-office

Créer un second vhost `C:/laragon/etc/apache2/sites-enabled/auto.ppe-bo.test.conf` :

```apache
<VirtualHost *:80>
    DocumentRoot "C:/laragon/www/ppe-bo/public"
    ServerName ppe-bo.test
    <Directory "C:/laragon/www/ppe-bo/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

(en symlinkant ou copiant `V1/backoffice/` dans `C:/laragon/www/ppe-bo/`)

Reload Apache.

### 5.2 Configurer l'URL de l'API

Éditer `V1/backoffice/config/config.php` :
```php
return [
    'api_base_url' => 'http://ppe-api.test',  // ou http://localhost/ppe_puy_du_fou/V1/api/public
    'app_name'     => 'Puy du Fou - Back-office',
];
```

### 5.3 Tester

Aller sur `http://ppe-bo.test/login`, se connecter avec :
- **Email** : `admin@puydufou.fr`
- **Mot de passe** : `password`

Tu dois arriver sur le tableau de bord avec les compteurs (spectacles, lieux, jours).

---

## 6. Lancer l'application Android

### 6.1 Ouvrir le projet

1. Lancer Android Studio.
2. **File → Open** → choisir le dossier `V1/android/`.
3. Attendre la fin du **Gradle Sync** (peut prendre quelques minutes la 1re fois — téléchargement des dépendances).

### 6.2 Configurer l'URL de l'API

Éditer `V1/android/app/src/main/res/values/strings.xml` :

```xml
<string name="api_base_url">http://10.0.2.2/ppe_puy_du_fou/V1/api/public</string>
```

| Cas d'usage | URL à mettre |
|---|---|
| **Émulateur Android** + API sur localhost | `http://10.0.2.2/...` (10.0.2.2 = alias spécial pour le localhost de la machine hôte) |
| **Téléphone physique** sur le même Wi-Fi | `http://<IP_de_ton_PC>/...` (ex. `http://192.168.1.42/...`) |
| **API derrière un vhost** | `http://10.0.2.2/` + ne pas oublier d'ajouter `ppe-api.test` dans le `hosts` Android (compliqué) → préférer l'IP directe |

> Le `AndroidManifest.xml` contient déjà `android:usesCleartextTraffic="true"` pour autoriser HTTP en dev. À retirer en prod (HTTPS only).

### 6.3 Lancer l'app

1. Démarrer un **émulateur** (AVD Manager) ou brancher un téléphone en mode développeur.
2. Cliquer sur **Run ▶** dans Android Studio.
3. L'app s'installe et démarre sur **LoginActivity**.
4. Se connecter avec `jean.dupont@email.fr` / `password`.

---

## 7. Workflow de test bout-en-bout

1. **API** : `GET http://ppe-api.test/api/health` → `{"status":"ok"}`
2. **Back-office** : login gestionnaire, créer un spectacle de test, ajouter une séance pour aujourd'hui.
3. **Android** : login visiteur, voir le spectacle créé, le sélectionner, calculer un parcours.

Si les trois étapes passent, l'architecture complète fonctionne.

---

## 8. Hébergement en production (déploiement)

### 8.1 Choix d'hébergeur

| Brique | Options |
|---|---|
| **API + Back-office** | Hébergement mutualisé PHP+MySQL (OVH, Infomaniak, Hostinger…), VPS (OVH, Hetzner, Scaleway), ou un PaaS (Render, Railway) |
| **Base MySQL** | Incluse chez la plupart des hébergeurs PHP, ou service managé (PlanetScale, AWS RDS) |
| **App Android** | APK distribué directement, ou Google Play Store (compte développeur 25 $) |

### 8.2 Procédure générique (hébergeur mutualisé type OVH)

1. **Créer la base MySQL** dans le panel de l'hébergeur. Noter `host`, `user`, `password`, `database`.
2. **Importer le schéma** via phpMyAdmin :
   - `V1/db/schema.sql` puis `V1/db/seed.sql` (ou seulement schema.sql si tu ne veux pas les données de démo).
3. **Uploader les fichiers** via FTP/SFTP (FileZilla, WinSCP) :
   - `V1/api/` → `www/api/`
   - `V1/backoffice/` → `www/backoffice/`
4. **Configurer le DocumentRoot** sur les sous-dossiers `public/` :
   - Soit via le panel de l'hébergeur (sous-domaines `api.tondomaine.fr` et `admin.tondomaine.fr` pointant chacun sur leur `public/`).
   - Soit en plaçant un `.htaccess` à la racine `www/api/` qui redirige tout vers `public/`.
5. **Modifier les fichiers de config** :
   - `api/config/config.php` : credentials BDD de prod, **secret JWT fort**.
   - `backoffice/config/config.php` : `api_base_url` = URL publique de l'API (ex. `https://api.tondomaine.fr`).
6. **Activer HTTPS** :
   - Certificat Let's Encrypt (souvent gratuit et 1-clic chez l'hébergeur).
   - Forcer HTTPS via `.htaccess` :
     ```apache
     RewriteEngine On
     RewriteCond %{HTTPS} off
     RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
     ```
   - Retirer `usesCleartextTraffic="true"` du manifest Android.
7. **Tester** : `https://api.tondomaine.fr/api/health` doit répondre.

### 8.3 Procédure VPS Linux (Ubuntu + Nginx, à titre indicatif)

```bash
# 1. Installer le stack
sudo apt update
sudo apt install nginx php8.2-fpm php8.2-mysql php8.2-curl php8.2-mbstring mariadb-server

# 2. Créer la base
sudo mysql -e "CREATE DATABASE ppe_puy_du_fou; CREATE USER 'ppe'@'localhost' IDENTIFIED BY 'motdepasse'; GRANT ALL ON ppe_puy_du_fou.* TO 'ppe'@'localhost';"
mysql -u ppe -p ppe_puy_du_fou < V1/db/schema.sql
mysql -u ppe -p ppe_puy_du_fou < V1/db/seed.sql

# 3. Déployer
sudo cp -r V1/api      /var/www/ppe-api
sudo cp -r V1/backoffice /var/www/ppe-bo
sudo chown -R www-data:www-data /var/www/ppe-api /var/www/ppe-bo

# 4. Vhost Nginx (un par site, root sur public/)
# /etc/nginx/sites-available/ppe-api
server {
    listen 80;
    server_name api.tondomaine.fr;
    root /var/www/ppe-api/public;
    index index.php;
    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    }
}

# 5. Activer + reload
sudo ln -s /etc/nginx/sites-available/ppe-api /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx

# 6. HTTPS
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d api.tondomaine.fr -d admin.tondomaine.fr
```

### 8.4 Distribuer l'APK Android

1. Dans Android Studio : **Build → Generate Signed Bundle / APK → APK**.
2. Créer un keystore (le **conserver précieusement**, indispensable pour les mises à jour).
3. Choisir `release`, signer, générer.
4. L'APK sort dans `app/release/app-release.apk`.
5. Distribuer (lien direct, drive partagé, ou Google Play).

> Avant de générer le release : remettre l'URL de production dans `strings.xml` et retirer `usesCleartextTraffic`.

---

## 9. Dépannage rapide

| Symptôme | Cause probable | Solution |
|---|---|---|
| `404` sur toutes les routes API | mod_rewrite désactivé ou DocumentRoot pas sur `public/` | Vérifier `.htaccess` et le vhost |
| `DB connection failed` | Mauvais credentials ou MySQL arrêté | Vérifier `config/config.php` et le service MySQL |
| Back-office : "Connexion impossible" | URL de l'API fausse ou API down | Tester `/api/health` directement |
| Android : `Connection refused` sur émulateur | URL `localhost` au lieu de `10.0.2.2` | Corriger `strings.xml` |
| Android : crash sur HTTP | `usesCleartextTraffic` désactivé | Le réactiver pour le dev, ou passer en HTTPS |
| `Invalid or expired token` | Secret JWT changé entre deux requêtes ou token expiré (24h) | Se reconnecter |
| Erreur CORS depuis un autre front | `.htaccess` ne renvoie pas les bons headers | Vérifier `V1/api/public/.htaccess` |

---

## 10. Checklist avant rendu / oral

- [ ] Schéma + seed importés dans MySQL
- [ ] API répond sur `/api/health`
- [ ] Login gestionnaire fonctionne sur le back-office
- [ ] Login visiteur fonctionne sur l'app Android
- [ ] CRUD complet testé sur le back-office
- [ ] Calcul de parcours testé sur l'app Android
- [ ] Secret JWT changé si déploiement public
- [ ] HTTPS activé en prod
- [ ] APK signé généré
- [ ] Trello à jour
- [ ] Documentation finale (`V1/docs/`) complète et paginée
