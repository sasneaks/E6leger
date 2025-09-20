<?php
session_start();
require_once(__DIR__ . '/../config/functions.php'); 
require_once(__DIR__ . '/../config/db_connect.php'); 

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['connectedUser'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = intval($_SESSION['connectedUser']['id_client']);

// Vérifier si les fonctions existent
if (!function_exists('removeCartItem') || !function_exists('emptyCart')) {
    die("Erreur : Fonction introuvable !");
}

// **1️⃣ Supprimer tout le panier**
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['emptycart'])) {
    if (emptyCart($user_id)) {
        header('Location: ../index.php'); // Redirection après suppression
        exit();
    } else {
        echo "Erreur : Impossible de vider le panier.";
    }
}

// **2️⃣ Supprimer un article spécifique**
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['produit_id'])) {
    $produit_id = intval($_POST['produit_id']);

    if ($produit_id > 0) {
        if (removeCartItem($user_id, $produit_id)) {
            header('Location: ../index.php'); // Redirection après suppression
            exit();
        } else {
            echo "Erreur : Impossible de supprimer cet article.";
        }
    } else {
        echo "Erreur : ID du produit invalide.";
    }
}

?>
