<?php

/**
 * Front Controller - Point d'entrée principal de l'application
 * Architecture MVC professionnelle pour Sasneaks E-commerce
 */

// Configuration des erreurs pour le développement
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Démarrage du buffer de sortie
ob_start();

// Définition du chemin racine
define('ROOT_PATH', dirname(__DIR__));

// Inclusion des classes Core
require_once ROOT_PATH . '/app/Core/Database.php';
require_once ROOT_PATH . '/app/Core/Session.php';
require_once ROOT_PATH . '/app/Core/Security.php';
require_once ROOT_PATH . '/app/Core/Validator.php';
require_once ROOT_PATH . '/app/Core/Controller.php';
require_once ROOT_PATH . '/app/Core/Router.php';

// Inclusion des Models
require_once ROOT_PATH . '/app/Models/User.php';
require_once ROOT_PATH . '/app/Models/Product.php';
require_once ROOT_PATH . '/app/Models/Cart.php';

// Initialisation de la session
$session = Session::getInstance();

// Initialisation du routeur
$router = new Router();

// === ROUTES PUBLIQUES ===

// Page d'accueil
$router->get('/', 'Home@index', 'home');

// Pages produits
$router->get('/products', 'Product@index', 'products');
$router->get('/products/search', 'Product@search', 'products.search');
$router->get('/product/{slug}', 'Product@show', 'product.show');
$router->get('/category/{slug}', 'Product@category', 'category.show');

// Authentification
$router->get('/login', 'Auth@showLogin', 'login');
$router->post('/login', 'Auth@login', 'login.post');
$router->get('/register', 'Auth@showRegister', 'register');
$router->post('/register', 'Auth@register', 'register.post');
$router->get('/logout', 'Auth@logout', 'logout');

// === ROUTES PROTÉGÉES (UTILISATEURS CONNECTÉS) ===

// Panier
$router->get('/cart', 'Cart@index', 'cart');
$router->post('/cart/add', 'Cart@add', 'cart.add');
$router->post('/cart/update', 'Cart@update', 'cart.update');
$router->post('/cart/remove', 'Cart@remove', 'cart.remove');
$router->post('/cart/clear', 'Cart@clear', 'cart.clear');

// Commandes
$router->get('/checkout', 'Order@checkout', 'checkout');
$router->post('/checkout', 'Order@process', 'checkout.process');
$router->get('/orders', 'Order@index', 'orders');
$router->get('/order/{id}', 'Order@show', 'order.show');

// Profil utilisateur
$router->get('/profile', 'User@profile', 'profile');
$router->post('/profile', 'User@updateProfile', 'profile.update');
$router->get('/profile/orders', 'User@orders', 'profile.orders');

// === ROUTES ADMIN ===

$router->get('/admin', 'Admin@dashboard', 'admin.dashboard');
$router->get('/admin/users', 'Admin@users', 'admin.users');
$router->get('/admin/products', 'Admin@products', 'admin.products');
$router->get('/admin/orders', 'Admin@orders', 'admin.orders');

// === GESTION DES ERREURS ===
try {
    // Résolution de la route
    $router->resolve();
} catch (Exception $e) {
    // Log de l'erreur
    error_log("Erreur application: " . $e->getMessage());
    
    // Affichage d'une page d'erreur générique en production
    if (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'production') {
        http_response_code(500);
        include ROOT_PATH . '/app/Views/errors/500.php';
    } else {
        // Affichage de l'erreur détaillée en développement
        echo '<h1>Erreur de l\'application</h1>';
        echo '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p><strong>Fichier:</strong> ' . $e->getFile() . ' (ligne ' . $e->getLine() . ')</p>';
        echo '<pre>' . $e->getTraceAsString() . '</pre>';
    }
}

// Nettoyage du buffer
ob_end_flush();
