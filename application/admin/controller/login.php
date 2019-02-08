<?php

namespace App\Admin\Controller;

use App\Lib\Config;
use App\Lib\Request;
use App\Lib\Response;
use App\Lib\Validate;
use App\Model\Language;
use App\Model\User;
use App\System\Application;
use App\System\Controller;

/**
 * @property Response Response
 * @property Request Request
 * @property Language Language
 * @property Application Application
 * @property Config Config
 * @property Validate Validate
 */
class ControllerLogin extends Controller {


    public function index() {
        $data = array();
        $error = false;
        $messages = [];
        if(isset($_SESSION['user']) && !empty($_SESSION['user']['email']) && !empty($_SESSION['login_status']) && $_SESSION['login_status'] == LOGIN_STATUS_LOGIN_FORM) {
            $token = generateToken();
            $_SESSION['token'] = $token;
            $_SESSION['token_time_expiry'] = time() + $this->Config->get('max_token_time_expiry');
            header("location:" . ADMIN_URL . "?token=" . $token);
            exit();
        }
        if(!empty($this->Request->post['username']) && !empty($this->Request->post['password']) && !isset($this->data['error_messages'])) {
            if(!$this->registry->has("Validate")) {
                $this->registry->Validate = new Validate();
            }
            /** @var Validate $Validate */
            $Validate = $this->Validate;
            $email = $this->Request->post['username'];
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
                /** @var User $User */
                $User = $this->load("User", $this->registry);
                if($result = $User->getUserByEmail($email)) {
                    if(password_verify($password, $result['password'])) {
                        $ip = get_ip_address();
                        $option = [];
                        if($ip) {
                            $option['ip'] = $ip;
                        }
                        $User->login($option);
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
            echo json_encode($json);
        }else {
            if(!empty($this->data['error_messages']) && count($this->data['error_messages']) > 0) {
                $data['Status'] = "error";
                foreach ($this->data['error_messages'] as $error_message) {
                    $data['Messages'][] = $error_message;
                }

            }
            $this->Response->setOutPut($this->render('login/index', $data));
        }
    }


}