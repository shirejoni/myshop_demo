<?php

namespace App\Admin\Controller;

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
 */
class ControllerLogin extends Controller {


    public function index() {
        $data = array();
        $error = false;
        $messages = [];
        if(!empty($this->Request->post['username']) && !empty($this->Request->post['password'])) {
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
                        $json['redirect'] = $this->Application->getUrl();
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
            $this->Response->setOutPut($this->render('login/index', $data));
        }
    }


}