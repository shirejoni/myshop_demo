<?php

namespace App\Web\Controller;

use App\Lib\Action;
use App\Lib\Request;
use App\Lib\Response;
use App\Model\Category;
use App\model\Image;
use App\Model\Manufacturer;
use App\model\Product;
use App\System\Controller;
use function Sodium\compare;

/**
 * @property Response Response
 * @property Request Request
 */
class ControllerCategory extends Controller {

    public function showList() {
        $data = [];
        $messages = [];
        $error = false;
        $category_id = isset($this->data['params'][0]) ? $this->data['params'][0] : 0;
        if($category_id) {
            /** @var Product $Product */
            $Product = $this->load("Product", $this->registry);
            $filter_manufactures = [];
            if(isset($this->Request->get['manufacturer']) && is_array($this->Request->get['manufacturer'])) {
                foreach ($this->Request->get['manufacturer'] as $manufacturer_id) {
                    if((int) $manufacturer_id) {
                        $filter_manufactures[] = (int) $manufacturer_id;
                    }
                }
            }
            $filter_filters = [];
            if(isset($this->Request->get['filter']) && is_array($this->Request->get['filter'])) {
                foreach ($this->Request->get['filter'] as $filter) {
                    list($filter_group_id, $filter_id) = explode('-', $filter);
                    if($filter_group_id && $filter_id) {
                        if((int) $filter_id) {
                            $filter_filters[$filter_group_id][] = (int) $filter_id;
                        }
                    }
                }
            }
            $option = array(
                'category_id'   => $category_id,
                'sort'          => 'p.date_added',
                'order'         => 'DESC',
                'manufacturers_id'   => $filter_manufactures,
                'filters_data'    => $filter_filters,
            );
            if(isset($this->Request->get['max'])) {
                $option['max'] = $this->Request->get['max'];
            }
            if(isset($this->Request->get['min'])) {
                $option['min'] = $this->Request->get['min'];
            }
            $products = $Product->getProductsComplete($option);
            if($products) {
                $data['Breadcrumbs'] = [];
                $data['Breadcrumbs'][] = array(
                    'text'  => $this->Language->get("home_page"),
                    'link'  => URL,
                    'active'=> 0
                );
                /** @var Category $Category */
                $Category = $this->load("Category", $this->registry);
                $categories = $Category->getCategoryInfoInPath($category_id);
                foreach ($categories as $cat) {
                    $data['Breadcrumbs'][] = array(
                        'text'  => $cat['name'],
                        'link'  => URL . 'category/' . $cat['category_id'],
                        'active'=> $category_id == $cat['category_id'] ? 1 : 0,
                    );
                }
                $category = $Category->getCategoryByID($category_id);
                $categoryFilters = $Category->getCategoryFilters();
                if($categoryFilters) {
                    $data['CategoryFilters'] = $categoryFilters;
                }
                /** @var Image $Image */
                $Image = $this->load("Image", $this->registry);
                $minimum_price = 0;
                $maximum_price = 0;
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
                }
                $data['MinPrice'] = $minimum_price;
                $data['MaxPrice'] = $maximum_price;
                $data['Manufacturers'] = [];
                /** @var Manufacturer $Manufacturer */
                $Manufacturer = $this->load("Manufacturer", $this->registry);
                $data['Manufacturers'] = $Manufacturer->getManufacturersProduct(array(
                    'category_id'   => $category_id,
                    'order'         => "DESC",
                ));
                $data['CompareCount'] = isset($_SESSION['compares_id']) ? count($_SESSION['compares_id']) : 0;
                $data['Products'] = $products;
                if(isset($this->Request->get['json-response'])) {
                    $json = [];
                    $json['status'] = 1;
                    $json['data'] = $this->render('category/products', $data);
                    $this->Response->setOutPut(json_encode($json));

                }else {
                    $this->Response->setOutPut($this->render('category/list', $data));
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