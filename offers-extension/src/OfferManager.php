<?php
require_once('Offer.php');

class OfferManager {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function addOffer(Offer $offer) {
        $stmt = $this->pdo->prepare("
            INSERT INTO offers (category_id, offer_type, start_date, end_date)
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([
            $offer->category_id,
            $offer->offer_type,
            $offer->start_date,
            $offer->end_date
        ]);
    }

    public function getOffers() {
        $stmt = $this->pdo->query("SELECT * FROM offers");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteOffer($id_offer) {
        $stmt = $this->pdo->prepare("DELETE FROM offers WHERE id_offer = ?");
        return $stmt->execute([$id_offer]);
    }
}
?>