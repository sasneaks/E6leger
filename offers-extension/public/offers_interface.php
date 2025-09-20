<?php
require_once('../../config/db_connect.php');
require_once(__DIR__.'/../src/OfferManager.php');

$pdo = connectDB();
$offerManager = new OfferManager($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_offer'])) {
        $offer = new Offer($_POST['category_id'], $_POST['offer_type'], $_POST['start_date'], $_POST['end_date']);
        $offerManager->addOffer($offer);
    } elseif (isset($_POST['delete_offer'])) {
        $offerManager->deleteOffer($_POST['id_offer']);
    }
}

$offers = $offerManager->getOffers();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestion des Offres</title>
</head>
<body>
    <h1>Gestion des Offres</h1>

    <form method="POST">
        <label>Catégorie ID:</label>
        <input type="number" name="category_id" required>
        <label>Type d'offre:</label>
        <input type="text" name="offer_type" value="1 acheté, 1 offert" readonly>
        <label>Date de début:</label>
        <input type="datetime-local" name="start_date" required>
        <label>Date de fin:</label>
        <input type="datetime-local" name="end_date" required>
        <button type="submit" name="add_offer">Ajouter l'offre</button>
    </form>

    <h2>Offres en cours</h2>
    <ul>
        <?php foreach ($offers as $offer): ?>
            <li>
                ID: <?= $offer['id_offer'] ?> - Catégorie: <?= $offer['category_id'] ?> - Type: <?= $offer['offer_type'] ?> - Période: <?= $offer['start_date'] ?> à <?= $offer['end_date'] ?>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="id_offer" value="<?= $offer['id_offer'] ?>">
                    <button type="submit" name="delete_offer">Supprimer</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>