<?php


namespace App\Model;


use App\Lib\Database;
use App\System\Model;

/**
 * @property Database Database
 */
class Coupon extends Model
{

    /**
     * @var array|bool
     */
    private $rows = [];

    public function insertCoupon($data) {
        $this->Database->query("INSERT INTO coupon (name, code, discount, type, minimum_price, date_start, date_end, `count`, status, date_added) VALUES 
        (:cName, :cCode, :cDiscount, :cType, :cMinimumPrice, :cDStart, :cDEnd, :cCount, :cStatus, :cDAdded)", array(
            'cName' => $data['name'],
            'cCode' => $data['code'],
            'cDiscount' => $data['discount'],
            'cType' => $data['type'],
            'cMinimumPrice' => $data['minimum_price'],
            'cDStart'       => $data['date_start'],
            'cDEnd'         => $data['date_end'],
            'cCount'        => $data['count'],
            'cStatus'       => $data['status'],
            'cDAdded'       => time()
        ));
        $coupon_id = $this->Database->insertId();
        if(isset($data['products_id'])) {
            foreach ($data['products_id'] as $product_id) {
                $this->Database->query("INSERT INTO coupon_product (coupon_id, product_id) VALUES (:cID, :pID)", array(
                    'cID'   => $coupon_id,
                    'pID'   => $product_id
                ));
            }
        }
        if(isset($data['categories_id'])) {
            foreach ($data['categories_id'] as $category_id) {
                $this->Database->query("INSERT INTO coupon_category (coupon_id, category_id) VALUES (:cID, :cCID)", array(
                    'cID'   => $coupon_id,
                    'cCID'  => $category_id
                ));
            }
        }
        return $coupon_id;
    }


    public function getCoupons($data = array()) {
        $data['sort'] = isset($data['sort']) ? $data['sort'] : '';
        $data['order'] = isset($data['order']) ? strtoupper($data['order']) : 'ASC';

            $sql = "SELECT * FROM coupon c ";
            $sort_data = array(
                'name',
                'date_start',
                'date_end',
                'quantity'
            );

            if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
                $sql .= " ORDER BY " . $data['sort'];
            }else {
                $data['sort'] = '';
                $sql .= " ORDER BY c.coupon_id";
            }

            if (isset($data['order']) && ($data['order'] == 'DESC')) {
                $sql .= " DESC";
            } else {
                $sql .= " ASC";
            }

            if (isset($data['start']) || isset($data['limit'])) {
                if ($data['start'] < 0) {
                    $data['start'] = 0;
                }

                if ($data['limit'] < 1) {
                    $data['limit'] = 20;
                }

                $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
            }
            $this->Database->query($sql);
            $rows = $this->Database->getRows();


        return $rows;
    }

    public function getCoupon($coupon_id) {
        $this->Database->query("SELECT * FROM coupon WHERE coupon_id = :cID", array(
            'cID'   => $coupon_id
        ));
        $row = $this->Database->getRow();
        $this->Database->query("SELECT category_id FROM coupon_category WHERE coupon_id = :cID", array(
            'cID'   => $coupon_id
        ));
        $row['categories_id'] = [];
        foreach ($this->Database->getRows() as $r) {
            $row['categories_id'][] = $r['category_id'];
        }
        $this->Database->query("SELECT product_id FROM coupon_product WHERE coupon_id = :cID", array(
            'cID'   => $coupon_id
        ));
        $row['products_id'] = [];
        foreach ($this->Database->getRows() as $r) {
            $row['products_id'][] = $r['product_id'];
        }
        $this->rows = [];
        $this->rows[0] = $row;
        return $row;
    }

    public function deleteCoupon($coupon_id) {
        $this->Database->query("DELETE FROM coupon WHERE coupon_id = :cID", array(
            'cID'   => $coupon_id
        ));
        return $this->Database->numRows();
    }

    public function editCoupon($coupon_id, $data) {
        if(count($data) > 0) {
            $sql = "UPDATE coupon SET ";
            $params = [];
            $query = [];
            if(isset($data['name'])) {
                $query[] = "name = :cName ";
                $params['cName'] = $data['name'];
            }
            if(isset($data['code'])) {
                $query[] = "code = :cCode ";
                $params['cCode'] = $data['code'];
            }
            if(isset($data['discount'])) {
                $query[] = "discount = :cDiscount ";
                $params['cDiscount'] = $data['discount'];
            }
            if(isset($data['type'])) {
                $query[] = "type = :cType ";
                $params['cType'] = $data['type'];
            }
            if(isset($data['minimum_price'])) {
                $query[] = "minimum_price = :cMinimumPrice ";
                $params['cMinimumPrice'] = $data['minimum_price'];
            }
            if(isset($data['date_start'])) {
                $query[] = "date_start = :cDStart ";
                $params['cDStart'] = $data['date_start'];
            }
            if(isset($data['date_end'])) {
                $query[] = "date_end = :cDEnd ";
                $params['cDEnd'] = $data['date_end'];
            }
            if(isset($data['count'])) {
                $query[] = "count = :cCount ";
                $params['cCount'] = $data['count'];
            }
            if(isset($data['status'])) {
                $query[] = "status = :cStatus ";
                $params['cStatus'] = $data['status'];
            }
            $sql .= implode(" , ", $query);
            $sql .= " WHERE coupon_id = :cID ";
            $params['cID'] = $coupon_id;
            if(count($query) > 0) {
                $this->Database->query($sql, $params);
            }
            if(isset($data['products_id'])) {
                $this->Database->query("DELETE FROM coupon_product WHERE coupon_id = :cID", array(
                    'cID'   => $coupon_id
                ));
                foreach ($data['products_id'] as $product_id) {
                    $this->Database->query("INSERT INTO coupon_product (coupon_id, product_id) VALUES (:cID, :pID)", array(
                        'cID'   => $coupon_id,
                        'pID'   => $product_id
                    ));
                }
            }
            if(isset($data['categories_id'])) {
                $this->Database->query("DELETE FROM coupon_category WHERE coupon_id = :cID", array(
                    'cID'   => $coupon_id
                ));
                foreach ($data['categories_id'] as $category_id) {
                    $this->Database->query("INSERT INTO coupon_category (coupon_id, category_id) VALUES (:cID, :cCID)", array(
                        'cID'   => $coupon_id,
                        'cCID'  => $category_id
                    ));
                }
            }

            return $this->Database->numRows() > 0 ? true : false;
        }
        return false;
    }

    public function getCouponByCode($coupon_key) {
        $this->Database->query("SELECT * FROM coupon WHERE BINARY code = :cCode", array(
            'cCode'   => $coupon_key
        ));
        if($this->Database->hasRows()) {
            $row = $this->Database->getRow();
            $this->Database->query("SELECT category_id FROM coupon_category WHERE coupon_id = :cID", array(
                'cID'   => $row['coupon_id']
            ));
            $row['categories_id'] = [];
            foreach ($this->Database->getRows() as $r) {
                $row['categories_id'][] = $r['category_id'];
            }
            $this->Database->query("SELECT product_id FROM coupon_product WHERE coupon_id = :cID", array(
                'cID'   => $row['coupon_id']
            ));
            $row['products_id'] = [];
            foreach ($this->Database->getRows() as $r) {
                $row['products_id'][] = $r['product_id'];
            }
            $this->rows = [];
            $this->rows[0] = $row;
            return $row;
        }
    }

    public function useCoupon($order_id, $customer_id, $coupon_id, $order_off_amount)
    {
        $this->Database->query("INSERT INTO coupon_history (coupon_id, order_id, customer_id, amount, date_added) VALUES
        (:cID, :oID, :cCID, :cAmount, :cDADDED)", array(
            'cID'   => $coupon_id,
            'oID'   => $order_id,
            'cCID'  => $customer_id,
            'cAmount'   => $order_off_amount,
            'cDADDED'   => time()
        ));
        $this->Database->query("UPDATE coupon SET count = count - 1 WHERE coupon_id = :cID", array(
            'cID'   => $coupon_id
        ));
    }
}