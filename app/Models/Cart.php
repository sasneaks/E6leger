<?php

/**
 * Model Cart - Gestion du panier
 */
class Cart
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Ajoute un produit au panier
     */
    public function add(int $userId, int $productId, int $quantity = 1): bool
    {
        // Vérifier si le produit existe et est disponible
        $product = new Product();
        $productData = $product->findById($productId);
        
        if (!$productData || $productData['stock'] < $quantity) {
            return false;
        }

        // Vérifier si le produit est déjà dans le panier
        $existing = $this->getItem($userId, $productId);
        
        if ($existing) {
            // Mettre à jour la quantité
            $newQuantity = $existing['quantite'] + $quantity;
            
            if ($newQuantity > $productData['stock']) {
                return false; // Stock insuffisant
            }
            
            return $this->updateQuantity($userId, $productId, $newQuantity);
        } else {
            // Ajouter un nouvel item
            $sql = "INSERT INTO panier (user_id, produit_id, quantite, prix, expires_at) 
                    VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY))";
            
            return $this->db->execute($sql, [
                $userId,
                $productId,
                $quantity,
                $productData['prix']
            ]);
        }
    }

    /**
     * Met à jour la quantité d'un produit dans le panier
     */
    public function updateQuantity(int $userId, int $productId, int $quantity): bool
    {
        if ($quantity <= 0) {
            return $this->remove($userId, $productId);
        }

        // Vérifier le stock disponible
        $product = new Product();
        $productData = $product->findById($productId);
        
        if (!$productData || $productData['stock'] < $quantity) {
            return false;
        }

        $sql = "UPDATE panier SET quantite = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE user_id = ? AND produit_id = ?";
        
        return $this->db->execute($sql, [$quantity, $userId, $productId]);
    }

    /**
     * Supprime un produit du panier
     */
    public function remove(int $userId, int $productId): bool
    {
        $sql = "DELETE FROM panier WHERE user_id = ? AND produit_id = ?";
        return $this->db->execute($sql, [$userId, $productId]);
    }

    /**
     * Vide complètement le panier d'un utilisateur
     */
    public function clear(int $userId): bool
    {
        $sql = "DELETE FROM panier WHERE user_id = ?";
        return $this->db->execute($sql, [$userId]);
    }

    /**
     * Récupère tous les items du panier d'un utilisateur
     */
    public function getItems(int $userId): array
    {
        $sql = "SELECT p.*, pr.nom, pr.image_url, pr.stock, pr.slug
                FROM panier p
                INNER JOIN products pr ON p.produit_id = pr.id_product
                WHERE p.user_id = ? AND pr.is_active = TRUE
                ORDER BY p.created_at DESC";
        
        return $this->db->query($sql, [$userId]);
    }

    /**
     * Récupère un item spécifique du panier
     */
    public function getItem(int $userId, int $productId): ?array
    {
        $sql = "SELECT * FROM panier WHERE user_id = ? AND produit_id = ?";
        return $this->db->queryOne($sql, [$userId, $productId]);
    }

    /**
     * Compte le nombre total d'articles dans le panier
     */
    public function getCount(int $userId): int
    {
        $sql = "SELECT SUM(quantite) as total FROM panier WHERE user_id = ?";
        $result = $this->db->queryOne($sql, [$userId]);
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Calcule le total du panier
     */
    public function getTotal(int $userId): array
    {
        $items = $this->getItems($userId);
        
        $subtotal = 0;
        $totalQuantity = 0;
        
        foreach ($items as $item) {
            $itemTotal = $item['prix'] * $item['quantite'];
            $subtotal += $itemTotal;
            $totalQuantity += $item['quantite'];
        }
        
        // Calcul de la TVA (20%)
        $taxRate = 0.20;
        $taxAmount = $subtotal * $taxRate;
        $total = $subtotal + $taxAmount;
        
        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total' => $total,
            'total_quantity' => $totalQuantity
        ];
    }

    /**
     * Valide le panier avant commande
     */
    public function validate(int $userId): array
    {
        $items = $this->getItems($userId);
        $errors = [];
        $validItems = [];
        
        foreach ($items as $item) {
            if ($item['stock'] < $item['quantite']) {
                $errors[] = "Stock insuffisant pour {$item['nom']} (disponible: {$item['stock']}, demandé: {$item['quantite']})";
            } else {
                $validItems[] = $item;
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'items' => $validItems
        ];
    }

    /**
     * Transfère le panier vers une commande
     */
    public function transferToOrder(int $userId, int $orderId): bool
    {
        $this->db->beginTransaction();
        
        try {
            $items = $this->getItems($userId);
            
            foreach ($items as $item) {
                // Créer une ligne de commande
                $sql = "INSERT INTO details_commandes (commande_id, produit_id, nom_produit, quantite, prix_unitaire, total) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                
                $total = $item['prix'] * $item['quantite'];
                
                $this->db->execute($sql, [
                    $orderId,
                    $item['produit_id'],
                    $item['nom'],
                    $item['quantite'],
                    $item['prix'],
                    $total
                ]);
                
                // Décrémenter le stock
                $product = new Product();
                $product->updateStock($item['produit_id'], $item['quantite'], 'decrease');
            }
            
            // Vider le panier
            $this->clear($userId);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Nettoie les paniers expirés
     */
    public function cleanExpired(): int
    {
        $sql = "DELETE FROM panier WHERE expires_at < NOW()";
        $this->db->execute($sql);
        
        // Retourne le nombre de lignes supprimées
        $result = $this->db->queryOne("SELECT ROW_COUNT() as deleted");
        return (int) $result['deleted'];
    }

    /**
     * Sauvegarde le panier pour les utilisateurs non connectés
     */
    public function saveGuestCart(string $sessionId, int $productId, int $quantity): bool
    {
        $product = new Product();
        $productData = $product->findById($productId);
        
        if (!$productData) {
            return false;
        }
        
        // Vérifier si l'item existe déjà
        $sql = "SELECT * FROM panier WHERE session_id = ? AND produit_id = ? AND user_id = 0";
        $existing = $this->db->queryOne($sql, [$sessionId, $productId]);
        
        if ($existing) {
            $sql = "UPDATE panier SET quantite = ?, updated_at = CURRENT_TIMESTAMP 
                    WHERE session_id = ? AND produit_id = ? AND user_id = 0";
            return $this->db->execute($sql, [$quantity, $sessionId, $productId]);
        } else {
            $sql = "INSERT INTO panier (user_id, produit_id, quantite, prix, session_id, expires_at) 
                    VALUES (0, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY))";
            
            return $this->db->execute($sql, [
                $productId,
                $quantity,
                $productData['prix'],
                $sessionId
            ]);
        }
    }

    /**
     * Récupère le panier d'un invité
     */
    public function getGuestItems(string $sessionId): array
    {
        $sql = "SELECT p.*, pr.nom, pr.image_url, pr.stock, pr.slug
                FROM panier p
                INNER JOIN products pr ON p.produit_id = pr.id_product
                WHERE p.session_id = ? AND p.user_id = 0 AND pr.is_active = TRUE
                ORDER BY p.created_at DESC";
        
        return $this->db->query($sql, [$sessionId]);
    }

    /**
     * Transfère le panier d'invité vers un utilisateur connecté
     */
    public function mergeGuestCart(string $sessionId, int $userId): bool
    {
        $guestItems = $this->getGuestItems($sessionId);
        
        foreach ($guestItems as $item) {
            $this->add($userId, $item['produit_id'], $item['quantite']);
        }
        
        // Supprimer le panier invité
        $sql = "DELETE FROM panier WHERE session_id = ? AND user_id = 0";
        return $this->db->execute($sql, [$sessionId]);
    }
}
