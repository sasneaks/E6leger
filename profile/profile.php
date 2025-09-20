<?php
// Inclure les fichiers nécessaires
require_once '../auth/fonctionlogin.php'; // Qui contient connectDB()
require_once '../commande/function_commande.php';

// Vérification de la session
if (!isset($_SESSION['connectedUser'])) {
    header('Location:../index.php');
    exit;
}

$user_id = $_SESSION['connectedUser']['id_client'];

// Établir la connexion à la base de données
$pdo = connectDB();

// Récupération des commandes
$stmt = $pdo->prepare('SELECT id, montant_total, statut FROM commande WHERE user_id = :user_id');
$stmt->execute(['user_id' => $user_id]);
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération des informations de l'utilisateur
$stmt = $pdo->prepare('SELECT identifiant, email, created_at FROM users WHERE id_client = :user_id');
$stmt->execute(['user_id' => $user_id]);
$user_info = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon profil</title>
    <link rel="stylesheet" href="../css/profile.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>Mon Profil</h2>
            <ul>
                <li><a href="../index.php">Accueil</a></li>
                <li><a href="#orders">Mes Commandes</a></li>
                <li><a href="#profile-info">Informations personnelles</a></li>
                <li><a href="../config/logout.php" class="logout-btn">Se Déconnecter</a></li>
            </ul>
        </div>
        <div class="main-content">
            <h1>Bienvenue <?php echo htmlspecialchars($_SESSION['connectedUser']['identifiant']); ?></h1>
            
            <div id="orders" class="orders">
                <h2>Mes Commandes</h2>
                <?php
                if (!empty($commandes)) {
                    foreach ($commandes as $res) { ?>
                        <div class="order-item">
                            <h3>Commande #<?php echo htmlspecialchars($res['id']); ?></h3>
                            <p>Statut: <?php echo htmlspecialchars($res['statut']); ?></p>
                            <p>Total : <?php echo number_format($res['montant_total'], 2); ?> €</p>
                            <?php if ($res['statut'] === 'en attente') { ?>
                                <!-- Bouton Payer -->
                                <form action="../paiement/checkout.php" method="GET">
                                    <input type="hidden" name="commande_id" value="<?php echo htmlspecialchars($res['id']); ?>">
                                    <button type="submit">Payer</button>
                                </form>
                            <?php } else { ?>
                                <p><strong>Déjà payé</strong></p>
                            <?php } ?>
                        </div>
                <?php }
                } else { ?>
                    <p>Vous n'avez encore passé aucune commande.</p>
                <?php } ?>
            </div>
            
            <div id="profile-info" class="profile-info">
                <h2>Informations personnelles</h2>
                <div class="info-card">
                    <div class="info-item">
                        <span class="info-label">Nom d'utilisateur:</span>
                        <span class="info-value"><?php echo htmlspecialchars($user_info['identifiant'] ?? $_SESSION['connectedUser']['identifiant']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email:</span>
                        <span class="info-value"><?php echo htmlspecialchars($user_info['email'] ?? $_SESSION['connectedUser']['email']); ?></span>
                    </div>
                    <?php if (isset($user_info['created_at'])): ?>
                    <div class="info-item">
                        <span class="info-label">Membre depuis:</span>
                        <span class="info-value"><?php echo date('d/m/Y', strtotime($user_info['created_at'])); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>