<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../config/functions.php');
require_once(__DIR__ . '/../config/db_connect.php');
require_once(__DIR__ . '/../panier/ajouterPanier.php'); // Pour accéder à getCartItems()

// Vérifier si la fonction est bien trouvée
if (!function_exists('getCartItems')) {
    die("Erreur : La fonction getCartItems() n'est pas disponible !");
}

function Ajoutcommande($user_id) {
    $pdo = connectDB();
    if (!$pdo) {
        die("Erreur de connexion à la base de données.");
    }
    
    $commande_id = null;
    
    try {
        // Vérifier si l'utilisateur existe
        if (!isset($user_id) || empty($user_id)) {
            die("Erreur : Aucun utilisateur connecté.");
        }
        
        // Récupérer les articles du panier
        $cart_items = getCartItems($user_id);
        if (empty($cart_items)) {
            return null; // Aucun article dans le panier
        }
        
        // Calculer le prix total
        $total_price = 0;
        foreach ($cart_items as $item) {
            $total_price += $item['prix'] * $item['quantite'];
        }
        
        // Insérer la commande
        $stmt = $pdo->prepare('INSERT INTO commande (user_id, commande_le, montant_total, statut) VALUES (?, NOW(), ?, "en attente")');
        $stmt->execute([$user_id, $total_price]);
        $commande_id = $pdo->lastInsertId();
        
        // Insérer les détails de la commande
        foreach ($cart_items as $item) {
            $stmt = $pdo->prepare('INSERT INTO details_commande (commande_id, produit_id, quantite, prix_a_achat) VALUES (?, ?, ?, ?)');
            $stmt->execute([
                $commande_id,
                $item['produit_id'],
                $item['quantite'],
                $item['prix']
            ]);
        }
        
        // Générer un code de suivi (optionnel)
        $tracking_code = strtoupper(bin2hex(random_bytes(4)));
        $stmt = $pdo->prepare('UPDATE commande SET tracking_code = ? WHERE id = ?');
        $stmt->execute([$tracking_code, $commande_id]);
        
        return $commande_id;
    }
    catch (PDOException $e) {
        error_log("Erreur lors de la création de la commande : " . $e->getMessage());
        return null;
    }
}

// Fonction pour obtenir les détails d'une commande
function getCommandeDetails($commande_id) {
    $pdo = connectDB();
    if (!$pdo) {
        return null;
    }
    
    try {
        // Récupérer les informations de la commande
        $stmt = $pdo->prepare('
            SELECT c.*, u.identifiant as client_name
            FROM commande c
            JOIN users u ON c.user_id = u.id_client
            WHERE c.id = ?
        ');
        $stmt->execute([$commande_id]);
        $commande = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$commande) {
            return null;
        }
        
        // Récupérer les produits de la commande
        $stmt = $pdo->prepare('
            SELECT dc.*, p.nom, p.image_url
            FROM details_commande dc
            JOIN products p ON dc.produit_id = p.id_product
            WHERE dc.commande_id = ?
        ');
        $stmt->execute([$commande_id]);
        $commande['produits'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $commande;
    }
    catch (PDOException $e) {
        error_log("Erreur lors de la récupération des détails de la commande : " . $e->getMessage());
        return null;
    }
}
?>