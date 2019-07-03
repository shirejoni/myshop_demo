<?php

namespace App\Web\Controller;

use App\Lib\Action;
use App\lib\Cart;
use App\Lib\Database;
use App\Lib\Request;
use App\Lib\Response;
use App\Model\Coupon;
use App\System\Controller;

/**
 * @property Request Request
 * @property Response Response
 * @property Database Database
 *
 * */
class ControllerCheckoutCoupon extends Controller {

    public function applycoupon($returnInfo = false) {
        if(isset($this->Request->post['coupon-post']) && $this->Customer) {
            $coupon_key = isset($this->Request->post['coupon']) ? $this->Request->post['coupon'] : '';
        }else if(isset($_SESSION['customer']['coupon']) && $this->Customer) {
            $coupon_key = $_SESSION['customer']['coupon']['code'];
            $coupon = $_SESSION['customer']['coupon'];
        }
        if(isset($coupon_key) && $coupon_key) {
                /** @var Coupon $Coupon */
                $Coupon = $this->load("Coupon", $this->registry);
                if(!isset($coupon)) {
                    $coupon = $Coupon->getCouponByCode($coupon_key);
                }
                if($coupon && $coupon['date_end'] > time()) {
                    $old_session_id = isset($_SESSION['old_session_id']) ? $_SESSION['old_session_id'] : false;
                    $Cart = new Cart($this->registry,$old_session_id);
                    $Product = $this->load("Product", $this->registry);
                    $products = $Cart->getProducts($Product);
                    $off_price = 0;
                    $total = 0;
                    $status = false;
                    foreach ($products as $product) {
                        $total += $product['total'];
                        if(in_array($product['product_id'], $coupon['products_id'])) {
                            $status = true;
                            if($coupon['type'] == 'percentage' && $coupon['discount']) {
                                $off_price += ceil(($coupon['discount'] * $product['total']) / 100);
                            }
                            continue;
                        }
                        foreach ($coupon['categories_id'] as $category_id) {
                            $this->Database->query("SELECT * FROM product_category WHERE product_id = :pID AND category_id = :cID", array(
                                'pID'   => $product['product_id'],
                                'cID'   => $category_id
                            ));
                            if($this->Database->hasRows()) {
                                $status = true;
                                $off_price += ceil(($coupon['discount'] * $product['total']) / 100);
                                break;
                            }
                        }
                    }

                    $json = [];
                    if($status == 1) {
                        if($coupon['minimum_price'] > $total) {
                            $status = 0;
                            $json['status'] = 0;
                            $json['messages'] = [str_replace('{{MINIMUM_PRICE}}', number_format($coupon['minimum_price']), $this->Language->get('error_off_code_minimum_price'))];

                        }
                        if($status) {
                            $_SESSION['customer']['coupon'] = $coupon;
                            $_SESSION['customer']['coupon']['code'] = $coupon_key;
                            $json['status'] = 1;
                            $json['off_price'] = $off_price;
                            $json['total'] = $total;
                            $json['payment_price'] = $total - $off_price;
                            $json['off_price_formatted'] = number_format($json['off_price']);
                            $json['total_formatted'] = number_format($json['total']);
                            $json['payment_price_formatted'] = number_format($json['payment_price']);
                            $json['messages'] = [$this->Language->get('success_message_off_price')];
                            $json['code'] = $coupon_key;
                        }
                    }else {
                        $json['status'] = 0;
                        $json['off_price'] = 0;
                        $json['messages'] = [$this->Language->get('error_messages_off_price')];
                    }

                }else {
                    $json['status'] = 0;
                    $json['off_price'] = 0;
                    $json['messages'] = [$this->Language->get('error_messages_invalid')];
                }
                if($returnInfo) {

                    return $json;
                }else {
                    $this->Response->setOutPut(json_encode($json));
                }
                return;
            }
        return new Action('error/notFound');
    }



}