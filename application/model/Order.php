<?php

namespace App\Model;

use App\Lib\Database;
use App\System\Model;

/**
 * @property Database Database
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

    public function editOrder($order_id, $data) {
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
        $sql .= implode(" , ", $query);
        $sql .= " WHERE order_id = :oID ";
        $params['oID'] = $order_id;
        if(count($query) > 0) {
            $this->Database->query($sql, $params);
        }

        return $this->Database->numRows() > 0 ? true : false;
    }

    public function getOrder($order_id) {
        $this->Database->query("SELECT * FROM `order` WHERE order_id = :oID", array(
            'oID'   => $order_id
        ));
        if($this->Database->hasRows()) {
            return $this->Database->getRow();
        }
        return false;
    }


}