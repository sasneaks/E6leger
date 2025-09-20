<?php
require_once('../config/db_connect.php');
require_once('OfferManager.php');

function applyOffer($cart, $pdo) {
    foreach ($cart as &$item) {
        // Vérifier si le produit a une offre active
        $stmt = $pdo->prepare("
            SELECT * FROM offers 
            WHERE category_id = (
                SELECT category_id FROM products WHERE id_product = ?
            ) 
            AND NOW() BETWEEN start_date AND end_date
        ");
        $stmt->execute([$item['product_id']]);
        $offer = $stmt->fetch(PDO::FETCH_ASSOC);

        // Appliquer l'offre "1 acheté, 1 offert"
        if ($offer && $offer['offer_type'] === '1 acheté, 1 offert') {
            $item['quantity'] += floor($item['quantity'] / 2); // Ajouter les articles gratuits
            $item['is_offer_applied'] = true; // Marquer l'offre comme appliquée
        }
    }
    return $cart;

}
?>