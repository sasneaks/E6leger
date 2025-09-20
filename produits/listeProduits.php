<?php
require_once(__DIR__.'/../config/db_connect.php');

function getProduits() {
    $pdo = connectDB();

$sql = "SELECT * FROM products";
$stmt = $pdo -> prepare($sql);
$stmt -> execute();
$produits = $stmt ->fetchAll(PDO::FETCH_ASSOC);

return $produits;
}

function getNouveauxProduits()
{
    $conn = connectDB();
    // Requête pour récupérer les produits sortis dans le dernier mois
    $sql = "SELECT * FROM products WHERE date_sortie >= CURDATE() - INTERVAL 1 MONTH";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $nouveaux_produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$nouveaux_produits) {
        $sql = "SELECT * FROM products ORDER BY date_sortie DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $nouveaux_produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return $nouveaux_produits;
    
}
function getSliderImages() {
    $pdo = connectDB();
    $stmt = $pdo->prepare('SELECT id_product, nom, image_url, description FROM products ORDER BY id_product LIMIT 3');
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>