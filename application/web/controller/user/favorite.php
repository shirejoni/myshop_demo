<?php

namespace App\Web\Controller;

use App\Lib\Request;
use App\Lib\Response;
use App\model\Customer;
use App\model\Product;
use App\System\Controller;
/**
 * @property Customer Customer
 * @property Response Response
 * @property Request Request
 * */
class ControllerUserFavorite extends Controller {

    public function index() {
        $data = [];
        $customerFavoriteProducts = $this->Customer->getCustomerFavorite($this->Customer->getCustomerId());
        $data['Products'] = [];
        if($customerFavoriteProducts) {
            /** @var Product $Product */
            $Product = $this->load("Product", $this->registry);
            foreach ($customerFavoriteProducts as $product_id) {
                $product = $Product->getProduct($product_id);
                /** @var Image $Image */
                $Image = $this->load("Image", $this->registry);
                if (is_file(ASSETS_PATH . DS . substr($product['image'], strlen(ASSETS_URL)))) {
                    $image = ASSETS_URL . $Image->resize(substr($product['image'], strlen(ASSETS_URL)), 200, 200);
                } else {
                    $image = ASSETS_URL . $Image->resize('img/no-image.jpeg', 200, 200);
                }
                $product_specials = $Product->getProductSpecials($product['product_id']);
                $special = '';
                foreach ($product_specials as $product_special) {
                    if ($product_special['date_start'] < time() && $product_special['date_end'] > time()) {
                        $special = $product_special['price'];
                    }
                }
                $data['Products'][] = array(
                    'product_id' => $product['product_id'],
                    'image' => $image,
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'special' => $special,
                    'status' => $product['status'],
                    'quantity' => $product['quantity'],
                    'sort_order' => $product['sort_order'],
                );
            }
        }
            $Language = $this->Language;
            $data['Languages'] = $Language->getLanguages();
            $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
            $this->Response->setOutPut($this->render('user/favorites', $data));
    }

    public function delete() {

        if (!empty($this->Request->post['products_id'])) {
            $json = [];
            /** @var Product $Product */
            $Product = $this->load("Product", $this->registry);
            $error = false;
            $this->Database->db->beginTransaction();
            foreach ($this->Request->post['products_id'] as $product_id) {
                $product = $Product->getProduct((int)$product_id);
                if ((int)$product_id && $product) {
                    $this->Customer->deleteCustomerFavorite($this->Customer->getCustomerId(), $product_id);
                } else {
                    $error = true;
                }
            }
            if ($error) {
                $this->Database->db->rollBack();
                $json['status'] = 0;
                $json['messages'] = [$this->Language->get('error_done')];
            } else {
                $this->Database->db->commit();
                $json['status'] = 1;
                $json['messages'] = [$this->Language->get('message_success_done')];
                $data = [];
                $customerFavoriteProducts = $this->Customer->getCustomerFavorite($this->Customer->getCustomerId());
                $data['Products'] = [];
                if($customerFavoriteProducts) {
                    foreach ($customerFavoriteProducts as $product_id) {
                        $product = $Product->getProduct($product_id);
                        /** @var Image $Image */
                        $Image = $this->load("Image", $this->registry);
                        if (is_file(ASSETS_PATH . DS . substr($product['image'], strlen(ASSETS_URL)))) {
                            $image = ASSETS_URL . $Image->resize(substr($product['image'], strlen(ASSETS_URL)), 200, 200);
                        } else {
                            $image = ASSETS_URL . $Image->resize('img/no-image.jpeg', 200, 200);
                        }
                        $product_specials = $Product->getProductSpecials($product['product_id']);
                        $special = '';
                        foreach ($product_specials as $product_special) {
                            if ($product_special['date_start'] < time() && $product_special['date_end'] > time()) {
                                $special = $product_special['price'];
                            }
                        }
                        $data['Products'][] = array(
                            'product_id' => $product['product_id'],
                            'image' => $image,
                            'name' => $product['name'],
                            'price' => $product['price'],
                            'special' => $special,
                            'status' => $product['status'],
                            'quantity' => $product['quantity'],
                            'sort_order' => $product['sort_order'],
                        );
                    }
                }
                $json['data'] = $this->render('user/favorites_table', $data);
            }

            $this->Response->setOutPut(json_encode($json));
            return;
        }
        return new Action("error/notFound", 'web');
    }

}