<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../config/db_connect.php');

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['connectedUser']) || !isset($_SESSION['connectedUser']['id_client'])) {
    $_SESSION['error_message'] = "Vous devez être connecté pour accéder à cette page.";
    header('Location: ../auth/login.php');
    exit;
}

$userId = $_SESSION['connectedUser']['id_client'];

// Vérifier si l'ID de la commande est fourni
if (!isset($_GET['commande_id']) || !is_numeric($_GET['commande_id'])) {
    $_SESSION['error_message'] = "Commande introuvable.";
    header('Location: ../profile/profile.php');
    exit;
}

$commandeId = (int) $_GET['commande_id'];
$trackingCode = '';

try {
    // Connexion à la BDD
    $pdo = connectDB();
    if (!$pdo) {
        throw new Exception("Erreur de connexion à la base de données.");
    }
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Vérifier si la commande existe et appartient à l'utilisateur
    $stmt = $pdo->prepare("SELECT * FROM commande WHERE id = ? AND user_id = ?");
    $stmt->execute([$commandeId, $userId]);
    $commande = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$commande) {
        $_SESSION['error_message'] = "Commande introuvable ou non autorisée.";
        header('Location: ../profile/profile.php');
        exit;
    }
    
    // Si la commande a déjà un code de suivi, l'utiliser
    if (!empty($commande['tracking_code'])) {
        $trackingCode = $commande['tracking_code'];
    } else {
        // Générer un code de suivi unique
        $trackingCode = strtoupper(bin2hex(random_bytes(4)));
        
        // Mettre à jour la commande avec le statut "complete" et ajouter le code de suivi
        $updateStmt = $pdo->prepare("UPDATE commande SET statut = 'complete', tracking_code = ? WHERE id = ?");
        $updateStmt->execute([$trackingCode, $commandeId]);
    }
    
} catch (Exception $e) {
    error_log("Erreur dans confirmation.php: " . $e->getMessage());
    $_SESSION['error_message'] = "Une erreur s'est produite lors du traitement de la commande.";
    header('Location: ../profile/profile.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de Paiement - SASneaks</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .confirmation-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            text-align: center;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .success-icon {
            color: #2ecc71;
            font-size: 60px;
            margin-bottom: 20px;
        }
        h1 {
            color: #2ecc71;
            margin-bottom: 20px;
        }
        p {
            font-size: 18px;
            margin-bottom: 15px;
        }
        .tracking-code {
            display: inline-block;
            padding: 10px 20px;
            font-size: 24px;
            font-weight: bold;
            color: #3498db;
            background-color: #e8f4fc;
            border-radius: 5px;
            margin: 20px 0;
        }
        .buttons {
            margin-top: a30px;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .button:hover {
            background-color: #2980b9;
        }
        .order-details {
            margin-top: 30px;
            padding: 20px;
            background-color: white;
            border-radius: 5px;
            text-align: left;
        }
        .order-details h3 {
            margin-top: 0;
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="success-icon">✅</div>
        <h1>Paiement confirmé !</h1>
        <p>Merci pour votre achat. Votre commande a été traitée avec succès.</p>
        
        <div>
            <p>Votre code de suivi est :</p>
            <div class="tracking-code"><?php echo htmlspecialchars($trackingCode); ?></div>
            <p>Conservez ce code pour suivre l'état de votre commande.</p>
        </div>
        
        <div class="order-details">
            <h3>Détails de la commande #<?php echo $commandeId; ?></h3>
            <p><strong>Date :</strong> <?php echo date('d/m/Y H:i', strtotime($commande['commande_le'])); ?></p>
            <p><strong>Montant total :</strong> <?php echo number_format($commande['montant_total'], 2); ?> €</p>
            <p><strong>Statut :</strong> Payée</p>
        </div>
        
        <div class="buttons">
            <a href="/e-commerce/profile/profile.php" class="button">Voir mes commandes</a>
            <a href="/e-commerce/index.php" class="button">Retour à l'accueil</a>
        </div>
    </div>
</body>
</html>