<?php

namespace App\Web\Controller;

use App\Lib\Request;
use App\Lib\Response;
use App\Lib\Validate;
use App\model\Customer;
use App\Model\Language;
use App\System\Controller;

/**
 * @property Response Response
 * @property Request Request
 * @property Language Language
 */
class ControllerRegister extends Controller {

    public function index() {
        $data = [];
        $messages = [];
        $error = false;
        if(isset($this->Request->post['register-post'])) {
            if(!$this->registry->has("Validate")) {
                $this->registry->Validate = new Validate();
            }
            /** @var Validate $Validate */
            $Validate = $this->Validate;
            $email = isset($this->Request->post['email']) ? $this->Request->post['email'] : false;
            $first_name = isset($this->Request->post['first_name']) ? $this->Request->post['first_name'] : false;
            $last_name = isset($this->Request->post['last_name']) ? $this->Request->post['last_name'] : false;
            $password = isset($this->Request->post['password']) ? $this->Request->post['password'] : false;
            $mobile = isset($this->Request->post['mobile']) ? $this->Request->post['mobile'] : false;
            $email = isset($this->Request->post['email']) ? $this->Request->post['email'] : false;
            if($email && !$Validate::emailValid($email)) {
                $error = true;
                $messages[] = $this->Language->get("error_email_invalid");
            }
            if($password && !$Validate::passwordValid($password)) {
                $error = true;
                $messages[] = $this->Language->get("error_password_invalid");
            }
            if($mobile && !$Validate::mobileValid($mobile)) {
                $error = true;
                $messages[] = $this->Language->get("error_mobile_empty");
            }
            if(!$first_name || empty($first_name)) {
                $error = true;
                $messages[] = $this->Language->get("error_first_name_empty");
            }

            if(!$last_name || empty($last_name)) {
                $error = true;
                $messages[] = $this->Language->get("error_last_name_empty");
            }

            $json = array();
            if(!$error) {
                /** @var Customer $Customer */
                $Customer = $this->load("Customer", $this->registry);
                if($Customer->getCustomerByEmail($email) || $Customer->getCustomerByMobile($mobile)) {
                    $error = true;
                    $messages[] = $this->Language->get("error_exist_such_user");
                }
                if(!$error) {
                    $data['first_name'] = $first_name;
                    $data['last_name']  = $last_name;
                    $data['email']      = $email;
                    $data['password']   = $password;
                    $data['mobile']     = $mobile;
                    $data['language_id']= $this->Language->getLanguageID();
                    $customer_id = $Customer->insertCustomer($data);
                    $Customer->getCustomerByID($customer_id);
                    $json['status'] = 1;
                    $json['messages'] = [$this->Language->get('message_success_done')];
                    $ip = get_ip_address();
                    $option = [];
                    if($ip) {
                        $option['ip'] = $ip;
                    }
                    $Customer->login($option);

                    $token = generateToken();
                    $_SESSION['token'] = $token;
                    $_SESSION['token_time_expiry'] = time() + $this->Config->get('max_token_time_expiry');
                    $_SESSION['user_ip'] = $ip;
                    $_SESSION['user_agent'] = $this->Request->server['HTTP_USER_AGENT'];
                    $_SESSION['login_time'] = time();
                    $_SESSION['login_time_expiry'] = time() + $this->Config->get('max_inactive_login_session_time');
                    $_SESSION['login_status'] = LOGIN_STATUS_LOGIN_FORM;
                    if(isset($this->Request->post['checkout-post'])) {
                        $json['redirect'] = URL . "checkout/index?token=" . $token;
                    }else {
                        $json['redirect'] = URL . "user/index";
                    }
                }
            }
            if($error) {
                $json['status'] = 0;
                $json['messages'] = $messages;
            }
            $this->Response->setOutPut(json_encode($json));
        }else {
            $this->Response->setOutPut($this->render('register/index'));
        }
    }

}