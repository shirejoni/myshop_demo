<?php

namespace App\Admin\Controller;

use App\Lib\Response;
use App\System\Controller;

/**
 * @property Response Response
 */
class ControllerCkfinder extends Controller {

    public function ckfinder() {
        require_once LIB_PATH . DS . 'Ckfinder' . DS . "connector.php";

    }
    public function index() {
        $this->Response->setOutPut($this->render("ckfinder/index"));
    }

}