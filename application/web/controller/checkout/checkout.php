<?php

namespace App\Web\Controller;

use App\Lib\Action;
use App\lib\Cart;
use App\Lib\Config;
use App\Lib\Request;
use App\Lib\Response;
use App\model\Address;
use App\Model\Coupon;
use App\model\Customer;
use App\model\Image;
use App\Model\Language;
use App\Model\Order;
use App\model\Product;
use App\Model\Weight;
use App\System\Controller;

/**
 * @property Response Response
 * @property Customer Customer
 * @property Language Language
 * @property Config Config
 * @property Request Request
 */
class ControllerCheckoutCheckout extends Controller {

    public function index() {
        if(isset($_SESSION['customer'])) {
            header('location:' . URL . 'checkout/cart?token=' . $_SESSION['token']);
            exit();
        }
        $data = [];
        $data['checkoutProcess'] = array(
            ['ورود', true],
            ['مرسوله', false],
            ['آدرس', false],
            ['پرداخت', false],
            ['پایان', false],
        );
        $this->Response->setOutPut($this->render('checkout/register-login', $data));
    }

    public function cart() {
        $data = [];
        $data['checkoutProcess'] = array(
            ['ورود', false],
            ['مرسوله', true],
            ['آدرس', false],
            ['پرداخت', false],
            ['پایان', false],
        );
        if($this->Customer && $this->Customer->getCustomerId()) {
            $old_session = isset($_SESSION['old_session_id']) ? $_SESSION['old_session_id'] : false;
            $Cart = new Cart($this->registry, $old_session);
        }else {
            $Cart = new Cart($this->registry);
        }
        /** @var Product $Product */
        $Product = $this->load("Product", $this->registry);
        $product_data = $Cart->getProducts($Product);
        if(empty($product_data)) {
            header("location:" . URL);
            exit();
        }
        /** @var Image $Image */
        $Image = $this->load("Image", $this->registry);
        $total = 0;
        foreach ($product_data as $index => $product) {
            $image = $product['image'];
            if (is_file(ASSETS_PATH . DS . substr($product['image'], strlen(ASSETS_URL)))) {
                $image = ASSETS_URL . $Image->resize(substr($product['image'], strlen(ASSETS_URL)), 200, 200);
            }
            $total += $product['total'];
            $product_data[$index]['total_formatted'] = number_format($product['total']);
            $product_data[$index]['total_price_for_unit_formatted'] = number_format($product['total_price_for_unit']);
            $product_data[$index]['image'] = $image;
        }
        require_once WEB_PATH . DS . 'controller/checkout/coupon.php';
        $CouponController = new ControllerCheckoutCoupon($this->registry);
        $couponInfo = $CouponController->applycoupon(true);
        $off = 0;
        if(is_array($couponInfo)) {
            if($couponInfo['status'] == 1) {
                $off = isset($couponInfo['off_price']) ? $couponInfo['off_price'] : 0;
            }
        }
        $data['Products'] = $product_data;
        $data['Total'] = $total;
        $data['TotalFormatted'] = number_format($total);
        $data['Off'] = $off;
        $data['OffFormatted'] = number_format($off);
        $data['PaymentPrice'] = $total - $off;
        $data['PaymentPriceFormatted'] = number_format($data['PaymentPrice']);
        $this->Response->setOutPut($this->render('checkout/cart', $data));
    }

    public function address() {
        $data = [];
        $data['checkoutProcess'] = array(
            ['ورود', false],
            ['مرسوله', false],
            ['آدرس', true],
            ['پرداخت', false],
            ['پایان', false],
        );
        $Address = $this->load("Address", $this->registry);
        $data['Addresses'] = $Address->getAddressesByCustomerID($_SESSION['customer']['customer_id']);
        $this->Response->setOutPut($this->render('checkout/address', $data));

    }

    public function applyAddress() {
        if(isset($this->Request->post['address_id'])) {
            /** @var Address $Address */
            $Address = $this->load("Address", $this->registry);
            $address = $Address->getAddress($this->Request->post['address_id'], $this->Customer->getCustomerId());
            $json = [];
            if($address) {
                $_SESSION['customer']['checkout_address_id'] = $address['address_id'];
                $json['status'] = 1;
                $json['redirect'] = URL . "checkout/checkout/payment";
                $json['messages'] = [$this->Language->get('error_address_is_register_for_checkout')];
            }else {
                $json['status'] = 0;
                $json['messages'] = [$this->Language->get('error_done')];
            }
            $this->Response->setOutPut(json_encode($json));
        }
    }

    public function payment() {
        $data = [];
        $data['checkoutProcess'] = array(
            ['ورود', false],
            ['مرسوله', false],
            ['آدرس', false],
            ['پرداخت', true],
            ['پایان', false],
        );
        if(!isset($_SESSION['customer']['checkout_address_id'])) {
            header('location:' . URL . "?hello");
            exit();
        }
        $data['PhoneNumber'] = $this->Customer->getMobile();
        /** @var Address $Address */
        $Address = $this->load("Address", $this->registry);
        $address = $Address->getAddress($_SESSION['customer']['checkout_address_id'], $this->Customer->getCustomerId());
        if(!$address) {
            header('location:' . URL . "?hi");
            exit();
        }
        $data['FullName'] = $address['first_name'] . " " . $address['last_name'];
        $data['CustomerAddress'] = $address['province_name'] . "," . $address['city_name'] . "," . $address['address'];
        if($this->Customer && $this->Customer->getCustomerId()) {
            $old_session = isset($_SESSION['old_session_id']) ? $_SESSION['old_session_id'] : false;
            $Cart = new Cart($this->registry, $old_session);
        }else {
            $Cart = new Cart($this->registry);
        }
        /** @var Product $Product */
        $Product = $this->load("Product", $this->registry);
        $product_data = $Cart->getProducts($Product);
        if(empty($product_data)) {
            header("location:" . URL);
            exit();
        }
        /** @var Image $Image */
        $Image = $this->load("Image", $this->registry);
        $total = 0;
        $quantity = 0;
        $weight = 0;
        $default_weight_id = $this->Config->get('info_config_default_weight_unit');
        /** @var Weight $Weight */
        $Weight = $this->load("Weight", $this->registry);
        $defaultWeight = $Weight->getWeight($default_weight_id);

        foreach ($product_data as $index => $product) {
            $image = $product['image'];
            if (is_file(ASSETS_PATH . DS . substr($product['image'], strlen(ASSETS_URL)))) {
                $image = ASSETS_URL . $Image->resize(substr($product['image'], strlen(ASSETS_URL)), 200, 200);
            }
            $productWeight = $Weight->getWeight($product['weight_id']);
            $weight += ($product['weight'] / $productWeight['value']) * $defaultWeight['value'];
            $quantity += $product['quantity'];
            $total += $product['total'];
            $product_data[$index]['total_formatted'] = number_format($product['total']);
            $product_data[$index]['total_price_for_unit_formatted'] = number_format($product['total_price_for_unit']);
            $product_data[$index]['image'] = $image;
        }
        require_once WEB_PATH . DS . 'controller/checkout/coupon.php';
        $CouponController = new ControllerCheckoutCoupon($this->registry);
        $couponInfo = $CouponController->applycoupon(true);
        $off = 0;
        if(is_array($couponInfo)) {
            if($couponInfo['status'] == 1) {
                $off = isset($couponInfo['off_price']) ? $couponInfo['off_price'] : 0;
            }
        }
        $order = [];
        $order['customer'] = $this->Customer->getCustomerByID($this->Customer->getCustomerId());
        $order['address'] = $address;
        $order['payment_code'] = '';
        $order['payment_gate'] = '';
        $order['total_without_off'] = $total;
        $order['off']  = $off;
        if(is_array($couponInfo)) {
            $order['code'] = $couponInfo['code'];
        }else {
            $order['code'] = "";
        }
        $order['total'] = $total - $off;
        $order['order_status_id'] = 0;
        $order['order_count'] = $quantity;
        $order['order_weight'] = $weight;
        $order['order_weight_id'] = $default_weight_id;
        $order['ip'] = get_ip_address();
        $order['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        $order['products'] = $product_data;
        /** @var Order $Order */
        $Order = $this->load("Order", $this->registry);
        $order_id = $Order->insertOrder($order);
        $data['Quantity'] = $quantity;
        $data['Weight'] = $weight;
        $data['WeightUnit'] = $defaultWeight['name'];
        $data['Products'] = $product_data;
        $data['Total'] = $total;
        $data['TotalFormatted'] = number_format($total);
        $data['Off'] = $off;
        $data['OffFormatted'] = number_format($off);
        $data['PaymentPrice'] = $total - $off;
        $data['PaymentPriceFormatted'] = number_format($data['PaymentPrice']);
        $_SESSION['customer']['order_id'] = $order_id;
        $_SESSION['customer']['order_off_amount'] = $off;
        $data['OrderID'] = $order_id;
        $data['token_payment'] = generateToken();
        $_SESSION['token_payment'] = $data['token_payment'];
        $_SESSION['token_payment_expired'] = time() + (20 * 60);
        $this->Response->setOutPut($this->render('checkout/confirm', $data));
    }

    public function pay() {
        if(isset($this->data['params'][0], $this->Request->get['token_payment'])) {
            $order_id = $this->data['params'][0];
            $token_payment = $this->Request->get['token_payment'];
            if(!isset($_SESSION['token_payment'])) {
                header('location:' . URL);
                exit();
            }
            if($_SESSION['token_payment'] != $token_payment || $_SESSION['token_payment_expired'] < time()) {
               unset($_SESSION['token_payment'], $_SESSION['token_payment_expired']);
               header('location:' . URL);
               exit();
            }
            /** @var Order $Order */
            $Order = $this->load("Order", $this->registry);
            /** @var Coupon $Coupon */
            $Coupon = $this->load("Coupon", $this->registry);
            $old_session = isset($_SESSION['old_session_id']) ? $_SESSION['old_session_id'] : false;
            $Cart = new Cart($this->registry, $old_session);
            $order = $Order->getOrder($order_id);
            if($order && $order_id == $_SESSION['customer']['order_id']) {
                require_once LIB_PATH . DS . 'pay_ir' . DS . 'functions.php';
                $response = send('test', $order['total'], URL . 'checkout/checkout/verify', null, $order['order_id']);
                $response = json_decode($response, true);
                if($response['status'] == 1) {
                    $Order->editOrder($order['order_id'], array(
                        'payment_code'  => $response['token'],
                        'payment_gate'  => 'pay.ir',
                        'order_status_id'   => 1
                    ));
                    if(isset($_SESSION['customer']['coupon']['coupon_id'])) {
                        $Coupon->useCoupon($order_id, $_SESSION['customer']['customer_id'],$_SESSION['customer']['coupon']['coupon_id'], $_SESSION['customer']['order_off_amount']);

                    }
                    $Cart->emptyCustomerCart($this->Customer->getCustomerId());
                    header('location:https://pay.ir/pg/' .$response['token']);
                    exit();
                }

            }
        }
        return new Action('error/notFound', 'web');
    }

    public function verify() {
        if(isset($this->Request->get['status']) && isset($_SESSION['customer']['order_id']) && isset($this->Request->get['token'])) {
            if($this->Request->get['status'] == 1 && !empty($this->Request->get['token'])) {
                require_once LIB_PATH . DS . 'pay_ir' . DS . 'functions.php';
                /** @var Order $Order */
                $Order = $this->load("Order", $this->registry);
                $order = $Order->getOrder($_SESSION['customer']['order_id']);
                $response = verify('test', $this->Request->get['token']);
                $response = json_decode($response, true);
                if($response['status'] == 1 && $response['factorNumber'] == $order['order_id'] && $this->Request->get['token'] == $order['payment_code'] && $response['amount'] == $order['total']) {
                    $Order->editOrder($order['order_id'], array(
                        'order_status_id'   => 2,
                        'transaction_code'           => $response['transId'],
                    ));
                    $data = [];
                    $data['Status'] = 1;
                    $data['TransactionCode'] = $response['transId'];
                    $data['OrderID']    = $response['factorNumber'];

                    $this->Response->setOutPut($this->render('checkout/verify', $data));
                    return;
                }
                if(isset($order['order_id'], $response['factorNumber']) && $order['order_id'] == $response['factorNumber']) {
                    $Order->editOrder($order['order_id'], array(
                        'order_status_id'   => 0
                    ));
                }
            }
            $data = [];
            $data['Status'] = 0;
            $this->Response->setOutPut($this->render('checkout/verify', $data));
            return;
        }
        return new Action('error/notFound', 'web');
    }

}