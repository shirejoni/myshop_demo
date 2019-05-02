<?php


namespace App\lib;


use App\model\Customer;
use App\Model\Language;
use App\model\Product;


class Cart
{
    /**
     * @var Database Database
     */
    private $Database;
    /**
     * @var Customer $Customer
     */
    private $Customer;
    /**
     * @var Config $Config
     */
    private $Config;
    /**
     * @var Language $Language
     */
    private $Language;

    public function __construct(Registry $registry)
    {
        $this->Config = $registry->Config;
        $this->Customer = $registry->Customer;
        $this->Database = $registry->Database;
        $this->Language = $registry->Language;
        $this->Database->query("DELETE FROM cart WHERE customer_id = 0 AND date_added < :dAdded", array(
            'dAdded'    => time() - 3600,
        ));
        if($this->Customer && $this->Customer->getCustomerId()) {
            $this->Database->query("UPDATE cart SET session_id = :sID WHERE customer_id = :cID ", array(
                'cID'   => $this->Customer->getCustomerId(),
                'sID'   => session_id(),
            ));
            $this->Database->query("SELECT * FROM cart WHERE session_id = :sID AND customer_id = 0", array(
                'sID'   => session_id(),
            ));
            foreach ($this->Database->getRows() as $row) {
                $this->Database->query("DELETE FROM cart WHERE cart_id = :cID", array(
                    'cID'   => $row['cart_id']
                ));
                $this->add($row['product_id'], $row['quantity'], json_decode($row['product_option']));
            }
        }
    }

    public function getProducts(Product $Product) {
        $customer_id = $this->Customer && $this->Customer->getCustomerId() ? $this->Customer->getCustomerId() : 0;
        $this->Database->query("SELECT * FROM cart WHERE customer_id = :cID AND session_id = :sID", array(
            'cID'   => $customer_id,
            'sID'   => session_id()
        ));
        $product_data = [];
        foreach ($this->Database->getRows() as $cart_row) {
            $product = $Product->getProduct($cart_row['product_id']);
            if($product && $cart_row['quantity'] > 0) {
                $option_price = 0;
                $option_weight = 0;
                $option_data = [];
                $stock = true;
                foreach (json_decode($cart_row['product_option']) as $product_option_id => $product_option_value_id) {
                    $this->Database->query("SELECT * FROM product_option po LEFT JOIN `option` o ON po.option_id = o.option_id LEFT JOIN option_language ol
                    ON ol.option_id = o.option_id WHERE po.product_option_id = :pOID AND po.product_id = :pID  AND ol.language_id = :lID", array(
                        'pOID'  => $product_option_id,
                        'pID'   => $product['product_id'],
                        'lID'   => $product['language_id']
                    ));
                    if($this->Database->hasRows()) {
                        $option = $this->Database->getRow();
                        $this->Database->query("SELECT * FROM product_option_value pov LEFT JOIN option_value ov on pov.option_value_id = ov.option_value_id LEFT JOIN option_value_language ovl ON ovl.option_value_id = ov.option_value_id WHERE
                        pov.product_option_value_id = :pOVID AND pov.product_option_id = :pOID AND ovl.language_id = :lID", array(
                           'pOVID'  => $product_option_value_id,
                           'pOID'   => $product_option_id,
                           'lID'    => $this->Language->getLanguageID()
                        ));
                        if(!$this->Database->hasRows()) {
                            $this->Database->query("SELECT * FROM product_option_value pov LEFT JOIN option_value ov on pov.option_value_id = ov.option_value_id LEFT JOIN option_value_language ovl ON ovl.option_value_id = ov.option_value_id WHERE
                            pov.product_option_value_id = :pOVID AND pov.product_option_id = :pOID AND ovl.language_id = :lID", array(
                                'pOVID'  => $product_option_value_id,
                                'pOID'   => $product_option_id,
                                'lID'    => $this->Language->getDefaultLanguageID()
                            ));
                        }
                        if($this->Database->hasRows()) {
                            $option_value = $this->Database->getRow();
                            if($option_value['price_prefix'] == '+') {
                                $option_price += $option_value['price'];
                            }else if($option_value['price_prefix'] == '-') {
                                $option_price -= $option_value['price'];
                            }
                            if($option_value['weight_prefix'] == '+') {
                                $option_weight += $option_value['weight'];
                            }else if($option_value['weight_prefix'] == '-') {
                                $option_weight -= $option_value['weight'];
                            }
                            if($option_value['subtract'] && (!$option_value['quantity'] || $option_value['quantity'] < $cart_row['quantity'])) {
                                $stock = false;
                            }
                            $option_data[] = array(
                                'product_option_id'     => $product_option_id,
                                'product_option_value_id'   => $product_option_value_id,
                                'option_id'             => $option['option_id'],
                                'option_value_id'       => $option_value['option_value_id'],
                                'type'                  => $option['option_type'],
                                'name'                  => $option_value['name'],
                                'quantity'              => $option_value['quantity'],
                                'subtract'              => $option_value['subtract'],
                                'price'                 => $option_value['price'],
                                'price_prefix'          => $option_value['price_prefix'],
                                'weight'                => $option_value['weight'],
                                'weight_prefix'         => $option_value['weight_prefix'],
                            );

                        }

                    }
                }

                $price = $product['price'];
                $product_specials = $Product->getProductSpecials($product['product_id']);
                $special = '';
                foreach ($product_specials as $product_special) {
                    if ($product_special['date_start'] < time() && $product_special['date_end'] > time()) {
                        $special = $product_special['price'];
                    }
                }
                if(!$product['quantity'] || $product['quantity'] < $cart_row['quantity']) {
                    $stock = false;
                }
                $product_data[] = array(
                    'cart_id'       => $cart_row['cart_id'],
                    'product_id'    => $product['product_id'],
                    'name'          => $product['name'],
                    'image'         => $product['image'],
                    'option'        => $option_data,
                    'quantity'      => $cart_row['quantity'],
                    'minimum'       => $product['minimum'],
                    'stock'         => $stock,
                    'price'         => $price + $option_price,
                    'total'         => ($price + $option_price) * $cart_row['quantity'],
                    'weight'        => ($product['weight'] + $option_weight) * $cart_row['quantity'],
                    'weight_id'     => $product['weight_id'],
                    'length'        => $product['length'],
                    'length_id'     => $product['length_id'],
                    'width'         => $product['width'],
                    'height'        => $product['height'],
                );
            }else {
                $this->remove($cart_row['cart_id']);
            }
        }
        return $product_data;
    }

    public function remove($cart_id) {
        $customer_id = $this->Customer && $this->Customer->getCustomerId() ? $this->Customer->getCustomerId() : 0;
        $this->Database->query("DELETE FROM cart WHERE cart_id = :cID AND customer_id = :cCID AND session_id = :sID", array(
            'cID'   => $cart_id,
            'cCID'  => $customer_id,
            'sID'   => session_id()
        ));
        return $this->Database->numRows();
    }

    public function add($product_id, $quantity = 1, $product_option = array()) {
        $customer_id = $this->Customer && $this->Customer->getCustomerId() ? $this->Customer->getCustomerId() : 0;
        $this->Database->query("SELECT COUNT(*) as `total` FROM cart WHERE customer_id = :cID AND product_id = :pID AND session_id = :sID AND product_option = :pOption", array(
            'cID'   => $customer_id,
            'sID'   => session_id(),
            'pID'   => $product_id,
            'pOption'   => json_encode($product_option)
        ));
        $result = $this->Database->getRow();
        if(!$result['total']) {
            $this->Database->query("INSERT INTO cart (customer_id, product_id, session_id, product_option, quantity, date_added) VALUES 
            (:cID, :pID, :sID, :pOption, :cQuantity, :cDAdded)", array(
                'cID'   => $customer_id,
                'pID'   => $product_id,
                'sID'   => session_id(),
                'pOption'   => json_encode($product_option),
                'cQuantity' => $quantity,
                'cDAdded'   => time()
            ));
        }else {
            $this->Database->query("UPDATE cart SET quantity = quantity + :cQuantity WHERE customer_id = :cID AND product_id = :pID AND 
            product_option = :pOption AND session_id = :sID", array(
                'cID'   => $customer_id,
                'pID'   => $product_id,
                'sID'   => session_id(),
                'pOption'   => json_encode($product_option),
                'cQuantity' => $quantity,
            ));
        }
    }
}