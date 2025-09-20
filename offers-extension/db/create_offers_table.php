<?php
require_once('../config/db_connect.php');

$pdo = connectDB();

$sql = "
CREATE TABLE IF NOT EXISTS offers (
    id_offer INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    offer_type VARCHAR(50) NOT NULL,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id_category)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
";

if ($pdo->exec($sql) === false) {
    echo "Erreur lors de la création de la table 'offers': " . implode(", ", $pdo->errorInfo());
} else {
    echo "Table 'offers' créée avec succès.";
}
?>