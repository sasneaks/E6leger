<?php
try {
    // Inclusions et configuration
    require_once(__DIR__ . '/../vendor/autoload.php');
    require_once(__DIR__ . '/../config/db_connect.php');
    require_once(__DIR__ . '/../config/env.php');
    
    // Définir l'API key de Stripe depuis les variables d'environnement
    $stripeSecretKey = env('STRIPE_SECRET_KEY');
    if (!$stripeSecretKey) {
        throw new Exception("Clé Stripe non configurée. Vérifiez votre fichier .env");
    }
    \Stripe\Stripe::setApiKey($stripeSecretKey);
    
    // Configurer les headers
    header('Content-Type: application/json');
    
    // Récupérer et valider les données de la requête
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data || !isset($data['commande_id']) || !is_numeric($data['commande_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de commande manquant ou invalide.']);
        exit;
    }
    
    $commandeId = (int)$data['commande_id'];
    
    // Vérifier si la commande existe dans la BDD
    $pdo = connectDB();
    if (!$pdo) {
        throw new Exception("Erreur de connexion à la base de données.");
    }
    
    $stmt = $pdo->prepare("SELECT montant_total, statut FROM commande WHERE id = ?");
    $stmt->execute([$commandeId]);
    $commande = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$commande) {
        http_response_code(404);
        echo json_encode(['error' => 'Commande introuvable.']);
        exit;
    }
    
    // Vérifier si la commande n'a pas déjà été payée
    if ($commande['statut'] === 'complete') {
        http_response_code(400);
        echo json_encode(['error' => 'Cette commande a déjà été payée.']);
        exit;
    }
    
    $totalAmount = (int)($commande['montant_total'] * 100); // Stripe attend le montant en centimes
    
    // Créer l'intention de paiement
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $totalAmount,
        'currency' => 'eur',
        'payment_method_types' => ['card'],
        'metadata' => [
            'commande_id' => $commandeId
        ],
        'description' => "Paiement pour la commande #$commandeId"
    ]);
    
    echo json_encode([
        'clientSecret' => $paymentIntent->client_secret,
        'amount' => $totalAmount / 100,
        'currency' => 'eur'
    ]);
    
} catch (\Stripe\Exception\ApiErrorException $e) {
    // Gestion des erreurs Stripe
    http_response_code(400);
    error_log("Erreur Stripe: " . $e->getMessage());
    echo json_encode(['error' => 'Erreur de paiement: ' . $e->getMessage()]);
} catch (Exception $e) {
    // Gestion des autres erreurs
    http_response_code(500);
    error_log("Erreur serveur: " . $e->getMessage());
    echo json_encode(['error' => 'Erreur serveur: Une erreur est survenue lors du traitement de votre demande.']);
}
