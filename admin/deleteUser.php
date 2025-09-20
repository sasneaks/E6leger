<?php

require_once(__DIR__.'/../config/db_connect.php');
$pdo=connectDB();

if (isset($_GET['delete'])){

        $id_client = $_GET['delete'];
        var_dump($id_client);
      
            // Suppression de l'utilisateur dans la base de données
            $stmt = $pdo->prepare('DELETE FROM users WHERE id_client = ?');
            if ($stmt->execute([$id_client])) {
                // Suppression réussie, redirection
                header("Location: dashboard.php");
                exit();
            } else {
                echo "Erreur lors de la suppression de l'utilisateur.";
            }
} 
?>