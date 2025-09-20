# 🔥 Sasneaks E-commerce - Architecture MVC Professionnelle

## 📋 Description du projet

**Sasneaks** est une plateforme e-commerce professionnelle spécialisée dans la vente de sneakers, développée avec une architecture MVC moderne et sécurisée. Le projet transforme un site vitrine basique en une solution e-commerce complète et professionnelle.

### ✨ Fonctionnalités principales

- **Catalogue produits** avec pagination, filtres et recherche
- **Système de panier** avancé avec gestion du stock
- **Authentification sécurisée** avec support 2FA
- **Gestion des commandes** complète
- **Interface administrateur** pour la gestion
- **Architecture MVC** propre et maintenable
- **Sécurité renforcée** (CSRF, validation, échappement XSS)
- **Base de données optimisée** avec contraintes et index

---

## 🚀 Installation et Configuration

### Prérequis

- **PHP 8.0+**
- **MySQL 5.7+** ou **MariaDB 10.3+**
- **MAMP** (pour développement local)
- **Serveur web** avec support PHP

### 🔧 Installation MAMP (Développement local)

#### 1. Configuration de MAMP

```bash
# Télécharger et installer MAMP
# https://www.mamp.info/en/downloads/

# Démarrer MAMP avec les paramètres par défaut :
# - Apache Port: 8888
# - MySQL Port: 8889
# - PHP Version: 8.0+
```

#### 2. Cloner le projet

```bash
# Cloner dans le dossier htdocs de MAMP
cd /Applications/MAMP/htdocs
git clone [votre-repo] sasneaks
cd sasneaks
```

#### 3. Configuration de la base de données

```bash
# 1. Ouvrir phpMyAdmin : http://localhost:8888/phpMyAdmin/
# 2. Créer une nouvelle base de données : sasneaks_db
# 3. Importer le fichier SQL
```

**Via phpMyAdmin :**
- Aller dans l'onglet "Importer"
- Sélectionner le fichier `database/migrations.sql`
- Cliquer sur "Exécuter"

**Via ligne de commande :**
```bash
mysql -u root -p -h localhost -P 8889 sasneaks_db < database/migrations.sql
```

#### 4. Configuration du projet

```bash
# Copier le fichier de configuration
cp config/config.example.php config/config.php
```

**Éditer `config/config.php` :**
```php
return [
    'database' => [
        'host' => 'localhost',
        'port' => '8889',        // Port MySQL MAMP
        'dbname' => 'sasneaks_db',
        'username' => 'root',
        'password' => 'root',    // Mot de passe MAMP par défaut
        'charset' => 'utf8mb4'
    ],
    'app' => [
        'name' => 'Sasneaks',
        'env' => 'development',
        'debug' => true,
        'url' => 'http://localhost:8888/sasneaks',
        'timezone' => 'Europe/Paris'
    ]
    // ... autres configurations
];
```

#### 5. Test de l'installation

```bash
# Ouvrir dans le navigateur
http://localhost:8888/sasneaks/public/

# Ou configurer un vhost pour
http://sasneaks.local
```

---

### 🌐 Déploiement sur Hostinger

#### 1. Préparation des fichiers

```bash
# Créer une archive du projet (sans les fichiers de développement)
zip -r sasneaks-production.zip . \
  -x "*.git*" "README.md" "*.DS_Store" \
  "storage/logs/*" "config/config.php"
```

#### 2. Upload sur Hostinger

1. **Se connecter au panneau de contrôle Hostinger**
2. **Aller dans "Gestionnaire de fichiers"**
3. **Naviguer vers `public_html/`**
4. **Uploader et extraire l'archive**

#### 3. Configuration de la base de données

1. **Créer une base de données MySQL** via le panneau Hostinger
2. **Noter les informations de connexion :**
   - Nom de la base : `u123456789_sasneaks`
   - Utilisateur : `u123456789_user`
   - Mot de passe : `[généré par Hostinger]`
   - Hôte : `localhost`

3. **Importer la base de données :**
   - Aller dans phpMyAdmin
   - Sélectionner la base créée
   - Importer `database/migrations.sql`

#### 4. Configuration du projet

```bash
# Éditer config/config.php sur le serveur
```

```php
return [
    'database' => [
        'host' => 'localhost',
        'port' => '3306',
        'dbname' => 'u123456789_sasneaks',
        'username' => 'u123456789_user',
        'password' => '[votre-mot-de-passe]',
        'charset' => 'utf8mb4'
    ],
    'app' => [
        'name' => 'Sasneaks',
        'env' => 'production',
        'debug' => false,
        'url' => 'https://votre-domaine.com',
        'timezone' => 'Europe/Paris'
    ]
    // ... autres configurations
];
```

#### 5. Configuration du serveur web

**Option A : Sous-domaine pointant vers `/public`**
```apache
# Document root doit pointer vers /public_html/sasneaks/public/
```

**Option B : .htaccess pour redirection**
```apache
# Dans /public_html/.htaccess
RewriteEngine On
RewriteRule ^(.*)$ sasneaks/public/$1 [L]
```

---

## 📁 Structure du projet

```
sasneaks/
├── 📂 public/                  # Point d'entrée web
│   └── index.php              # Front controller
├── 📂 app/                    # Code de l'application
│   ├── 📂 Core/               # Classes principales
│   │   ├── Database.php       # Gestionnaire BDD (Singleton)
│   │   ├── Router.php         # Routeur MVC
│   │   ├── Controller.php     # Contrôleur de base
│   │   ├── Session.php        # Gestion des sessions
│   │   ├── Security.php       # Sécurité (CSRF, XSS, etc.)
│   │   └── Validator.php      # Validation des données
│   ├── 📂 Controllers/        # Contrôleurs MVC
│   │   ├── HomeController.php
│   │   ├── AuthController.php
│   │   ├── ProductController.php
│   │   ├── CartController.php
│   │   └── AdminController.php
│   ├── 📂 Models/             # Modèles de données
│   │   ├── User.php
│   │   ├── Product.php
│   │   ├── Cart.php
│   │   ├── Category.php
│   │   └── Order.php
│   └── 📂 Views/              # Templates et vues
│       ├── 📂 layouts/        # Layouts de base
│       ├── 📂 home/
│       ├── 📂 products/
│       ├── 📂 auth/
│       ├── 📂 cart/
│       ├── 📂 admin/
│       └── 📂 errors/
├── 📂 assets/                 # Ressources statiques
│   ├── 📂 css/               # Feuilles de style
│   ├── 📂 js/                # Scripts JavaScript
│   └── 📂 images/            # Images du site
├── 📂 config/                 # Configuration
│   ├── config.php            # Config principale
│   └── config.example.php    # Template de config
├── 📂 database/              # Base de données
│   └── migrations.sql        # Script de création BDD
├── 📂 storage/               # Stockage local
│   └── 📂 logs/              # Fichiers de logs
└── 📂 image/                 # Images produits existantes
```

---

## 🛣️ Routes disponibles

### Routes publiques

| Méthode | URI | Contrôleur | Description |
|---------|-----|------------|-------------|
| GET | `/` | Home@index | Page d'accueil |
| GET | `/products` | Product@index | Liste des produits |
| GET | `/products/search` | Product@search | Recherche produits |
| GET | `/product/{slug}` | Product@show | Détail produit |
| GET | `/category/{slug}` | Product@category | Produits par catégorie |
| GET | `/login` | Auth@showLogin | Formulaire de connexion |
| POST | `/login` | Auth@login | Traitement connexion |
| GET | `/register` | Auth@showRegister | Formulaire d'inscription |
| POST | `/register` | Auth@register | Traitement inscription |

### Routes protégées (utilisateurs connectés)

| Méthode | URI | Contrôleur | Description |
|---------|-----|------------|-------------|
| GET | `/cart` | Cart@index | Page du panier |
| POST | `/cart/add` | Cart@add | Ajouter au panier |
| POST | `/cart/update` | Cart@update | Modifier quantité |
| POST | `/cart/remove` | Cart@remove | Supprimer du panier |
| GET | `/checkout` | Order@checkout | Page de commande |
| POST | `/checkout` | Order@process | Traiter la commande |
| GET | `/profile` | User@profile | Profil utilisateur |
| GET | `/orders` | Order@index | Historique commandes |

### Routes administrateur

| Méthode | URI | Contrôleur | Description |
|---------|-----|------------|-------------|
| GET | `/admin` | Admin@dashboard | Tableau de bord |
| GET | `/admin/users` | Admin@users | Gestion utilisateurs |
| GET | `/admin/products` | Admin@products | Gestion produits |
| GET | `/admin/orders` | Admin@orders | Gestion commandes |

---

## 👥 Comptes de démonstration

### Compte administrateur
- **Email :** `admin@sasneaks.com`
- **Mot de passe :** `Admin#1234`
- **Rôle :** Administrateur complet

### Compte utilisateur
- **Email :** `demo@sasneaks.com`
- **Mot de passe :** `Demo#1234`
- **Rôle :** Client standard

---

## ✅ Tests manuels

### Scénarios de test essentiels

#### 1. Navigation et catalogue
- [ ] Accéder à la page d'accueil
- [ ] Naviguer dans le catalogue de produits
- [ ] Utiliser la recherche de produits
- [ ] Filtrer par catégorie
- [ ] Consulter le détail d'un produit

#### 2. Authentification
- [ ] S'inscrire avec un nouveau compte
- [ ] Se connecter avec les identifiants de démo
- [ ] Tester la déconnexion
- [ ] Vérifier la protection des pages

#### 3. Panier et commande
- [ ] Ajouter des produits au panier
- [ ] Modifier les quantités
- [ ] Supprimer des articles
- [ ] Procéder au checkout
- [ ] Finaliser une commande

#### 4. Administration
- [ ] Se connecter en tant qu'admin
- [ ] Consulter le tableau de bord
- [ ] Gérer les produits (CRUD)
- [ ] Consulter les commandes

#### 5. Sécurité
- [ ] Vérifier les protections CSRF
- [ ] Tester l'échappement XSS
- [ ] Valider les contraintes de saisie

---

## 🏗️ Architecture technique

### Principes de conception

#### MVC (Model-View-Controller)
- **Models :** Gestion des données et logique métier
- **Views :** Présentation et templates
- **Controllers :** Logique de l'application et flux

#### Sécurité renforcée
- **PDO + requêtes préparées** pour toutes les interactions BDD
- **Tokens CSRF** sur tous les formulaires POST
- **Validation serveur** systématique
- **Échappement XSS** automatique dans les vues
- **Sessions sécurisées** avec régénération d'ID

#### Patterns utilisés
- **Singleton** pour Database et Session
- **Repository** pour les modèles de données
- **Front Controller** pour le routage
- **Factory** pour les contrôleurs

### Base de données optimisée

```sql
-- Contraintes de clés étrangères
-- Index optimisés pour les requêtes
-- Triggers d'audit automatiques
-- Champs timestamp automatiques
-- Validation des données au niveau BDD
```

---

## 🔒 Sécurité implémentée

### Protection CSRF
```php
// Dans tous les formulaires
<?php echo Security::csrfField(); ?>

// Validation côté serveur
if (!$this->validateCsrf()) {
    throw new Exception('Token CSRF invalide');
}
```

### Protection XSS
```php
// Échappement automatique
<?php echo Security::escape($userInput); ?>

// Dans les vues
<?php echo htmlspecialchars($data, ENT_QUOTES, 'UTF-8'); ?>
```

### Validation des données
```php
$validator = new Validator();
$result = $validator->validate($_POST, [
    'email' => 'required|email|unique:users',
    'password' => 'required|password|confirmed'
]);
```

### Sessions sécurisées
```php
// Configuration automatique
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.use_strict_mode', 1);
```

---

## 📦 Migration et déploiement

### Checklist de déploiement

#### Avant le déploiement
- [ ] Tester en local sur MAMP
- [ ] Valider tous les scénarios de test
- [ ] Optimiser les images et assets
- [ ] Configurer les variables d'environnement

#### Déploiement Hostinger
- [ ] Créer la base de données
- [ ] Uploader les fichiers
- [ ] Importer la structure BDD
- [ ] Configurer config.php
- [ ] Tester les URLs de production

#### Post-déploiement
- [ ] Vérifier la connectivité BDD
- [ ] Tester les fonctionnalités critiques
- [ ] Configurer les sauvegardes
- [ ] Mettre en place le monitoring

---

## 🔧 Interface pour Stripe (Futur)

### Architecture préparée pour les paiements

```php
// Interface PaymentGateway
interface PaymentGateway {
    public function createPayment(array $orderData): array;
    public function confirmPayment(string $paymentId): bool;
    public function refundPayment(string $paymentId): bool;
}

// Implémentation Stripe
class StripeAdapter implements PaymentGateway {
    // Logique Stripe séparée du domaine métier
}
```

### Intégration future
1. Créer `app/Services/Payment/StripeAdapter.php`
2. Ajouter les clés API dans `config.php`
3. Modifier `OrderController` pour utiliser l'adapter
4. Ajouter les webhooks Stripe

---

## 📚 Technologies utilisées

- **Backend :** PHP 8+, Architecture MVC
- **Base de données :** MySQL/MariaDB avec PDO
- **Frontend :** HTML5, CSS3, JavaScript, Tailwind CSS (CDN)
- **Sécurité :** CSRF, XSS, validation serveur, sessions sécurisées
- **Serveur :** Apache/Nginx compatible

---

## 🤝 Support et maintenance

### Contact technique
- **Développeur :** Salim Rahmouni
- **Formation :** BTS SIO 2025
- **Email :** [votre-email]

### Maintenance recommandée
- **Sauvegardes BDD :** Quotidiennes
- **Logs de sécurité :** Surveillance hebdomadaire  
- **Mises à jour PHP :** Selon les versions stables
- **Monitoring :** Uptime et performances

---

## 📈 Évolutions possibles

### Court terme
- [ ] Système de favoris/wishlist
- [ ] Avis et notes produits
- [ ] Newsletter et promotions
- [ ] API REST pour mobile

### Moyen terme  
- [ ] Paiement Stripe complet
- [ ] Multi-langues (i18n)
- [ ] Cache Redis/Memcached
- [ ] Elasticsearch pour la recherche

### Long terme
- [ ] Architecture microservices
- [ ] Progressive Web App (PWA)
- [ ] Machine Learning (recommandations)
- [ ] Intégrations ERP/CRM

---

**🎯 Projet réalisé dans le cadre du BTS SIO 2025 - Sasneaks E-commerce Professional**

> *Une architecture MVC moderne, sécurisée et prête pour la production !*
