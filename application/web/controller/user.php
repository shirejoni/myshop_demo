<?php

namespace App\Web\Controller;

use App\Lib\Action;
use App\Lib\Request;
use App\Lib\Response;
use App\model\Customer;
use App\Model\Language;
use App\System\Controller;

/**
 * @property Response Response
 * @property Request Request
 * @property Language Language
 */
class ControllerUser extends Controller {

    public function index() {
        $this->Response->setOutPut($this->render('user/index'));
    }

    public function edit() {
        $data = [];
        $messages = [];
        $error = false;
        $customer_id = $_SESSION['customer']['customer_id'];
        /** @var Customer $Customer */
        $Customer = $this->load("Customer", $this->registry);
        $customer = $Customer->getCustomerByID($customer_id);
        if($customer) {
            if(isset($this->Request->post['customer-post'])) {
                if(empty($this->Request->post['customer-first-name'])) {
                    $error = true;
                    $messages[] = $this->Language->get("error_first_name_empty");
                }else if($customer['first_name'] != $this->Request->post['customer-first-name']) {
                    $data['first_name'] = $this->Request->post['customer-first-name'];
                }
                if(empty($this->Request->post['customer-last-name'])) {
                    $error = true;
                    $messages[] = $this->Language->get("error_last_name_empty");
                }else if($customer['last_name'] != $this->Request->post['customer-last-name']) {
                    $data['last_name'] = $this->Request->post['customer-last-name'];
                }
                if(empty($this->Request->post['customer-mobile'])) {
                    $error = true;
                    $messages[] = $this->Language->get("error_mobile_empty");
                }else if($customer['mobile'] != $this->Request->post['customer-mobile']) {
                    $data['mobile'] = $this->Request->post['customer-mobile'];
                }
                $json = [];
                if(!$error) {
                    $Customer->edit($customer_id, $data);
                    $json['status'] = 1;
                    $json['messages'] = [$this->Language->get('message_success_done')];
                    $json['redirect'] = URL . 'user/index?token=' . $_SESSION['token'];
                }
                if($error) {
                    $json['status'] = 0;
                    $json['messages'] = $messages;
                }
                $this->Response->setOutPut(json_encode($json));
                return;
            }else {
                $data['Customer'] = $customer;
                $this->Response->setOutPut($this->render('user/edit', $data));
                return;
            }
        }
        return new Action('error/notFound', 'web');
    }

}