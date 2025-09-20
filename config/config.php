<?php
/**
 * Configuration générale de l'application
 * Copiez ce fichier en config.php et modifiez les valeurs selon votre environnement
 */

return [
    // Configuration de la base de données
    'database' => [
        'host' => 'localhost',
        'port' => '8889', // Port par défaut MAMP (8888 pour l'interface web, 8889 pour MySQL)
        'dbname' => 'sasneaks_db',
        'username' => 'root',
        'password' => 'root', // Mot de passe par défaut MAMP
        'charset' => 'utf8mb4'
    ],

    // Configuration de l'application
    'app' => [
        'name' => 'Sasneaks',
        'env' => 'development', // development, production
        'debug' => true,
        'url' => 'http://localhost:8888/e-commerce',
        'timezone' => 'Europe/Paris'
    ],

    // Configuration de sécurité
    'security' => [
        'csrf_token_name' => '_token',
        'session_name' => 'SASNEAKS_SESSION',
        'password_cost' => 12,
        'remember_token_lifetime' => 86400 * 30, // 30 jours
    ],

    // Configuration email (pour les notifications)
    'mail' => [
        'driver' => 'smtp',
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => '',
        'password' => '',
        'encryption' => 'tls',
        'from_address' => 'noreply@sasneaks.com',
        'from_name' => 'Sasneaks'
    ],

    // Configuration uploads
    'upload' => [
        'max_size' => 5 * 1024 * 1024, // 5MB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'webp'],
        'path' => 'assets/images/uploads/'
    ],

    // Configuration pagination
    'pagination' => [
        'per_page' => 12,
        'admin_per_page' => 20
    ]
];
