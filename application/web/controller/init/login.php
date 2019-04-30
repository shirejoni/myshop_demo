<?php

namespace App\Web\Controller;

use App\Lib\Action;
use App\Lib\Config;
use App\Lib\Request;
use App\Model\Language;
use App\System\Application;
use App\System\Controller;

/**
 * @property Application Application
 * @property Request Request
 * @property Language Language
 * @property Config Config
 */
class ControllerInitLogin extends Controller {
    public function index() {
        $route = $this->Application->getUri();
        $ignore_routes = array(
            'login/index',
            'login/forget',
            'login/reset',

        );
        if(!in_array($route, $ignore_routes) && !isset($_SESSION['customer'])) {
            $action = new Action('login/index');
            return $action;
        }
        if(isset($_SESSION['customer'])) {
            if($_SESSION['login_time_expiry'] < time()) {
                unset($_SESSION['user'], $_SESSION['login_time_expiry'], $_SESSION['login_time'], $_SESSION['user_agent'], $_SESSION['ip']);
                $action = new Action('login/index');
                return $action;
            }else {
                $_SESSION['login_time_expiry'] = time() + $this->Config->get('max_inactive_login_session_time');
            }
            if($_SESSION['user_agent'] != $this->Request->server['HTTP_USER_AGENT'] || $_SESSION['user_ip'] != get_ip_address()){
                unset($_SESSION['user'], $_SESSION['login_time_expiry'], $_SESSION['login_time'], $_SESSION['user_agent'], $_SESSION['ip']);
                $action = new Action('login/index');
                return $action;
            }
        }

        /*
         * Token
         * */
        $ignore_routes = array(
            'login/index',
            'login/forget',
            'login/reset',
            'error/notfound',
            'error/permission'
        );
//        if(!in_array($route, $ignore_routes) && (!isset($this->Request->get['token']) || !isset($_SESSION['token'])|| empty($this->Request->get['token']) || $_SESSION['token'] != $this->Request->get['token'] || !isset($_SESSION['token_time_expiry']) || $_SESSION['token_time_expiry'] < time())) {
//            unset($_SESSION['user'], $_SESSION['login_time_expiry'], $_SESSION['login_time'], $_SESSION['user_agent'], $_SESSION['ip'], $_SESSION['token_time_expiry'] , $_SESSION['token']);
//            $action = new Action('login/index');
//            $action->setData('error_messages', [$this->Language->get("error_invalid_token")]);
//            return $action;
//        }

        if(!isset($this->Request->post['post'])) {
            $token = generateToken();
            $_SESSION['token'] = $token;
            $_SESSION['token_time_expiry'] = time() + $this->Config->get('max_token_time_expiry');
        }


    }

}