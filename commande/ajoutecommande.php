<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../config/db_connect.php');
require_once(__DIR__ . '/function_commande.php');
require_once(__DIR__ . '/../panier/ajouterPanier.php');
require_once(__DIR__ . '/../config/functions.php');

// Vérifie si la fonction est bien incluse
if (!function_exists('emptycart')) {
    die("Erreur : La fonction emptycart() n'est pas disponible !");
}

if (isset($_SESSION['connectedUser'])) {
    $user_id = $_SESSION['connectedUser']['id_client'];
    
    // Récupérer la connexion à la base de données si nécessaire
    if (!isset($pdo) || !$pdo) {
        $pdo = connectDB();
        if (!$pdo) {
            die("Erreur de connexion à la base de données.");
        }
    }
    
    $commande_id = Ajoutcommande($user_id);    
    
    if ($commande_id) {
        // Vider le panier après commande
        emptycart($user_id);
        
        // Redirection avec un chemin absolu
        header('Location: /e-commerce/profile/profile.php');
        exit();
    } else {
        echo "Erreur lors du passage de la commande.";
    }
} else {
    // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
    header('Location: ../auth/login.php');
    exit();
}
?>