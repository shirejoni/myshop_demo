<?php

namespace App\Web\Controller;

use App\Lib\Action;
use App\Lib\Response;
use App\Model\Category;
use App\model\Image;
use App\Model\Manufacturer;
use App\model\Product;
use App\System\Controller;

/**
 * @property Response Response
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
            $products = $Product->getProductsComplete(array(
                'category_id'   => $category_id,
                'sort'          => 'p.date_added',
                'order'         => 'DESC'
            ));
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
                $manufacturers_id = [];
                foreach ($products as $index => $product) {
                    if (is_file(ASSETS_PATH . DS . substr($product['image'], strlen(ASSETS_URL)))) {
                        $image = ASSETS_URL . $Image->resize(substr($product['image'], strlen(ASSETS_URL)), 400, 400);
                    } else {
                        $image = ASSETS_URL . $Image->resize('img/no-image.jpeg', 300, 200);
                    }
                    $products[$index]['image'] = $image;
                    if(!array_key_exists($product['manufacturer_id'],$manufacturers_id)) {
                        $manufacturers_id[$product['manufacturer_id']] = 1;
                    }else {
                        $manufacturers_id[$product['manufacturer_id']]++;
                    }

                }
                arsort($manufacturers_id);
                $data['Manufacturers'] = [];
                /** @var Manufacturer $Manufacturer */
                $Manufacturer = $this->load("Manufacturer", $this->registry);
                foreach ($manufacturers_id as $manufacture_id => $count) {
                    $data['Manufacturers'][] = $Manufacturer->getManufacturerByID($manufacture_id);
                }
                $data['Products'] = $products;
                $this->Response->setOutPut($this->render('category/list', $data));
                return;
            }
        }
        return new Action('error/notFound', 'web');
    }

}