<?php
require_once(__DIR__.'/../config/db_connect.php');
$pdo = connectDB();

// Vérifier si le formulaire a été soumis avec les données nécessaires
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_client'], $_POST['identifiant'], $_POST['email'], $_POST['role'])) {
    $id_client = $_POST['id_client'];
    $identifiant = $_POST['identifiant'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    // Mise à jour des informations de l'utilisateur dans la base de données
    $stmt = $pdo->prepare('UPDATE users SET identifiant = ?, email = ?, role = ? WHERE id_client = ?');
    if ($stmt->execute([$identifiant, $email, $role, $id_client])) {
        header("Location: dashboard.php");
        exit();
    } 
} 
?>