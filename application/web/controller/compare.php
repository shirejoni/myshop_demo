<?php

namespace App\Web\Controller;

use App\Lib\Action;
use App\Lib\Request;
use App\Lib\Response;
use App\Model\AttributeGroup;
use App\model\Image;
use App\Model\Language;
use App\model\Product;
use App\System\Controller;


/**
 * @property Request Request
 * @property Response Response
 * @property Language Language
 * */
class ControllerCompare extends Controller {

    public function toggle() {
        if(isset($this->Request->post['product_id'])) {
            /** @var Product $Product */
            $Product = $this->load("Product", $this->registry);
            $product = $Product->getProduct((int)$this->Request->post['product_id']);
            $json = [];
            if($product) {
                if(isset($_SESSION['compares_id']) && in_array($product['product_id'], $_SESSION['compares_id'])) {
                    $index = array_search($product['product_id'], $_SESSION['compares_id']);
                    unset($_SESSION['compares_id'][$index]);
                    $json['messages'] = [$this->Language->get('message_success_done_compare_delete')];
                }else {
                    if(isset($_SESSION['compares_id'])) {
                        $_SESSION['compares_id'][] = $product['product_id'];
                    }else {
                        $_SESSION['compares_id'] = [];
                        $_SESSION['compares_id'][] = $product['product_id'];
                    }
                    $json['messages'] = [$this->Language->get('message_success_done_compare')];
                }
                $json['status'] = 1;
                $json['count_compare'] = count($_SESSION['compares_id']);
                $this->Response->setOutPut(json_encode($json));
                return;
            }
        }
        return new Action('error/notFound', 'web');
    }

    public function index() {
        if(isset($_SESSION['compares_id']) && count($_SESSION['compares_id']) >= 2 && count($_SESSION['compares_id']) <= 4) {
            $data = [];
            $error = false;
            $messages = [];
            /** @var Product $Product */
            $Product = $this->load("Product", $this->registry);
            /** @var Image $Image */
            $Image = $this->load("Image", $this->registry);
            /** @var AttributeGroup $AttributeGroup */
            $AttributeGroup = $this->load("AttributeGroup", $this->registry);
            $data['Products'] = [];
            $data['AttributeGroups'] = [];
            foreach ($_SESSION['compares_id'] as $product_id) {
                $product = $Product->getProduct($product_id);
                if (is_file(ASSETS_PATH . DS . substr($product['image'], strlen(ASSETS_URL)))) {
                    $image = ASSETS_URL . $Image->resize(substr($product['image'], strlen(ASSETS_URL)), 400, 400);
                } else {
                    $image = ASSETS_URL . $Image->resize('img/no-image.jpeg', 300, 200);
                }
                $product['image'] = $image;
                $attributes = $Product->getAttributes($product['product_id']);
                foreach ($attributes as $attribute) {
                    $product['attributes'][$attribute['attribute_id']] = $attribute;
                }
                $data['Products'][] = $product;
                foreach ($attributes as $attribute) {
                    if(!isset($data['AttributeGroups'][$attribute['attribute_group_id']])) {
                        $attributeGroup = $AttributeGroup->getAttributeGroupByID($attribute['attribute_group_id']);
                        $data['AttributeGroups'][$attributeGroup['attribute_group_id']]['name'] = $attributeGroup['name'];
                        $data['AttributeGroups'][$attributeGroup['attribute_group_id']]['attributes'] = [];
                    }
                    if(!isset($data['AttributeGroups'][$attribute['attribute_group_id']]['attributes'][$attribute['attribute_id']])) {
                        $data['AttributeGroups'][$attribute['attribute_group_id']]['attributes'][$attribute['attribute_id']] = $attribute['name'];
                    }
                }
            }
            if(count($data['Products']) < 4) {
                for(;count($data['Products']) < 4;) {
                    $data['Products'][] = [];
                }
            }

            $this->Response->setOutPut($this->render('compare/index', $data));
            return;
        }
        return new Action('error/notFound', 'web');
    }
}