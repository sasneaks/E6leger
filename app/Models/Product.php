<?php

/**
 * Model Product - Gestion des produits
 */
class Product
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Récupère tous les produits actifs avec pagination
     */
    public function getAll(int $page = 1, int $perPage = 12, array $filters = []): array
    {
        $offset = ($page - 1) * $perPage;
        $where = ['is_active = TRUE'];
        $params = [];

        // Filtres
        if (!empty($filters['category'])) {
            $where[] = 'category_id = ?';
            $params[] = $filters['category'];
        }

        if (!empty($filters['search'])) {
            $where[] = '(nom LIKE ? OR description LIKE ?)';
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($filters['price_min'])) {
            $where[] = 'prix >= ?';
            $params[] = $filters['price_min'];
        }

        if (!empty($filters['price_max'])) {
            $where[] = 'prix <= ?';
            $params[] = $filters['price_max'];
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);
        
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id_category 
                {$whereClause}
                ORDER BY p.created_at DESC 
                LIMIT ? OFFSET ?";
        
        $params[] = $perPage;
        $params[] = $offset;
        
        $products = $this->db->query($sql, $params);

        // Compter le total
        $countSql = "SELECT COUNT(*) as total FROM products p {$whereClause}";
        $countParams = array_slice($params, 0, -2); // Enlever LIMIT et OFFSET
        $total = $this->db->queryOne($countSql, $countParams)['total'];

        return [
            'data' => $products,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * Trouve un produit par son ID
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id_category 
                WHERE p.id_product = ? AND p.is_active = TRUE";
        
        return $this->db->queryOne($sql, [$id]);
    }

    /**
     * Trouve un produit par son slug
     */
    public function findBySlug(string $slug): ?array
    {
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id_category 
                WHERE p.slug = ? AND p.is_active = TRUE";
        
        return $this->db->queryOne($sql, [$slug]);
    }

    /**
     * Récupère les produits en vedette
     */
    public function getFeatured(int $limit = 6): array
    {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id_category 
                WHERE p.is_featured = TRUE AND p.is_active = TRUE 
                ORDER BY p.created_at DESC 
                LIMIT ?";
        
        return $this->db->query($sql, [$limit]);
    }

    /**
     * Récupère les nouveaux produits
     */
    public function getNew(int $limit = 6): array
    {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id_category 
                WHERE p.is_active = TRUE 
                ORDER BY p.created_at DESC 
                LIMIT ?";
        
        return $this->db->query($sql, [$limit]);
    }

    /**
     * Récupère les produits d'une catégorie
     */
    public function getByCategory(int $categoryId, int $limit = 12): array
    {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id_category 
                WHERE p.category_id = ? AND p.is_active = TRUE 
                ORDER BY p.created_at DESC 
                LIMIT ?";
        
        return $this->db->query($sql, [$categoryId, $limit]);
    }

    /**
     * Recherche de produits
     */
    public function search(string $query, int $page = 1, int $perPage = 12): array
    {
        $offset = ($page - 1) * $perPage;
        $searchTerm = "%{$query}%";
        
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id_category 
                WHERE (p.nom LIKE ? OR p.description LIKE ? OR c.name LIKE ?) 
                AND p.is_active = TRUE 
                ORDER BY p.created_at DESC 
                LIMIT ? OFFSET ?";
        
        $products = $this->db->query($sql, [$searchTerm, $searchTerm, $searchTerm, $perPage, $offset]);

        // Compter le total
        $countSql = "SELECT COUNT(*) as total FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id_category 
                     WHERE (p.nom LIKE ? OR p.description LIKE ? OR c.name LIKE ?) 
                     AND p.is_active = TRUE";
        $total = $this->db->queryOne($countSql, [$searchTerm, $searchTerm, $searchTerm])['total'];

        return [
            'data' => $products,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage),
            'query' => $query
        ];
    }

    /**
     * Crée un nouveau produit
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO products (category_id, nom, slug, description, prix, prix_promo, 
                stock, sku, image_url, image_hover_url, brand, is_featured, is_active,
                meta_title, meta_description) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        return $this->db->insert($sql, [
            $data['category_id'],
            $data['nom'],
            $data['slug'],
            $data['description'] ?? null,
            $data['prix'],
            $data['prix_promo'] ?? null,
            $data['stock'] ?? 0,
            $data['sku'] ?? null,
            $data['image_url'] ?? null,
            $data['image_hover_url'] ?? null,
            $data['brand'] ?? null,
            $data['is_featured'] ?? false,
            $data['is_active'] ?? true,
            $data['meta_title'] ?? null,
            $data['meta_description'] ?? null
        ]);
    }

    /**
     * Met à jour un produit
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $values = [];
        
        $allowedFields = ['category_id', 'nom', 'slug', 'description', 'prix', 'prix_promo', 
                         'stock', 'sku', 'image_url', 'image_hover_url', 'brand', 
                         'is_featured', 'is_active', 'meta_title', 'meta_description'];
        
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = $field . ' = ?';
                $values[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $values[] = $id;
        $sql = "UPDATE products SET " . implode(', ', $fields) . ", updated_at = CURRENT_TIMESTAMP WHERE id_product = ?";
        
        return $this->db->execute($sql, $values);
    }

    /**
     * Supprime un produit (soft delete)
     */
    public function delete(int $id): bool
    {
        $sql = "UPDATE products SET is_active = FALSE, updated_at = CURRENT_TIMESTAMP WHERE id_product = ?";
        return $this->db->execute($sql, [$id]);
    }

    /**
     * Met à jour le stock d'un produit
     */
    public function updateStock(int $id, int $quantity, string $operation = 'decrease'): bool
    {
        if ($operation === 'decrease') {
            $sql = "UPDATE products SET stock = stock - ?, updated_at = CURRENT_TIMESTAMP 
                    WHERE id_product = ? AND stock >= ?";
            return $this->db->execute($sql, [$quantity, $id, $quantity]);
        } else {
            $sql = "UPDATE products SET stock = stock + ?, updated_at = CURRENT_TIMESTAMP WHERE id_product = ?";
            return $this->db->execute($sql, [$quantity, $id]);
        }
    }

    /**
     * Vérifie si un SKU existe déjà
     */
    public function skuExists(string $sku, int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM products WHERE sku = ?";
        $params = [$sku];
        
        if ($excludeId) {
            $sql .= " AND id_product != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->queryOne($sql, $params);
        return $result['count'] > 0;
    }

    /**
     * Vérifie si un slug existe déjà
     */
    public function slugExists(string $slug, int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM products WHERE slug = ?";
        $params = [$slug];
        
        if ($excludeId) {
            $sql .= " AND id_product != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->queryOne($sql, $params);
        return $result['count'] > 0;
    }

    /**
     * Génère un slug unique
     */
    public function generateSlug(string $title, int $excludeId = null): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $originalSlug = $slug;
        $counter = 1;
        
        while ($this->slugExists($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    /**
     * Récupère les statistiques des produits
     */
    public function getStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_products,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_products,
                    SUM(CASE WHEN stock = 0 THEN 1 ELSE 0 END) as out_of_stock,
                    SUM(CASE WHEN stock <= stock_alert THEN 1 ELSE 0 END) as low_stock,
                    SUM(CASE WHEN is_featured = 1 THEN 1 ELSE 0 END) as featured_products,
                    AVG(prix) as average_price
                FROM products";
        
        return $this->db->queryOne($sql);
    }

    /**
     * Récupère les produits en rupture de stock
     */
    public function getOutOfStock(): array
    {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id_category 
                WHERE p.stock = 0 AND p.is_active = TRUE 
                ORDER BY p.nom";
        
        return $this->db->query($sql);
    }

    /**
     * Récupère les produits en stock faible
     */
    public function getLowStock(): array
    {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id_category 
                WHERE p.stock <= p.stock_alert AND p.stock > 0 AND p.is_active = TRUE 
                ORDER BY p.stock ASC, p.nom";
        
        return $this->db->query($sql);
    }
}
