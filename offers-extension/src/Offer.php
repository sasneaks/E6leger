<?php

class Offer {
    public $id_offer;
    public $category_id;
    public $offer_type;
    public $start_date;
    public $end_date;

    public function __construct($category_id, $offer_type, $start_date, $end_date, $id_offer = null) {
        $this->id_offer = $id_offer;
        $this->category_id = $category_id;
        $this->offer_type = $offer_type;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }
}
?>