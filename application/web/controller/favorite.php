<?php

namespace App\Web\Controller;

use App\Lib\Action;
use App\Lib\Database;
use App\Lib\Request;
use App\Lib\Response;
use App\model\Customer;
use App\model\Product;
use App\System\Controller;

/**
 * @property Customer Customer
 * @property Response Response
 * @property Request Request
 * @property Database Database
 */
class ControllerFavorite extends Controller {

    public function toggle() {
        if( $this->Customer && $this->Customer->getCustomerId()) {
            if(isset($this->Request->post['product_id'])) {

                /** @var Product $Product */
                $Product = $this->load("Product", $this->registry);
                $product = $Product->getProduct((int)$this->Request->post['product_id']);
                $json = [];
                if($product) {
                    $customer_id = $this->Customer->getCustomerId();
                    $customerFavoriteProducts = $this->Customer->getCustomerFavorite($customer_id);
                    if(in_array($product['product_id'], $customerFavoriteProducts)) {
                        $this->Customer->deleteCustomerFavorite($customer_id, $product['product_id']);
                    }else {
                        $this->Customer->insertCustomerFavorite($customer_id, $product['product_id']);
                    }
                    $json['status'] = 1;
                    $json['messages'] = ['با موفقیت انجام شد'];
                    $this->Response->setOutPut(json_encode($json));
                    return;
                }
            }
            return new Action('error/notFound', 'web');
        }else {
            $json = array(
                'status'    => 0,
                'messages'  => ['شما باید اول وارد شوید']
            );
            $this->Response->setOutPut(json_encode($json));
            return;
        }
    }


}