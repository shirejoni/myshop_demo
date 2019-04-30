<?php

namespace App\Web\Controller;

use App\Lib\Config;
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
 * @property Config Config
 */
class ControllerLogin extends Controller {

    public function index() {
        $data = [];
        $messages = [];
        $error = false;
        if(isset($_SESSION['customer']) && !empty($_SESSION['customer']['email']) && !empty($_SESSION['login_status']) && $_SESSION['login_status'] == LOGIN_STATUS_LOGIN_FORM) {
            $token = generateToken();
            $_SESSION['token'] = $token;
            $_SESSION['token_time_expiry'] = time() + $this->Config->get('max_token_time_expiry');
            header("location:" . URL . "user/index?token=" . $token);
            exit();
        }
        if(isset($this->Request->post['login-post'])) {
            if(!$this->registry->has("Validate")) {
                $this->registry->Validate = new Validate();
            }
            /** @var Validate $Validate */
            $Validate = $this->Validate;
            $email = $this->Request->post['email'];
            $password = $this->Request->post['password'];
            if(!$Validate::emailValid($email)) {
                $error = true;
                $messages[] = $this->Language->get("error_email_invalid");
            }
            if(!$Validate::passwordValid($password)) {
                $error = true;
                $messages[] = $this->Language->get("error_password_invalid");
            }
            $json = array();
            if(!$error) {

                /** @var Customer $Customer */
                $Customer = $this->load("Customer", $this->registry);
                if($result = $Customer->getCustomerByEmail($email)) {
                    if(password_verify($password, $result['password'])) {
                        $ip = get_ip_address();
                        $option = [];
                        if($ip) {
                            $option['ip'] = $ip;
                        }
                        $Customer->login($option);
                        $messages[] = $this->Language->get("message_success_login");
                        $json['status'] = 1;
                        $token = generateToken();
                        $_SESSION['token'] = $token;
                        $_SESSION['token_time_expiry'] = time() + $this->Config->get('max_token_time_expiry');
                        $_SESSION['user_ip'] = $ip;
                        $_SESSION['user_agent'] = $this->Request->server['HTTP_USER_AGENT'];
                        $_SESSION['login_time'] = time();
                        $_SESSION['login_time_expiry'] = time() + $this->Config->get('max_inactive_login_session_time');
                        $_SESSION['login_status'] = LOGIN_STATUS_LOGIN_FORM;
                        $json['redirect'] = $this->Application->getUrl() . "?token=" . $token;
                        $json['messages'] = $messages;
                    }else {
                        $error = true;
                        $messages[] = $this->Language->get("error_not_such_user");
                    }
                }else {
                    $error = true;
                    $messages[] = $this->Language->get("error_not_such_user");
                }

            }
            if($error) {
                $json['status'] = 0;
                $json['messages'] = $messages;
            }
            $this->Response->setOutPut(json_encode($json));
        }else {
            var_dump($_SESSION);
            $this->Response->setOutPut($this->render('login/index'));
        }
    }

}