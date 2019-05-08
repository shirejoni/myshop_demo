<?php

namespace App\Admin\Controller;

use App\Lib\Config;
use App\Lib\Database;
use App\Lib\Request;
use App\Lib\Response;
use App\Model\Category;
use App\Model\Coupon;
use App\Model\Language;
use App\model\Product;
use App\System\Controller;

/**
 * @property Response Response
 * @property Config Config
 * @property Language Language
 * @property Request Request
 * @property Database Database
 */
class ControllerCoupon extends Controller {

    public function index() {
        $data = [];
        $language_id = $this->Language->getLanguageID();
        /** @var Coupon $Coupon */
        $Coupon = $this->load("Coupon", $this->registry);
        $data['Coupons'] = $Coupon->getCoupons();
        $Language = $this->Language;
        $data['Languages'] = $Language->getLanguages();
        $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
        $this->Response->setOutPut($this->render('coupon/index', $data));
    }

    public function add() {
        $data = [];
        $messages = [];
        $error = false;

        $couponTypes = $this->Config->get('coupon_type');
        if(isset($this->Request->post['coupon-post'])) {
            $Language = $this->Language;
            $languages = $Language->getLanguages();
            $languageDefaultID = $Language->getDefaultLanguageID();
            /** @var Product $Product */
            $Product = $this->load("Product", $this->registry);
            /** @var Category $Category */
            $Category = $this->load("Category", $this->registry);
            if(!empty($this->Request->post['coupon-name'])) {
                $data['name'] = $this->Request->post['coupon-name'];
            }else {
                $error = true;
                $messages[] = $this->Language->get('error_coupon_name_empty');
            }
            if(!empty($this->Request->post['coupon-code'])) {
                $data['code'] = $this->Request->post['coupon-code'];
            }else {
                $error = true;
                $messages[] = $this->Language->get('error_coupon_code_empty');
            }
            if(!empty($this->Request->post['coupon-type'])) {
                $data['type'] = $this->Request->post['coupon-type'];
            }else {
                $error = true;
                $messages[] = $this->Language->get('error_coupon_type_empty');
            }
            if(!empty($this->Request->post['coupon-discount'])) {
                $data['discount'] = (int) $this->Request->post['coupon-discount'];
            }else {
                $data['discount'] = 0;
            }
            if(!empty($this->Request->post['coupon-minimum-price'])) {
                $data['minimum_price'] = (int) $this->Request->post['coupon-minimum-price'];
            }else {
                $data['minimum_price'] = 0;
            }
            if(!empty($this->Request->post['coupon-count'])) {
                $data['count'] = (int) $this->Request->post['coupon-count'];
            }else {
                $data['count'] = 0;
            }
            if(!empty($this->Request->post['coupon-products'])) {
                $data['products_id'] = [];
                foreach ($this->Request->post['coupon-products'] as $product_id) {
                    $product = $Product->getProduct($product_id);
                    if($product) {
                        $data['products_id'][] = $product_id;
                    }
                }
            }
            if(!empty($this->Request->post['coupon-categories'])) {
                $data['categories_id'] = [];
                foreach ($this->Request->post['coupon-categories'] as $category_id) {
                    $category = $Category->getCategoryByID($category_id);
                    if($category) {
                        $data['categories_id'][] = $category_id;
                    }
                }
            }

            require_once LIB_PATH . DS . 'jdate/jdf.php';
            if (!empty($this->Request->post['coupon-date-start'])) {
                $parts = explode('/', $this->Request->post['coupon-date-start']);
                if (count($parts) == 3 && jcheckdate($parts[1], $parts[2], $parts[0])) {
                    $time = jmktime(0, 0, 0, $parts[1], $parts[2], $parts[0]);
                    $data['date_start'] = $time;
                }
            }
            if (!empty($this->Request->post['coupon-date-end'])) {
                $parts = explode('/', $this->Request->post['coupon-date-end']);
                if (count($parts) == 3 && jcheckdate($parts[1], $parts[2], $parts[0])) {
                    $time = jmktime(0, 0, 0, $parts[1], $parts[2], $parts[0]);
                    $data['date_end'] = $time;
                }
            }
            $json = [];

            if(!$error) {
                $data['status'] = 0;
                /** @var Coupon $Coupon */
                $Coupon = $this->load("Coupon", $this->registry);
                $Coupon->insertCoupon($data);
                $json['messages'] = [$this->Language->get('message_success_done')];
                $json['status'] = 1;
                $json['redirect'] = ADMIN_URL . 'coupon/index?token=' . $_SESSION['token'];
            }else {
                $json['status'] = 0;
                $json['messages'] = $messages;
            }
            $this->Response->setOutPut(json_encode($json));
        }else {
            foreach ($couponTypes as $index => $couponType) {
                $data['CouponTypes'][] = array(
                    'value' => $couponType,
                    'index' => $index
                );
            }
            $Language = $this->Language;
            $data['Languages'] = $Language->getLanguages();
            $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
            $this->Response->setOutPut($this->render('coupon/add', $data));
        }
    }

    public function edit() {
        $data = array();
        $error = false;
        $messages = [];
        if(isset($this->Request->get[0])) {
            $coupon_id = (int) $this->Request->get[0];
            /** @var Coupon $Coupon */
            $Coupon = $this->load("Coupon", $this->registry);
            $coupon = $Coupon->getCoupon($coupon_id);
            $couponTypes = $this->Config->get('coupon_type');

            if($coupon_id && $coupon) {
                if(isset($this->Request->post['coupon-post'])) {
                    $Language = $this->Language;
                    $languages = $Language->getLanguages();
                    $languageDefaultID = $Language->getDefaultLanguageID();
                    /** @var Product $Product */
                    $Product = $this->load("Product", $this->registry);
                    /** @var Category $Category */
                    $Category = $this->load("Category", $this->registry);
                    if(!empty($this->Request->post['coupon-name'])) {
                        $data['name'] = $this->Request->post['coupon-name'];
                    }else {
                        $error = true;
                        $messages[] = $this->Language->get('error_coupon_name_empty');
                    }
                    if(!empty($this->Request->post['coupon-code'])) {
                        $data['code'] = $this->Request->post['coupon-code'];
                    }else {
                        $error = true;
                        $messages[] = $this->Language->get('error_coupon_code_empty');
                    }
                    if(!empty($this->Request->post['coupon-type'])) {
                        $data['type'] = $this->Request->post['coupon-type'];
                    }else {
                        $error = true;
                        $messages[] = $this->Language->get('error_coupon_type_empty');
                    }
                    if(!empty($this->Request->post['coupon-discount'])) {
                        $data['discount'] = (int) $this->Request->post['coupon-discount'];
                    }else {
                        $data['discount'] = 0;
                    }
                    if(!empty($this->Request->post['coupon-minimum-price'])) {
                        $data['minimum_price'] = (int) $this->Request->post['coupon-minimum-price'];
                    }else {
                        $data['minimum_price'] = 0;
                    }
                    if(!empty($this->Request->post['coupon-count'])) {
                        $data['count'] = (int) $this->Request->post['coupon-count'];
                    }else {
                        $data['count'] = 0;
                    }
                    if(!empty($this->Request->post['coupon-products'])) {
                        $data['products_id'] = [];
                        foreach ($this->Request->post['coupon-products'] as $product_id) {
                            $product = $Product->getProduct($product_id);
                            if($product) {
                                $data['products_id'][] = $product_id;
                            }
                        }
                    }
                    if(!empty($this->Request->post['coupon-categories'])) {
                        $data['categories_id'] = [];
                        foreach ($this->Request->post['coupon-categories'] as $category_id) {
                            $category = $Category->getCategoryByID($category_id);
                            if($category) {
                                $data['categories_id'][] = $category_id;
                            }
                        }
                    }

                    require_once LIB_PATH . DS . 'jdate/jdf.php';
                    if (!empty($this->Request->post['coupon-date-start'])) {
                        $parts = explode('/', $this->Request->post['coupon-date-start']);
                        if (count($parts) == 3 && jcheckdate($parts[1], $parts[2], $parts[0])) {
                            $time = jmktime(0, 0, 0, $parts[1], $parts[2], $parts[0]);
                            $data['date_start'] = $time;
                        }
                    }
                    if (!empty($this->Request->post['coupon-date-end'])) {
                        $parts = explode('/', $this->Request->post['coupon-date-end']);
                        if (count($parts) == 3 && jcheckdate($parts[1], $parts[2], $parts[0])) {
                            $time = jmktime(0, 0, 0, $parts[1], $parts[2], $parts[0]);
                            $data['date_end'] = $time;
                        }
                    }
                    $json = [];
                    if(!$error) {
                        $delete = [];
                        $add = [];
                        if($coupon['name'] == $data['name']) {
                            unset($data['name']);
                        }
                        if($coupon['code'] == $data['code']) {
                            unset($data['code']);
                        }
                        if($data['discount'] == $coupon['discount']) {
                            unset($data['discount']);
                        }
                        if($data['type'] == $coupon['type']) {
                            unset($data['type']);
                        }
                        if($data['minimum_price'] == $coupon['minimum_price']) {
                            unset($data['minimum_price']);
                        }
                        if($data['date_start'] == $coupon['date_start']) {
                            unset($data['date_start']);
                        }
                        if($data['date_end'] == $coupon['date_end']) {
                            unset($data['date_end']);
                        }
                        if(count($data) > 0) {
                            $Coupon->editCoupon($coupon_id, $data);
                        }

                        $json['status'] = 1;
                        $json['messages'] = [$this->Language->get('message_success_done')];
                        $json['redirect'] = ADMIN_URL . "coupon/index?token=" . $_SESSION['token'];
                    }else {
                        $json['status'] = 0;
                        $json['messages'] = $messages;
                    }
                    $this->Response->setOutPut(json_encode($json));
                    return;
                }else {
                    if(!empty($coupon['products_id'])) {
                        /** @var Product $Product */
                        $Product = $this->load("Product", $this->registry);
                        foreach ($coupon['products_id'] as $index => $product_id) {
                            $product = $Product->getProduct($product_id);
                            $coupon['products_id'][$index] = $product;
                        }
                    }
                    if(!empty($coupon['categories_id'])) {
                        /** @var Category $Category */
                        $Category = $this->load("Category", $this->registry);
                        foreach ($coupon['categories_id'] as $index => $category_id) {
                            $category = $Category->getCategoryByID($category_id);
                            $coupon['categories_id'][$index] = $category;
                        }
                    }

                    foreach ($couponTypes as $index => $couponType) {
                        $data['CouponTypes'][] = array(
                            'value' => $couponType,
                            'index' => $index
                        );
                    }
                    require_once LIB_PATH . DS . 'jdate/jdf.php';
                    $coupon['date_start'] = jdate('Y-m-d', $coupon['date_start'], '', '', 'en');
                    $coupon['date_end'] = jdate('Y-m-d', $coupon['date_end'], '', '', 'en');

                    $data['Languages'] = $this->Language->getLanguages();
                    $data['Coupon'] = $coupon;
                    $data['LanguageDefaultID'] = $this->Language->getDefaultLanguageID();
                    $this->Response->setOutPut($this->render('coupon/edit', $data));
                    return;
                }
            }
        }
        return new Action('error/notFound', 'web');
    }

    public function delete() {
        if(!empty($this->Request->post['coupons_id'])) {
            $json = [];
            /** @var Coupon $Coupon */
            $Coupon = $this->load("Coupon", $this->registry);
            $error = false;
            $this->Database->db->beginTransaction();
            foreach ($this->Request->post['coupons_id'] as $coupon_id) {
                $coupon = $Coupon->getCoupon((int) $coupon_id);
                if((int) $coupon_id && $coupon) {
                    $Coupon->deleteCoupon($coupon_id);
                }else {
                    $error = true;
                }
            }
            if($error) {
                $this->Database->db->rollBack();
                $json['status'] = 0;
                $json['messages'] = [$this->Language->get('error_done')];
            }else {
                $this->Database->db->commit();
                $json['status'] = 1;
                $json['messages'] = [$this->Language->get('message_success_done')];
                $data = [];
                $language_id = $this->Language->getLanguageID();
                $data['Coupons'] = $Coupon->getCoupons();
                $Language = $this->Language;
                $data['Languages'] = $Language->getLanguages();
                $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
                $json['data'] = $this->render("coupon/coupons_table", $data);
            }

            $this->Response->setOutPut(json_encode($json));
            return;
        }
        return new Action("error/notFound", 'web');
    }


}
