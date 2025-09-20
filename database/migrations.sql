-- Migration pour Sasneaks E-commerce
-- Base de données optimisée avec contraintes de clés étrangères et index

SET FOREIGN_KEY_CHECKS = 0;

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS sasneaks_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sasneaks_db;

-- =========================================
-- Table: categories
-- =========================================
DROP TABLE IF EXISTS categories;
CREATE TABLE categories (
    id_category INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    image_url VARCHAR(255),
    parent_id INT NULL,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_categories_parent (parent_id),
    INDEX idx_categories_active (is_active),
    INDEX idx_categories_sort (sort_order),
    
    FOREIGN KEY (parent_id) REFERENCES categories(id_category) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================================
-- Table: users
-- =========================================
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id_client INT AUTO_INCREMENT PRIMARY KEY,
    identifiant VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    mdp VARCHAR(255) NOT NULL,
    role ENUM('client', 'admin', 'employee') DEFAULT 'client',
    firstname VARCHAR(50),
    lastname VARCHAR(50),
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    zip_code VARCHAR(10),
    country VARCHAR(50) DEFAULT 'France',
    birth_date DATE,
    is_active BOOLEAN DEFAULT TRUE,
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100),
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    secret VARCHAR(32), -- Pour 2FA
    
    INDEX idx_users_email (email),
    INDEX idx_users_role (role),
    INDEX idx_users_active (is_active),
    INDEX idx_users_remember (remember_token)
) ENGINE=InnoDB;

-- =========================================
-- Table: products
-- =========================================
DROP TABLE IF EXISTS products;
CREATE TABLE products (
    id_product INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    nom VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT,
    prix DECIMAL(10,2) NOT NULL,
    prix_promo DECIMAL(10,2) NULL,
    stock INT DEFAULT 0,
    stock_alert INT DEFAULT 5,
    sku VARCHAR(50) UNIQUE,
    image_url VARCHAR(255),
    image_hover_url VARCHAR(255),
    gallery JSON, -- Stockage de plusieurs images
    weight DECIMAL(8,2),
    dimensions VARCHAR(50),
    brand VARCHAR(100),
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    meta_title VARCHAR(200),
    meta_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_products_category (category_id),
    INDEX idx_products_name (nom),
    INDEX idx_products_slug (slug),
    INDEX idx_products_price (prix),
    INDEX idx_products_stock (stock),
    INDEX idx_products_featured (is_featured),
    INDEX idx_products_active (is_active),
    INDEX idx_products_sku (sku),
    
    FOREIGN KEY (category_id) REFERENCES categories(id_category) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =========================================
-- Table: panier
-- =========================================
DROP TABLE IF EXISTS panier;
CREATE TABLE panier (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    produit_id INT NOT NULL,
    quantite INT NOT NULL DEFAULT 1,
    prix DECIMAL(10,2) NOT NULL, -- Prix unitaire au moment de l'ajout
    session_id VARCHAR(255), -- Pour les paniers non connectés
    expires_at TIMESTAMP NULL, -- Pour nettoyer les paniers abandonnés
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_user_product (user_id, produit_id),
    INDEX idx_panier_user (user_id),
    INDEX idx_panier_product (produit_id),
    INDEX idx_panier_session (session_id),
    INDEX idx_panier_expires (expires_at),
    
    FOREIGN KEY (user_id) REFERENCES users(id_client) ON DELETE CASCADE,
    FOREIGN KEY (produit_id) REFERENCES products(id_product) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================
-- Table: commandes
-- =========================================
DROP TABLE IF EXISTS commandes;
CREATE TABLE commandes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    numero_commande VARCHAR(20) NOT NULL UNIQUE,
    statut ENUM('en_attente', 'confirmee', 'en_preparation', 'expediee', 'livree', 'annulee') DEFAULT 'en_attente',
    
    -- Informations de facturation
    billing_firstname VARCHAR(50) NOT NULL,
    billing_lastname VARCHAR(50) NOT NULL,
    billing_email VARCHAR(100) NOT NULL,
    billing_phone VARCHAR(20),
    billing_address TEXT NOT NULL,
    billing_city VARCHAR(100) NOT NULL,
    billing_zip VARCHAR(10) NOT NULL,
    billing_country VARCHAR(50) DEFAULT 'France',
    
    -- Informations de livraison
    shipping_firstname VARCHAR(50),
    shipping_lastname VARCHAR(50),
    shipping_address TEXT,
    shipping_city VARCHAR(100),
    shipping_zip VARCHAR(10),
    shipping_country VARCHAR(50),
    shipping_method VARCHAR(50),
    shipping_cost DECIMAL(10,2) DEFAULT 0,
    
    -- Montants
    subtotal DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    
    -- Suivi
    tracking_code VARCHAR(50),
    notes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_commandes_user (user_id),
    INDEX idx_commandes_numero (numero_commande),
    INDEX idx_commandes_statut (statut),
    INDEX idx_commandes_date (created_at),
    INDEX idx_commandes_tracking (tracking_code),
    
    FOREIGN KEY (user_id) REFERENCES users(id_client) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =========================================
-- Table: details_commandes
-- =========================================
DROP TABLE IF EXISTS details_commandes;
CREATE TABLE details_commandes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commande_id INT NOT NULL,
    produit_id INT NOT NULL,
    nom_produit VARCHAR(200) NOT NULL, -- Nom au moment de la commande
    quantite INT NOT NULL,
    prix_unitaire DECIMAL(10,2) NOT NULL, -- Prix au moment de la commande
    total DECIMAL(10,2) NOT NULL,
    
    INDEX idx_details_commande (commande_id),
    INDEX idx_details_produit (produit_id),
    
    FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE,
    FOREIGN KEY (produit_id) REFERENCES products(id_product) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =========================================
-- Table: paiements
-- =========================================
DROP TABLE IF EXISTS paiements;
CREATE TABLE paiements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commande_id INT NOT NULL,
    methode_paiement VARCHAR(50) NOT NULL,
    statut ENUM('en_attente', 'valide', 'refuse', 'rembourse') DEFAULT 'en_attente',
    montant DECIMAL(10,2) NOT NULL,
    transaction_id VARCHAR(100),
    gateway_response TEXT, -- Réponse de la passerelle de paiement
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_paiements_commande (commande_id),
    INDEX idx_paiements_transaction (transaction_id),
    INDEX idx_paiements_statut (statut),
    
    FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =========================================
-- Table: offres_promotions
-- =========================================
DROP TABLE IF EXISTS offres_promotions;
CREATE TABLE offres_promotions (
    id_offre INT AUTO_INCREMENT PRIMARY KEY,
    nom_offre VARCHAR(100) NOT NULL,
    code_promo VARCHAR(50) UNIQUE,
    type_offre ENUM('pourcentage', 'montant_fixe', '2pour1', 'livraison_gratuite') NOT NULL,
    valeur DECIMAL(10,2) NOT NULL, -- Pourcentage ou montant
    montant_minimum DECIMAL(10,2) DEFAULT 0,
    quantite_maximum INT, -- Limite d'utilisation
    utilisations_actuelles INT DEFAULT 0,
    date_debut DATETIME NOT NULL,
    date_fin DATETIME NOT NULL,
    categories_concernees JSON, -- IDs des catégories concernées
    produits_concernes JSON, -- IDs des produits concernés
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_offres_code (code_promo),
    INDEX idx_offres_dates (date_debut, date_fin),
    INDEX idx_offres_active (is_active)
) ENGINE=InnoDB;

-- =========================================
-- Table: utilisations_promotions
-- =========================================
DROP TABLE IF EXISTS utilisations_promotions;
CREATE TABLE utilisations_promotions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    offre_id INT NOT NULL,
    user_id INT NOT NULL,
    commande_id INT NOT NULL,
    montant_reduction DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_user_offre_commande (offre_id, user_id, commande_id),
    INDEX idx_utilisations_offre (offre_id),
    INDEX idx_utilisations_user (user_id),
    
    FOREIGN KEY (offre_id) REFERENCES offres_promotions(id_offre) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id_client) ON DELETE CASCADE,
    FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================
-- Table: avis_produits
-- =========================================
DROP TABLE IF EXISTS avis_produits;
CREATE TABLE avis_produits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produit_id INT NOT NULL,
    user_id INT NOT NULL,
    note INT CHECK (note >= 1 AND note <= 5),
    titre VARCHAR(200),
    commentaire TEXT,
    is_verified BOOLEAN DEFAULT FALSE, -- Achat vérifié
    is_approved BOOLEAN DEFAULT FALSE, -- Modéré par admin
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_user_product_review (user_id, produit_id),
    INDEX idx_avis_produit (produit_id),
    INDEX idx_avis_user (user_id),
    INDEX idx_avis_note (note),
    INDEX idx_avis_approved (is_approved),
    
    FOREIGN KEY (produit_id) REFERENCES products(id_product) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id_client) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================
-- Table: favoris
-- =========================================
DROP TABLE IF EXISTS favoris;
CREATE TABLE favoris (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    produit_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_user_product_favorite (user_id, produit_id),
    INDEX idx_favoris_user (user_id),
    INDEX idx_favoris_produit (produit_id),
    
    FOREIGN KEY (user_id) REFERENCES users(id_client) ON DELETE CASCADE,
    FOREIGN KEY (produit_id) REFERENCES products(id_product) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================
-- Tables d'audit (pour traçabilité)
-- =========================================
DROP TABLE IF EXISTS audit_logs;
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(50) NOT NULL,
    record_id INT NOT NULL,
    action ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
    old_values JSON,
    new_values JSON,
    user_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_audit_table (table_name),
    INDEX idx_audit_record (record_id),
    INDEX idx_audit_action (action),
    INDEX idx_audit_user (user_id),
    INDEX idx_audit_date (created_at)
) ENGINE=InnoDB;

-- =========================================
-- Données de démonstration
-- =========================================

-- Catégories
INSERT INTO categories (name, slug, description, is_active) VALUES
('Sneakers', 'sneakers', 'Toutes nos sneakers tendance', TRUE),
('Jordan', 'jordan', 'Collection Air Jordan', TRUE),
('Nike', 'nike', 'Chaussures Nike', TRUE),
('Yeezy', 'yeezy', 'Collection Adidas Yeezy', TRUE),
('Accessoires', 'accessoires', 'Accessoires et vêtements', TRUE);

-- Utilisateurs de démo
INSERT INTO users (identifiant, email, mdp, role, firstname, lastname, is_active) VALUES
('admin', 'admin@sasneaks.com', '$2y$12$LQv3c1ydiCLQHqnX9XlUJe.Mz5/vgQq5Q5Q5Q5Q5Q5Q5Q5Q5Q5Q5Q5Q', 'admin', 'Admin', 'Système', TRUE),
('demo', 'demo@sasneaks.com', '$2y$12$LQv3c1ydiCLQHqnX9XlUJe.Mz5/vgQq5Q5Q5Q5Q5Q5Q5Q5Q5Q5Q5Q5Q', 'client', 'Client', 'Démo', TRUE);

-- Produits de démonstration (utilisant les images existantes)
INSERT INTO products (category_id, nom, slug, description, prix, stock, image_url, is_featured, is_active, sku) VALUES
(2, 'Jordan 11 Space Jam', 'jordan-11-space-jam', 'Paire iconique Space Jam', 855.00, 10, 'image/24D06AAB-595F-47F9-9EE6-686B66253AB2.JPG', TRUE, TRUE, 'J11-SJ-001'),
(2, 'Jordan 11 Cap and Gown', 'jordan-11-cap-gown', 'Élégance en noir', 200.00, 15, 'image/IMG_2175.jpg', TRUE, TRUE, 'J11-CG-001'),
(2, 'Jordan 4 Black Cat', 'jordan-4-black-cat', 'Classique tout noir', 860.00, 8, 'image/IMG_2772.jpg', TRUE, TRUE, 'J4-BC-001'),
(3, 'Nike SB Dunk High Pro Ishod Wair', 'nike-sb-dunk-ishod-wair', 'Collaboration Magnus Walker', 800.00, 5, 'image/A5D520CA-56CA-489D-A1AA-77AF602BB1A9.JPG', FALSE, TRUE, 'NK-SB-001'),
(2, 'Jordan 4 Fire Red', 'jordan-4-fire-red', 'Colorway iconique rouge', 500.00, 12, 'image/73E031B6-866B-4CAB-AEB0-1FBA7D44F306.JPG', FALSE, TRUE, 'J4-FR-001'),
(4, 'Yeezy 700 Cream', 'yeezy-700-cream', 'Confort et style', 750.00, 6, 'image/2EF18C6F-1505-4B28-8905-446B6ECE8F4A.JPG', TRUE, TRUE, 'YZ-700-001'),
(3, 'Dunk Low Peach Cream', 'dunk-low-peach-cream', 'Couleurs douces', 120.00, 20, 'image/145E6441-4F21-4710-A08A-2252886B94E5.JPG', FALSE, TRUE, 'NK-DL-001'),
(2, 'Jordan 11 Blue Gamma', 'jordan-11-blue-gamma', 'Bleu élégant', 540.00, 7, 'image/IMG_2330.jpg', FALSE, TRUE, 'J11-BG-001'),
(2, 'Jordan 1 Peach Mocha', 'jordan-1-peach-mocha', 'Teintes chaudes', 350.00, 18, 'image/IMG_2959.jpg', FALSE, TRUE, 'J1-PM-001'),
(2, 'Jordan 11 Low Georgetown', 'jordan-11-low-georgetown', 'Version basse', 300.00, 25, 'image/IMG_3863.jpg', FALSE, TRUE, 'J11L-GT-001'),
(2, 'Jordan 4 Fear Pack', 'jordan-4-fear-pack', 'Collection Fear', 500.00, 9, 'image/IMG_4463.jpg', FALSE, TRUE, 'J4-FP-001'),
(4, 'Yeezy 700 V3 Black', 'yeezy-700-v3-black', 'Version V3 noire', 600.00, 11, 'image/IMG_5079.jpg', FALSE, TRUE, 'YZ-700V3-001');

SET FOREIGN_KEY_CHECKS = 1;

-- =========================================
-- Triggers pour l'audit automatique
-- =========================================

DELIMITER $$

-- Trigger pour audit des utilisateurs
CREATE TRIGGER audit_users_insert AFTER INSERT ON users
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, record_id, action, new_values, created_at)
    VALUES ('users', NEW.id_client, 'INSERT', 
            JSON_OBJECT('identifiant', NEW.identifiant, 'email', NEW.email, 'role', NEW.role),
            NOW());
END$$

CREATE TRIGGER audit_users_update AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, record_id, action, old_values, new_values, created_at)
    VALUES ('users', NEW.id_client, 'UPDATE',
            JSON_OBJECT('identifiant', OLD.identifiant, 'email', OLD.email, 'role', OLD.role),
            JSON_OBJECT('identifiant', NEW.identifiant, 'email', NEW.email, 'role', NEW.role),
            NOW());
END$$

-- Trigger pour audit du panier
CREATE TRIGGER audit_panier_insert AFTER INSERT ON panier
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, record_id, action, new_values, user_id, created_at)
    VALUES ('panier', NEW.id, 'INSERT',
            JSON_OBJECT('user_id', NEW.user_id, 'produit_id', NEW.produit_id, 'quantite', NEW.quantite),
            NEW.user_id, NOW());
END$$

CREATE TRIGGER audit_panier_delete AFTER DELETE ON panier
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, record_id, action, old_values, user_id, created_at)
    VALUES ('panier', OLD.id, 'DELETE',
            JSON_OBJECT('user_id', OLD.user_id, 'produit_id', OLD.produit_id, 'quantite', OLD.quantite),
            OLD.user_id, NOW());
END$$

DELIMITER ;
