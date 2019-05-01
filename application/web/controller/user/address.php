<?php

namespace App\Web\Controller;

use App\Lib\Action;
use App\Lib\Request;
use App\Lib\Response;
use App\Lib\Validate;
use App\model\Address;
use App\Model\Language;
use App\System\Controller;

/**
 * @property Response Response
 * @property Request Request
 * @property Language Language
 */
class ControllerUserAddress extends Controller {

    public function index() {
        $data = [];
        $Address = $this->load("Address", $this->registry);
        $data['Addresses'] = $Address->getAddressesByCustomerID($_SESSION['customer']['customer_id']);
        $this->Response->setOutPut($this->render('user/adress/index', $data));
    }

    public function add() {
        $data = [];
        $messages = [];
        $error = false;
        /** @var Address $Address */
        $Address = $this->load("Address", $this->registry);
        if(isset($this->Request->post['address-post'])) {
            if(!empty($this->Request->post['address-first-name'])) {
                $data['first_name'] = $this->Request->post['address-first-name'];
            }else {
                $error = true;
                $messages[] = $this->Language->get('error_first_name_empty');
            }
            if(!empty($this->Request->post['address-last-name'])) {
                $data['last_name'] = $this->Request->post['address-last-name'];
            }else {
                $error = true;
                $messages[] = $this->Language->get('error_last_name_empty');
            }
            if(!empty($this->Request->post['address-address'])) {
                $data['address'] = $this->Request->post['address-address'];
            }else {
                $error = true;
                $messages[] = $this->Language->get('error_address_empty');
            }
            if(!empty($this->Request->post['address-province-id']) && (int) $this->Request->post['address-province-id'] &&
            $Address->getProvince((int) $this->Request->post['address-province-id'])) {
                $data['province_id'] = (int) $this->Request->post['address-province-id'];
            }else {
                $error = true;
                $messages[] = $this->Language->get('error_province_empty');
            }

            if(!empty($this->Request->post['address-city-id']) && (int) $this->Request->post['address-city-id'] &&
                $Address->getCity((int) $this->Request->post['address-city-id'])) {
                $data['city_id'] = (int) $this->Request->post['address-city-id'];
            }else {
                $error = true;
                $messages[] = $this->Language->get('error_city_empty');
            }
            if(!$this->registry->has('Validate')) {
                $this->registry->Validate = new Validate();
            }
            /** @var Validate $Validate */
            $Validate = $this->Validate;
            if(!empty($this->Request->post['address-zip-code']) && $Validate::zipCodeValid($this->Request->post['address-zip-code'])) {
                $data['zip_code'] = $this->Request->post['address-zip-code'];
            }else {
                $error = true;
                $messages[] = $this->Language->get('error_zip_code_invalid');
            }
            $json = [];
            if(!$error) {
                $data['customer_id'] = $_SESSION['customer']['customer_id'];
                $Address->insertAddress($data);
                $json['status'] = 1;
                $json['messages'] = [$this->Language->get('message_success_done')];
                $json['redirect'] = URL . 'user/address/index?token=' . $_SESSION['token'];
            }
            if($error) {
                $json['status'] = 0;
                $json['messages'] = $messages;
            }
            $this->Response->setOutPut(json_encode($json));
        }else {
            $data['Provinces'] = $Address->getProvinces();
            $this->Response->setOutPut($this->render('user/adress/add', $data));
        }
    }

    public function getcity() {
        $data = [];
        $messages = [];
        $error = false;
        if(isset($this->Request->post['address-post']) && isset($this->Request->post['province_id'])) {
            $province_id = (int) $this->Request->post['province_id'];
            if($province_id) {
                /** @var Address $Address */
                $Address = $this->load("Address", $this->registry);
                $cities = $Address->getProvinceCities($province_id);
                $json = [];
                if($cities) {
                    $json['status'] = 1;
                    $json['cities'] = $cities;
                }else {
                    $messages[] = $this->Language->get('error_city_not_found_by_province');
                    $json['status'] = 0;
                    $json['messages'] = $messages;
                }
                $this->Response->setOutPut(json_encode($json));
                return;
            }
        }
        return new Action('error/notFound', 'web');
    }

}