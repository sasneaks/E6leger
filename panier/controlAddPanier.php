<?php
session_start(); // Assurer que la session est active
require_once('./ajouterPanier.php');
require_once('../config/db_connect.php');

$pdo = connectDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et validation des données du formulaire
    $id_produit = filter_input(INPUT_POST, 'id_product', FILTER_VALIDATE_INT);
    $id_utilisateur = filter_input(INPUT_POST, 'id_client', FILTER_VALIDATE_INT);
    $quantite = filter_input(INPUT_POST, 'quantite', FILTER_VALIDATE_INT);
    $prix = filter_input(INPUT_POST, 'prix', FILTER_VALIDATE_FLOAT);

    // Vérification des données reçues
    if (!$id_produit || !$id_utilisateur || !$quantite || !$prix) {
        echo "<div class='error-message'>Erreur : Les données envoyées sont invalides.</div>";
        exit;
    }

    // Vérifier si le produit existe bien dans la base de données
    $stmt = $pdo->prepare("SELECT id_product FROM products WHERE id_product = ?");
    $stmt->execute([$id_produit]);
    if (!$stmt->fetch()) {
        echo "<div class='error-message'>Erreur : Ce produit n'existe pas.</div>";
        exit;
    }




    // Préparer les données pour l'ajout au panier (sans `nom`)
    $data = [
        'produit_id' => $id_produit,
        'user_id' => $id_utilisateur,
        'quantite' => $quantite,
        'prix' => $prix
    ];
    
    if (ajoutPanier($data)) {
        header('Location: ../index.php');
        exit;
    } 
    else {
        echo "<div class='error-message'>Un problème est survenu. Veuillez réessayer plus tard.</div>";
    }
    
}
?>
