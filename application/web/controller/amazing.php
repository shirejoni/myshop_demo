<?php

namespace App\Web\Controller;

use App\Lib\Action;
use App\Model\Category;
use App\model\Image;
use App\Model\Manufacturer;
use App\model\Product;
use App\System\Controller;

class ControllerAmazing extends Controller {

    public function index()
    {
        $data = [];
        $messages = [];
        $error = false;
        $amazing_id = isset($this->data['params'][0]) ? $this->data['params'][0] : 0;
        if($amazing_id) {
            /** @var Product $Product */
            $Product = $this->load('Product', $this->registry);
            $Amazing = $this->load('Amazing', $this->registry);
            $amazing = $Amazing->getAmazing($amazing_id);
            $products = $Product->getProductsComplete(['products_id' => $amazing['products_id']]);

            if($products) {
                /** @var Image $Image */
                $Image = $this->load("Image", $this->registry);
                $minimum_price = 0;
                $maximum_price = 0;
                $favoriteProducts = [];
                if($this->Customer && $this->Customer->getCustomerId()) {
                    $favoriteProducts = $this->Customer->getCustomerFavorite($this->Customer->getCustomerId());
                }
                foreach ($products as $index => $product) {
                    if (is_file(ASSETS_PATH . DS . substr($product['image'], strlen(ASSETS_URL)))) {
                        $image = ASSETS_URL . $Image->resize(substr($product['image'], strlen(ASSETS_URL)), 400, 400);
                    } else {
                        $image = ASSETS_URL . $Image->resize('img/no-image.jpeg', 300, 200);
                    }
                    $products[$index]['image'] = $image;
                    if($minimum_price == 0 || (!empty($product['special']) && $minimum_price > $product['special']) || $minimum_price > $product['price'] ) {
                        $minimum_price = !empty($product['special']) && $minimum_price > $product['special'] && $product['special'] < $product['price'] ? $product['special'] : $product['price'];
                    }
                    if($maximum_price == 0 || (!empty($product['special']) && $maximum_price < $product['special']) || $maximum_price < $product['price'] ) {
                        $maximum_price = !empty($product['special']) && $maximum_price < $product['special'] && $product['special'] > $product['price'] ?  $product['special'] : $product['price'];
                    }
                    if(isset($_SESSION['compares_id']) && in_array($product['product_id'], $_SESSION['compares_id'])) {
                        $products[$index]['is_compare'] = 1;
                    }else {
                        $products[$index]['is_compare'] = 0;
                    }
                    if(in_array($product['product_id'], $favoriteProducts)) {
                        $products[$index]['is_favorite'] = 1;
                    }else {
                        $products[$index]['is_favorite'] = 0;
                    }
                }
                $data['MinPrice'] = $minimum_price;
                $data['MaxPrice'] = $maximum_price;
                $data['CompareCount'] = isset($_SESSION['compares_id']) ? count($_SESSION['compares_id']) : 0;
                $data['Products'] = $products;
                if(isset($this->Request->get['json-response'])) {
                    $json = [];
                    $json['status'] = 1;
                    $json['data'] = $this->render('category/products', $data);
                    $this->Response->setOutPut(json_encode($json));

                }else {
                    $this->Response->setOutPut($this->render('amazing/list', $data));
                }
                return;
            }
            if(isset($this->Request->get['json-response'])) {
                $json = [];
                $json['status'] = 1;
                $json['data'] = $this->render('category/products', $data);
                $this->Response->setOutPut(json_encode($json));
                return;
            }
        }
        return new Action('error/notFound', 'web');
    }
}