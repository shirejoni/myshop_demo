<?php

namespace App\Web\Controller;

use App\lib\Cart;
use App\Lib\Response;
use App\model\Image;
use App\model\Product;
use App\System\Controller;

/**
 * @property Response Response
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
        $data['Products'] = $product_data;
        $data['Total'] = $total;
        $data['TotalFormatted'] = number_format($total);
        $data['Off'] = 0;
        $data['OffFormatted'] = number_format(0);
        $data['PaymentPrice'] = $total - 0;
        $data['PaymentPriceFormatted'] = number_format($data['PaymentPrice']);
//        var_dump($data);
        $this->Response->setOutPut($this->render('checkout/cart', $data));
    }

}