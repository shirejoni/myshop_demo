<?php

namespace App\Web\Controller;

use App\Lib\Action;
use App\lib\Cart;
use App\Lib\Request;
use App\Lib\Response;
use App\model\Customer;
use App\model\Image;
use App\Model\Language;
use App\model\Product;
use App\System\Controller;

/**
 * @property Request Request
 * @property Language Language
 * @property Response Response
 * @property Customer Customer
 * */
class ControllerCheckoutCart extends Controller {

    public function add() {
        $data = [];
        $messages = [];
        $error = false;
        if(isset($this->Request->post['cart-post'])) {
            $product_id = isset($this->Request->post['product_id']) ? $this->Request->post['product_id'] : 0;
            /** @var Product $Product */
            $Product = $this->load("Product", $this->registry);
            if($product_id && $product = $Product->getProduct($product_id, $this->Language->getDefaultLanguageID())) {
                $data['product_id'] = $product_id;
                if(isset($this->Request->post['quantity']) && $this->Request->post['quantity'] > $product['minimum'])  {
                    $data['quantity'] = (int) $this->Request->post['quantity'];
                }else {
                    $data['quantity'] = (int) $this->Request->post['quantity'];
                }
                if(isset($this->Request->post['options'])) {
                    $product_cart_option = array_filter($this->Request->post['options']);
                }else {
                    $product_cart_option = [];
                }

                $options = $Product->getOptions($product_id);
                $data['options'] = [];
                foreach ($options as $option) {
                    if($option['required'] == 1 && empty($product_cart_option[$option['product_option_id']])) {
                            $error = true;
                            $messages[] = $this->Language->get('error_cart_option_item_required');
                    }
                    if(isset($product_cart_option[$option['product_option_id']]) && isset($option['product_option_values'][$product_cart_option[$option['product_option_id']]])) {
                        $data['options'][$option['product_option_id']] = $product_cart_option[$option['product_option_id']];
                    }
                }
                $json = [];
                if(!$error) {
                    $Cart = new Cart($this->registry);
                    $this->registry->Cart = $Cart;
                    $Cart->add($data['product_id'], $data['quantity'], $data['options']);

                    $json['status'] = 1;
                    $json['messages'] = [$this->Language->get('message_success_done')];
                }
                if($error) {
                    $json['status'] = 0;
                    $json['messages'] = $messages;
                }
                $this->Response->setOutPut(json_encode($json));
                return;
            }
        }
        return new Action('error/notFound', 'web');
    }

    public function info() {
        if($this->Customer && $this->Customer->getCustomerId()) {
            $old_session = isset($_SESSION['old_session_id']) ? $_SESSION['old_session_id'] : false;
            $Cart = new Cart($this->registry, $old_session);
        }else {
            $Cart = new Cart($this->registry);
        }
        /** @var Product $Product */
        $Product = $this->load("Product", $this->registry);
        $product_data = $Cart->getProducts($Product);
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
        $data['total'] = $total;
        $data['total_formatted'] = number_format($total);
        $json = [];
        $json['status'] = 1;
        $json['data'] = $data;
        $this->Response->setOutPut(json_encode($json));
    }

    public function remove() {
        if(isset($this->Request->post['cart-post']) && isset($this->Request->post['cart-id'])) {
            $Cart = new Cart($this->registry);
            $json = [];
            if($Cart->remove((int) $this->Request->post['cart-id'])) {
                $json['status'] = 1;
            }else {
                $json['status'] = 0;
            }
            $this->Response->setOutPut(json_encode($json));
        }
    }

}