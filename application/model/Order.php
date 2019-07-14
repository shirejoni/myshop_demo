<?php

namespace App\Model;

use App\Lib\Database;
use App\System\Model;

/**
 * @property Database Database
 * @property Language Language
 */
class Order extends Model {
    public function insertOrder($data) {
        $this->Database->query("INSERT INTO `order` (customer_id, first_name, last_name, email, mobile, payment_first_name, payment_last_name, payment_city_id, payment_province_id, payment_address, payment_code, payment_gate, total, order_status_id, 
        `count`, weight, weight_id, ip, use_agent, date_added, date_modified) VALUES
        (:cID, :cFName, :cLName, :cEmail, :cMobile, :cPFName , :cPLName, :pCID, :pPID, :pAddress, :pCode, :pGate, :oTotal, :oStatusID,
         :oCount, :oWeight, :oWID, :cIP, :cUserAgent, :oDADDED, :oDModified)", array(
            'cID'  => $data['customer']['customer_id'],
            'cFName'    => $data['customer']['first_name'],
            'cLName'    => $data['customer']['last_name'],
            'cEmail'    => $data['customer']['email'],
            'cMobile'   => $data['customer']['mobile'],
            'cPFName'   => $data['address']['first_name'],
            'cPLName'   => $data['address']['last_name'],
            'pCID'      => $data['address']['city'],
            'pPID'      => $data['address']['province_id'],
            'pAddress'  => $data['address']['address'],
            'pCode'     => $data['payment_code'],
            'pGate'     => $data['payment_gate'],
            'oTotal'    => $data['total'],
            'oStatusID' => $data['order_status_id'],
            'oCount'    => $data['order_count'],
            'oWeight'   => $data['order_weight'],
            'oWID'      => $data['order_weight_id'],
            'cIP'       => $data['ip'],
            'cUserAgent'    => $data['user_agent'],
            'oDADDED'       => time(),
            'oDModified'    => time()
        ));
        $order_id = $this->Database->insertId();
        if(isset($data['products'])) {
            foreach ($data['products'] as $product) {
                $this->Database->query("INSERT INTO order_product (order_id, product_id, quantity, price, total) VALUES 
                (:oID, :pID, :pQuantity, :pPrice, :pTotal)", array(
                    'oID'   => $order_id,
                    'pID'   => $product['product_id'],
                    'pQuantity'  => $product['quantity'],
                    'pPrice'     => $product['total_price_for_unit'],
                    'pTotal'     => $product['total']
                ));
                $order_product_id = $this->Database->insertId();
                foreach ($product['option'] as $option_value) {
                    $this->Database->query("INSERT INTO order_option (order_id, order_product_id, product_option_id, product_option_value_id, name, `value`, type)
                    VALUES (:oID, :oPID, :pOID, :pOVID, :oOName, :oOValue, :oOType)", array(
                        'oID'   => $order_id,
                        'oPID'  => $order_product_id,
                        'pOID'  => $option_value['product_option_id'],
                        'pOVID' => $option_value['product_option_value_id'],
                        'oOName'    => $option_value['option_group_name'],
                        'oOValue'   => $option_value['name'],
                        'oOType'    => $option_value['type']
                    ));
                }
            }
        }
        $this->Database->query("INSERT INTO order_total (order_id, code, name, `value`) VALUES 
        (:oID, :oTCode, :oTName, :oTValue)", array(
            'oID'   => $order_id,
            'oTCode'    => 'total',
            'oTName'    => 'total without off',
            'oTValue'   => $data['total_without_off']
        ));
        $this->Database->query("INSERT INTO order_total (order_id, code, name, `value`, serialized) VALUES 
        (:oID, :oTCode, :oTName, :oTValue , :oSerialized)", array(
            'oID'   => $order_id,
            'oTCode'    => 'off',
            'oTName'    => 'Off Info',
            'oTValue'   => serialize(['off'   => $data['off'], 'code' => $data['code']]),
            'oSerialized'   => 1
        ));
        return $order_id;
    }

    public function editOrder($order_id, $data): bool
    {
        $sql = "UPDATE `order` SET ";
        $params = [];
        $query = [];
        if(isset($data['order_status_id'])) {
            $query[] = "order_status_id = :oStatusID ";
            $params['oStatusID'] = $data['order_status_id'];
        }
        if(isset($data['payment_code'])) {
            $query[] = "payment_code = :pCode ";
            $params['pCode'] = $data['payment_code'];
        }

        if(isset($data['payment_gate'])) {
            $query[] = "payment_gate = :pGate ";
            $params['pGate'] = $data['payment_gate'];
        }
        if(isset($data['transaction_code'])) {
            $query[] = 'transaction_code = :pTransactionCode';
            $params['pTransactionCode'] = $data['transaction_code'];
        }
        $sql .= implode(" , ", $query);
        $sql .= " WHERE order_id = :oID ";
        $params['oID'] = $order_id;
        if(count($query) > 0) {
            $this->Database->query($sql, $params);
        }

        return $this->Database->numRows() > 0;
    }

    public function getOrder($order_id)
    {
        $this->Database->query('SELECT * FROM `order` WHERE order_id = :oID', array(
            'oID'   => $order_id
        ));
        if($this->Database->hasRows()) {
            return $this->Database->getRow();
        }
        return false;
    }

    public function getOrderProducts($order_id): array
    {
        $this->Database->query('SELECT *, op.price as `price`, op.total as `total`, op.quantity as `quantity` FROM order_product op LEFT JOIN product p ON op.product_id = p.product_id LEFT JOIN 
        product_language pl ON p.product_id = pl.product_id WHERE order_id = :oID AND pl.language_id = :lID', array(
            'oID'   => $order_id,
            'lID'   => $this->Language->getLanguageID()
        ));
        if($this->Database->hasRows()) {
            $orderProducts = [];
            foreach ($this->Database->getRows() as $row) {
                $this->Database->query('SELECT * FROM order_option WHERE order_product_id = :oPI', array(
                    'oPI'   => $row['order_product_id']
                ));
                $orderProductOptions = [];
                if($this->Database->hasRows()) {
                    $orderProductOptions = $this->Database->getRows();
                }
                $row['order_product_options'] = $orderProductOptions;
                $orderProducts[] = $row;
            }
            return $orderProducts;
        }
        return [];
    }

    public function getOrderTotal($order_id): array
    {
        $this->Database->query('SELECT * FROM order_total ot WHERE ot.order_id = :oID', array(
            'oID'   => $order_id
        ));
        $order_total = [];
        foreach ($this->Database->getRows() as $row) {
            if($row['serialized']) {
                $row['value'] = unserialize($row['value'], [ 'allowed_classes'  => false]);
            }
            $order_total[$row['code']] = $row;
        }
        return $order_total;
    }

    public function insertOrderHistory(array $array)
    {
        $this->Database->query('REPLACE INTO order_history (order_id, order_status_id, date_added) VALUES 
        (:oID, :oSID, :oDAdded)', array(
            'oID'   => $array['order_id'],
            'oSID'  => $array['order_status_id'],
            'oDAdded'   => $array['date_added']
        ));
        return $this->Database->insertId();
    }

    public function getOrderHistories($order_id) {
        $this->Database->query('SELECT * FROM order_history oh LEFT JOIN order_status os ON oh.order_status_id =
        os.order_status_id WHERE oh.order_id = :oID AND language_id = :lID', array(
            'oID'   => $order_id,
            'lID'   => $this->Language->getLanguageID()
        ));
        return $this->Database->getRows();
    }

    public function getOrderStatuses() {
        $this->Database->query("SELECT * FROM order_status WHERE language_id = :lID", array(
            'lID'   => $this->Language->getLanguageID()
        ));
        return $this->Database->getRows();
    }

    public function getOrders($data = [])
    {
        $data['sort'] = $data['sort'] ?? '';
        $data['order'] = isset($data['order']) ? strtoupper($data['order']) : 'ASC';
        $data['language_id'] = $data['language_id'] ?? $this->Language->getLanguageID();

        $sql = 'SELECT *, ohm.date_added as `date_updated` FROM  `order` o INNER JOIN (SELECT MAX(oh.order_status_id) as `order_status_id`, oh.order_id, oh.date_added FROM order_history oh GROUP BY oh.order_id) ohm ON ohm.order_id = o.order_id  LEFT JOIN order_status os ON ohm.order_status_id = os.order_status_id
        WHERE 1 = 1 ';
        $sort_data = array(
            'name',
            'sort_order'
        );
        $sql .= ' GROUP BY o.order_id';
        if (isset($data['sort']) && in_array($data['sort'], $sort_data, false)) {
            $sql .= ' ORDER BY ' . $data['sort'];
        }else {
            $data['sort'] = '';
            $sql .= ' ORDER BY o.order_id';
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= ' DESC';
        } else {
            $sql .= ' ASC';
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= ' LIMIT ' . (int)$data['start'] . ',' . (int)$data['limit'];
        }
        $this->Database->query($sql);
        $rows = $this->Database->getRows();
        return $rows;
    }
}