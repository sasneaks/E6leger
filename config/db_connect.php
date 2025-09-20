
<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}





// Connexion à la base de données
function connectDB(){


$host = 'localhost';
$dbname = 'u821579703_shrew'; // Assurez-vous que ce nom est correct
$username = 'u821579703_shrew'; // Nom d'utilisateur MySQL
$password = 'v&Ft0CEX2Xo!'; // Mot de passe MySQL

//$host = 'localhost';
//$dbname = 'sasneaks'; // Assurez-vous que ce nom est correct
//$username = 'root'; // Nom d'utilisateur MySQL
//$password = ''; // Mot de passe MySQL


try {
    // Crée une connexion PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
} catch (PDOException $e) {
    // Gestion des erreurs
    die("Erreur de connexion : " . $e->getMessage());

}
}
    function closeDB($pdo){
        $pdo=null;
    }

?> 