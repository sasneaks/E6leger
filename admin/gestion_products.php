<?php
require_once(__DIR__.'/../config/db_connect.php');

// Connexion à la base de données
$pdo = connectDB();

try {
    // Requête pour récupérer les produits
    $stmt = $pdo->prepare('SELECT id_product, nom, description, prix, image_url, date_sortie, image_hover_url FROM products');
    $stmt->execute();
    
    // Stocker les résultats dans un tableau
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erreur lors de la récupération des produits : " . $e->getMessage();
}
?>