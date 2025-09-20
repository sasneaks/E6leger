<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../config/db_connect.php');

// Récupérer la connexion à la base de données
$pdo = connectDB();
if (!$pdo) {
    die("Erreur de connexion à la base de données.");
}

function ajoutPanier($data) {
    global $pdo; // S'assurer que $pdo est accessible dans la fonction

    // Vérifier si le produit existe
    $stmt = $pdo->prepare("SELECT nom, image_url FROM products WHERE id_product = ?");
    $stmt->execute([$data['produit_id']]);
    $produit = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$produit) {
        echo "<div class='error-message'>Erreur : Produit introuvable.</div>";
        return false;
    }

    // Insérer dans le panier sans `nom` et `image_url`
    $stmt = $pdo->prepare("INSERT INTO panier (user_id, produit_id, quantite, prix) 
                           VALUES (:user_id, :produit_id, :quantite, :prix)");

    return $stmt->execute([
        'user_id' => $data['user_id'],
        'produit_id' => $data['produit_id'],
        'quantite' => $data['quantite'],
        'prix' => $data['prix']
    ]);

    function getCartItems($user_id) {
        global $pdo;
        if (!$pdo) {
            $pdo = connectDB();
        }
    
        $stmt = $pdo->prepare("
            SELECT p.nom, p.image_url, p.prix, pa.quantite, pa.produit_id 
            FROM panier pa
            JOIN products p ON pa.produit_id = p.id_product
            WHERE pa.user_id = ?
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
}
