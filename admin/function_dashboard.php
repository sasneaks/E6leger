<?php
require_once(__DIR__.'/../config/db_connect.php');
$pdo=connectDB();
$stmt = $pdo->prepare('SELECT * FROM users');
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

function checkAdmin() {
    if ($_SESSION['connectedUser']['role'] !== 'admin') {
        header('Location: ../index.php');
        exit;
    }
}function CheckLog(){
    if (!isset($_SESSION['connectedUser'])) {
        header('Location:../index.php');
        exit;
    }
}

?>