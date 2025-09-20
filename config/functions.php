<?php
$file_path = __DIR__ . '/db_connect.php'; // Chemin attendu

if (!file_exists($file_path)) {
    die("Erreur : Le fichier db_connect.php est introuvable à l'emplacement : " . $file_path);
}

require_once($file_path);


function getCartItems($user_id) {
    $pdo = connectDB();

    $stmt = $pdo->prepare("
        SELECT p.nom, p.image_url, p.prix, pa.quantite, pa.produit_id 
        FROM panier pa
        JOIN products p ON pa.produit_id = p.id_product
        WHERE pa.user_id = ?
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

require_once(__DIR__ . '/db_connect.php');

function getCartCount($user_id) {
    $pdo = connectDB();

    $stmt = $pdo->prepare("
        SELECT SUM(quantite) AS total 
        FROM panier 
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result['total'] ?? 0; // Retourne 0 si aucun article dans le panier
}
function emptycart($user_id) {
    $pdo = connectDB();
    $stmt = $pdo->prepare("DELETE FROM panier WHERE user_id = ?");
    return $stmt->execute([$user_id]);
}
function removeCartItem($user_id, $produit_id) {
    $pdo = connectDB();
    
    $stmt = $pdo->prepare("DELETE FROM panier WHERE user_id = ? AND produit_id = ?");
    return $stmt->execute([$user_id, $produit_id]);
}
?>

