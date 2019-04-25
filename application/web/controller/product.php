<?php

namespace App\Web\Controller;

use App\Lib\Action;
use App\Lib\Request;
use App\Lib\Response;
use App\Model\Attribute;
use App\Model\AttributeGroup;
use App\Model\Category;
use App\model\Image;
use App\Model\Language;
use App\Model\Option;
use App\model\Product;
use App\System\Controller;

/**
 * @property Product Product
 * @property Request Request
 * @property Response Response
 * @property Language Language
 * @property Category Category
 * */
class ControllerProduct extends Controller {

    public function index() {
        if(isset($this->data['params'][0])) {

            $product_id = (int) $this->data['params'][0];
            /** @var Product $Product */
            $Product = $this->load("Product", $this->registry);
            /** @var Category $Category */
            $Category = $this->load("Category", $this->registry);
            /** @var Option $Option */
            $Option = $this->load("Option", $this->registry);
            /** @var AttributeGroup $AttributeGroup */
            $AttributeGroup = $this->load("AttributeGroup", $this->registry);
            if($product_id && $productInfo = $Product->getProductComplete($product_id)) {
                $data['Breadcrumbs'] = [];
                $data['Breadcrumbs'][] = array(
                    'text'  => $this->Language->get("home_page"),
                    'link'  => URL,
                    'active'=> 0
                );
                $category = $Product->getCategory($product_id);
                $categories = $Category->getCategoryInfoInPath($category['category_id']);
                foreach ($categories as $cat) {
                    $data['Breadcrumbs'][] = array(
                        'text'  => $cat['name'],
                        'link'  => URL . 'category/' . $cat['category_id'],
                        'active'=> 0
                    );
                }
                $data['Breadcrumbs'][] = array(
                    'text'  => $productInfo['name'],
                    'link'  => '#',
                    'active'=> 1,
                );
                $productInfo['category_name'] = $categories[$category['category_id']]['name'];
                $productInfo['category_id'] = $category['category_id'];
                switch ($productInfo['stock_status_id']) {
                    case '1' :
                        $productInfo['stock_status_class'] = 'green';
                        break;
                    case '2' :
                        $productInfo['stock_status_class'] = 'red';
                        break;
                    default :
                        $productInfo['stock_status_class'] = 'yellow';
                        break;
                }
                foreach ($Product->getOptions($product_id) as $option_group) {
                    $productOption = [];
                    $productOption['option_id'] = $option_group['option_id'];
                    $productOption['name'] = $option_group['name'];
                    $productOption['product_option_id'] = $option_group['product_option_id'];
                    $productOption['required'] = $option_group['required'];
                    $productOption['option_type'] = $option_group['option_type'];
                    $option = $Option->getOptionByID($option_group['option_id']);
                    $productOptionValues = [];
                    $option_items = $Option->getOptionValues();
                    foreach ($option_group['product_option_values'] as $product_option_value) {
                        if($product_option_value['quantity'] > 0 || $product_option_value['subtract'] != 1) {
                            $productOptionValues[] = array(
                                'product_option_value_id'   => $product_option_value['product_option_value_id'],
                                'option_value_id'           => $product_option_value['option_value_id'],
                                'image'                     => $option_items[$product_option_value['option_value_id']]['image'],
                                'name'                      => $option_items[$product_option_value['option_value_id']]['name'],
                                'price'                     => $product_option_value['price'],
                                'price_prefix'              => $product_option_value['price_prefix'],
                            );
                        }
                    }
                    $productOption['option_items'] = $productOptionValues;
                    $productInfo['options'][$option_group['sort_order']] = $productOption;
                }
                $attributes = [];
                foreach ($Product->getAttributes($product_id) as $attribute) {
                    if(!isset($attributes[$attribute['attribute_group_id']])) {
                        $attributeGroup = $AttributeGroup->getAttributeGroupByID($attribute['attribute_group_id']);
                        $attributes[$attribute['attribute_group_id']] = array(
                            'attribute_group_id'    => $attribute['attribute_group_id'],
                            'name'                  => $attributeGroup['name'],
                        );
                    }
                    $attributes[$attribute['attribute_group_id']]['attributes'][] = array(
                        'name'    => $attribute['name'],
                        'attribute_id'  => $attribute['attribute_id'],
                        'value'         => $attribute['value'],
                    );

                }
                $images = $Product->getImages($product_id);
                /** @var Image $Image */
                $Image = $this->load("Image", $this->registry);
                if(isset($productInfo['image'])) {
                    if (is_file(ASSETS_PATH . DS . substr($productInfo['image'], strlen(ASSETS_URL)))) {
                        $productInfo['image'] = ASSETS_URL . $Image->resize(substr($productInfo['image'], strlen(ASSETS_URL)), 700, 490);
                    }
                }
                foreach ($images as $image) {
                    $thumbnailImage = $image['image'];
                    if (is_file(ASSETS_PATH . DS . substr($image['image'], strlen(ASSETS_URL)))) {
                        $image['image'] = ASSETS_URL . $Image->resize(substr($image['image'], strlen(ASSETS_URL)), 700, 490);
                        $thumbnailImage = ASSETS_URL . $Image->resize(substr($image['image'], strlen(ASSETS_URL)), 100, 100);
                    }
                    $productInfo['images'][] = array(
                        'image'     => $image['image'],
                        'thumbnail' => $thumbnailImage,
                    );
                }

                $productInfo['reviews'] = $Product->getReviews($product_id);

                $productInfo['attributes'] = $attributes;
                $data['Product'] = $productInfo;

                $this->Response->setOutPut($this->render("product/index", $data));
                return;
            }
        }
        return new Action('error/notFound', 'web');
    }

}