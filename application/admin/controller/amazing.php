<?php

namespace App\Admin\Controller;

use App\Lib\Database;
use App\Lib\Request;
use App\Lib\Response;
use App\Model\Amazing;
use App\model\Product;
use App\System\Controller;

/**
 * @property Response Response
 * @property Request Request
 * @property Database Database
 */
class ControllerAmazing extends Controller
{

    public function index()
    {
        $data = [];
        $language_id = $this->Language->getLanguageID();
        /** @var Amazing $Amazing */
        $Amazing = $this->load('Amazing', $this->registry);
        $data['Amazings'] = $Amazing->getAmazings();
        require_once LIB_PATH . DS . 'jdate' . DS . 'jdf.php';
        foreach ($data['Amazings'] as $index => $amazing) {
            $data['Amazings'][$index]['date_start_formatted'] = jdate('Y/m/d H:i:s', $amazing['date_start']);
            $data['Amazings'][$index]['date_end_formatted'] = jdate('Y/m/d H:i:s', $amazing['date_end']);
        }
        $Language = $this->Language;
        $data['Languages'] = $Language->getLanguages();
        $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
        $this->Response->setOutPut($this->render('amazing/index', $data));
    }

    public function add()
    {
        $data = [];
        $messages = [];
        $error = false;
        if (isset($this->Request->post['amazing-post'])) {
            /** @var Product $Product */
            $Product = $this->load('Product', $this->registry);
            if (!empty($this->Request->post['amazing-name'])) {
                $data['name'] = $this->Request->post['amazing-name'];
            } else {
                $error = true;
                $messages[] = $this->Language->get('error_coupon_name_empty');
            }

            if (!empty($this->Request->post['amazing-type'])) {
                $data['type'] = $this->Request->post['amazing-type'];
            } else {
                $error = true;
                $messages[] = $this->Language->get('error_coupon_type_empty');
            }
            if (!empty($this->Request->post['amazing-discount'])) {
                $data['discount'] = (int)$this->Request->post['amazing-discount'];
            } else {
                $data['discount'] = 0;
            }

            if (!empty($this->Request->post['amazing-products'])) {
                $data['products_id'] = [];
                foreach ($this->Request->post['amazing-products'] as $product_id) {
                    $product = $Product->getProduct($product_id);
                    if ($product) {
                        $data['products_id'][] = $product_id;
                    }
                }
            }

            require_once LIB_PATH . DS . 'jdate/jdf.php';
            if (!empty($this->Request->post['amazing-date-start'])) {
                list($date, $time) = explode(' ', $this->Request->post['amazing-date-start']);
                $parts = explode('/', $date);
                list($hour, $minute, $second) = explode(':', $time);
                if (jcheckdate($parts[1], $parts[2], $parts[0])) {
                    $time = jmktime($hour, $minute, $second, $parts[1], $parts[2], $parts[0]);
                    $data['date_start'] = $time;
                }
            }
            if (!empty($this->Request->post['amazing-date-end'])) {
                list($date, $time) = explode(' ', $this->Request->post['amazing-date-end']);
                $parts = explode('/', $date);
                list($hour, $minute, $second) = explode(':', $time);
                if (jcheckdate($parts[1], $parts[2], $parts[0])) {
                    $time = jmktime($hour, $minute, $second, $parts[1], $parts[2], $parts[0]);
                    $data['date_end'] = $time;
                }
            }
            $json = [];

            if (!$error) {
                $data['status'] = 0;
                /** @var Amazing $Amazing */
                $Amazing = $this->load('Amazing', $this->registry);
                $Amazing->insertAmazing($data);
                $json['messages'] = [$this->Language->get('message_success_done')];
                $json['status'] = 1;
                $json['redirect'] = ADMIN_URL . 'amazing/index?token=' . $_SESSION['token'];
            } else {
                $json['status'] = 0;
                $json['messages'] = $messages;
            }
            $this->Response->setOutPut(json_encode($json));
        } else {
            $couponTypes = $this->Config->get('coupon_type');
            foreach ($couponTypes as $index => $couponType) {
                $data['CouponTypes'][] = array(
                    'value' => $couponType,
                    'index' => $index
                );
            }
            $Language = $this->Language;
            $data['Languages'] = $Language->getLanguages();
            $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
            $this->Response->setOutPut($this->render('amazing/add', $data));
        }
    }

    public function edit()
    {
        $data = array();
        $error = false;
        $messages = [];
        if(isset($this->Request->get[0])) {
            $amazing_id = (int) $this->Request->get[0];
            /** @var Amazing $Amazing */
            $Amazing = $this->load("Amazing", $this->registry);
            $amazing = $Amazing->getAmazing($amazing_id);
            $couponTypes = $this->Config->get('coupon_type');

            if($amazing_id && $amazing) {
                if (isset($this->Request->post['amazing-post'])) {
                    /** @var Product $Product */
                    $Product = $this->load('Product', $this->registry);
                    if (!empty($this->Request->post['amazing-name'])) {
                        $data['name'] = $this->Request->post['amazing-name'];
                    } else {
                        $error = true;
                        $messages[] = $this->Language->get('error_coupon_name_empty');
                    }

                    if (!empty($this->Request->post['amazing-type'])) {
                        $data['type'] = $this->Request->post['amazing-type'];
                    } else {
                        $error = true;
                        $messages[] = $this->Language->get('error_coupon_type_empty');
                    }
                    if (!empty($this->Request->post['amazing-discount'])) {
                        $data['discount'] = (int)$this->Request->post['amazing-discount'];
                    } else {
                        $data['discount'] = 0;
                    }

                    if (!empty($this->Request->post['amazing-products'])) {
                        $data['products_id'] = [];
                        foreach ($this->Request->post['amazing-products'] as $product_id) {
                            $product = $Product->getProduct($product_id);
                            if ($product) {
                                $data['products_id'][] = $product_id;
                            }
                        }
                    }

                    require_once LIB_PATH . DS . 'jdate/jdf.php';
                    if (!empty($this->Request->post['amazing-date-start'])) {
                        list($date, $time) = explode(' ', $this->Request->post['amazing-date-start']);
                        $parts = explode('/', $date);
                        list($hour, $minute, $second) = explode(':', $time);
                        if (jcheckdate($parts[1], $parts[2], $parts[0])) {
                            $time = jmktime($hour, $minute, $second, $parts[1], $parts[2], $parts[0]);
                            $data['date_start'] = $time;
                        }
                    }
                    if (!empty($this->Request->post['amazing-date-end'])) {
                        list($date, $time) = explode(' ', $this->Request->post['amazing-date-end']);
                        $parts = explode('/', $date);
                        list($hour, $minute, $second) = explode(':', $time);
                        if (jcheckdate($parts[1], $parts[2], $parts[0])) {
                            $time = jmktime($hour, $minute, $second, $parts[1], $parts[2], $parts[0]);
                            $data['date_end'] = $time;
                        }
                    }
                    $json = [];
                    if(!$error) {
                        $delete = [];
                        $add = [];
                        if($amazing['name'] === $data['name']) {
                            unset($data['name']);
                        }
                        if($data['discount'] == $amazing['discount']) {
                            unset($data['discount']);
                        }
                        if($data['type'] == $amazing['type']) {
                            unset($data['type']);
                        }
                        if($data['date_start'] == $amazing['date_start']) {
                            unset($data['date_start']);
                        }
                        if($data['date_end'] == $amazing['date_end']) {
                            unset($data['date_end']);
                        }
                        if(count($data) > 0) {
                            $Amazing->editAmazing($amazing_id, $data);
                        }

                        $json['status'] = 1;
                        $json['messages'] = [$this->Language->get('message_success_done')];
                        $json['redirect'] = ADMIN_URL . "amazing/index?token=" . $_SESSION['token'];
                    }else {
                        $json['status'] = 0;
                        $json['messages'] = $messages;
                    }
                    $this->Response->setOutPut(json_encode($json));
                    return;
                }else {
                    if(!empty($amazing['products_id'])) {
                        /** @var Product $Product */
                        $Product = $this->load("Product", $this->registry);
                        foreach ($amazing['products_id'] as $index => $product_id) {
                            $product = $Product->getProduct($product_id);
                            $amazing['products_id'][$index] = $product;
                        }
                    }

                    foreach ($couponTypes as $index => $couponType) {
                        $data['CouponTypes'][] = array(
                            'value' => $couponType,
                            'index' => $index
                        );
                    }
                    require_once LIB_PATH . DS . 'jdate/jdf.php';
                    $amazing['date_start'] = jdate('Y-m-d\TH:i:s', $amazing['date_start'], '', '', 'en');
                    $amazing['date_end'] = jdate('Y-m-d\TH:i:s', $amazing['date_end'], '', '', 'en');

                    $data['Languages'] = $this->Language->getLanguages();
                    $data['Amazing'] = $amazing;
                    $data['LanguageDefaultID'] = $this->Language->getDefaultLanguageID();
                    $this->Response->setOutPut($this->render('amazing/edit', $data));
                    return;
                }
            }
        }
        return new Action('error/notFound', 'web');
    }

    public function delete() {
        if(!empty($this->Request->post['amazings_id'])) {
            $json = [];
            /** @var Amazing $Amazing */
            $Amazing = $this->load('Amazing', $this->registry);
            $error = false;
            $this->Database->db->beginTransaction();
            foreach ($this->Request->post['amazings_id'] as $amazing_id) {
                $amazing = $Amazing->getAmazing((int) $amazing_id);
                if((int) $amazing_id && $amazing) {
                    $Amazing->deleteAmazing($amazing_id);
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
                $data['Amazings'] = $Amazing->getAmazings();
                require_once LIB_PATH . DS . 'jdate' . DS . 'jdf.php';
                foreach ($data['Amazings'] as $index => $amazing) {
                    $data['Amazings'][$index]['date_start_formatted'] = jdate('Y/m/d H:i:s', $amazing['date_start']);
                    $data['Amazings'][$index]['date_end_formatted'] = jdate('Y/m/d H:i:s', $amazing['date_end']);
                }
                $Language = $this->Language;
                $data['Languages'] = $Language->getLanguages();
                $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
                $json['data'] = $this->render("amazing/amazings_table", $data);
            }

            $this->Response->setOutPut(json_encode($json));
            return;
        }
        return new Action("error/notFound", 'web');
    }

    public function status() {
        if (isset($this->Request->post['amazing_id']) && isset($this->Request->post['amazing_status'])) {
            $amazing_id = (int)$this->Request->post['amazing_id'];
            $amazing_status = (int)$this->Request->post['amazing_status'];
            /** @var Amazing $Amazing */
            $Amazing = $this->load('Amazing', $this->registry);
            $amazing = $Amazing->getAmazing($amazing_id);
            $json = [];
            if ($amazing_id && $amazing) {
                $Amazing->editAmazing($amazing_id, array(
                    'status' => $amazing_status
                ));
                $json['status'] = 1;
                $json['messages'] = [$this->Language->get('message_success_done')];
            } else {
                $json['status'] = 0;
                $json['messages'] = [$this->Language->get('error_done')];
            }
            $this->Response->setOutPut(json_encode($json));
            return;
        }
        return new Action('error/notFound', 'web');
    }

}