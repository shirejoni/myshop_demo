<?php


namespace App\Model;

use App\Lib\Database;
use App\System\Model;

/**
 * @property Database Database
 */
class Review extends Model {

    public function insertReview($data) {
        $this->Database->query("INSERT INTO review (product_id, customer_id, text, author, rate, date_added, date_updated, status) VALUES 
        (:pID, :cID, :rText, :rAuthor, :rRate, :rDAdded, :rDUpdated, :rStatus)", array(
            'pID'   => $data['product_id'],
            'cID'   => $data['customer_id'],
            'rAuthor'=> $data['author'],
            'rText' => $data['text'],
            'rRate' => $data['rate'],
            'rDAdded'=> $data['date_added'],
            'rDUpdated'=> $data['date_updated'],
            'rStatus'   => $data['status']
        ));

        $review_id = $this->Database->insertId();
        return $review_id;
    }

}