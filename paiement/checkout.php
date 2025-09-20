<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../config/db_connect.php'); // Connexion à la base

// Vérification correcte de la session
if (!isset($_SESSION['connectedUser']) || !is_array($_SESSION['connectedUser']) || !isset($_SESSION['connectedUser']['id_client'])) {
    // Redirection plus douce au lieu de die()
    $_SESSION['error_message'] = "Vous devez être connecté pour accéder à cette page.";
    header('Location: ../auth/login.php');
    exit;
}

$userId = $_SESSION['connectedUser']['id_client'];

if (!isset($_GET['commande_id'])) {
    $_SESSION['error_message'] = "Aucune commande sélectionnée.";
    header('Location: ../profile/profile.php');
    exit;
}

$commandeId = (int) $_GET['commande_id'];

// Obtenir la connexion à la base de données
$pdo = connectDB();
if (!$pdo) {
    $_SESSION['error_message'] = "Erreur de connexion à la base de données.";
    header('Location: ../profile/profile.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT montant_total, statut FROM commande WHERE id = ? AND user_id = ?");
    $stmt->execute([$commandeId, $userId]);
    $commande = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$commande) {
        $_SESSION['error_message'] = "Commande introuvable ou non autorisée.";
        header('Location: ../profile/profile.php');
        exit;
    }

    // Vérifier si la commande n'a pas déjà été payée
    if ($commande['statut'] === 'complete') {
        $_SESSION['info_message'] = "Cette commande a déjà été payée.";
        header('Location: ../profile/profile.php');
        exit;
    }

    $totalAmount = (int) ($commande['montant_total'] * 100); // Stripe demande le montant en centimes
    $stripePublicKey = 'pk_test_51LDnP3JTq9l9ulW6KrPXsLTKGqJuJV3R0uz67XBarvS7x3bIoqkgLfP9gGRLyrjWNQiI72UinKZ1xt15tNuXTlYw00E2fwmKGc'; // Remplace avec ta clé publique Stripe
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération de la commande: " . $e->getMessage());
    $_SESSION['error_message'] = "Une erreur s'est produite lors de la récupération des informations de commande.";
    header('Location: ../profile/profile.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement de votre commande - SASneaks</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        .payment-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        #card-element {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        button#submit {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button#submit:hover {
            background-color: #45a049;
        }
        #payment-message {
            margin-top: 10px;
            color: #e74c3c;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .summary-table th, .summary-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .summary-table th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <h1>Finaliser votre commande</h1>
        
        <h2>Résumé de votre commande #<?php echo $commandeId; ?></h2>
        <table class="summary-table">
            <tr>
                <th>Description</th>
                <th>Montant</th>
            </tr>
            <tr>
                <td>Commande #<?php echo $commandeId; ?></td>
                <td><?php echo number_format($totalAmount / 100, 2); ?> €</td>
            </tr>
        </table>
        
        <h2>Paiement sécurisé avec Stripe</h2>
        <p>Veuillez entrer vos informations de carte bancaire ci-dessous pour procéder au paiement.</p>
        
        <form id="payment-form">
            <div id="card-element">
                <!-- Stripe Card Element will be inserted here -->
            </div>
            <button id="submit">Payer <?php echo number_format($totalAmount / 100, 2); ?> €</button>
            <p id="payment-message"></p>
            <input type="hidden" id="commande_id" value="<?php echo $commandeId; ?>">
        </form>
        
        <p><a href="../profile/profile.php">Retour à mon profil</a></p>
    </div>
    
    <script>
        // Créer l'instance Stripe avec la clé publique
        const stripe = Stripe("<?php echo $stripePublicKey; ?>");
        
        // Créer les éléments
        const elements = stripe.elements();
        const cardElement = elements.create("card");
        cardElement.mount("#card-element");
        
        // Gérer la soumission du formulaire
        const form = document.getElementById("payment-form");
        const submitButton = document.getElementById("submit");
        const messageElement = document.getElementById("payment-message");
        
        form.addEventListener("submit", async (event) => {
            event.preventDefault();
            
            // Désactiver le bouton pour éviter les soumissions multiples
            submitButton.disabled = true;
            messageElement.textContent = "Traitement en cours...";
            
            try {
                const commandeId = document.getElementById("commande_id").value;
                
                // Créer l'intention de paiement
                const response = await fetch("/e-commerce/paiement/create-payment-intent.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ commande_id: commandeId })
                });
                
                if (!response.ok) {
                    throw new Error("Erreur réseau lors de la création de l'intention de paiement");
                }
                
                const result = await response.json();
                
                if (result.error) {
                    messageElement.textContent = result.error;
                    submitButton.disabled = false;
                    return;
                }
                
                // Confirmer le paiement avec Stripe
                const { paymentIntent, error: stripeError } = await stripe.confirmCardPayment(
                    result.clientSecret, 
                    { payment_method: { card: cardElement } }
                );
                
                if (stripeError) {
                    messageElement.textContent = stripeError.message;
                } else if (paymentIntent.status === "succeeded") {
                    messageElement.textContent = "Paiement réussi ! Redirection...";
                    messageElement.style.color = "#2ecc71";
                    window.location.href = "../paiement/confirmation.php?commande_id=" + commandeId;
                } else {
                    messageElement.textContent = "Statut de paiement inattendu. Veuillez contacter le support.";
                }
            } catch (error) {
                messageElement.textContent = "Une erreur s'est produite: " + error.message;
                console.error("Erreur:", error);
            }
            
            submitButton.disabled = false;
        });
    </script>
</body>
</html>