<?php

namespace App\Admin\Controller;

use App\Lib\Response;
use App\System\Controller;

/**
 * @property Response Response
 */
class ControllerHome extends Controller {

    public function index() {
        $this->Response->setOutPut($this->render('home/index'));

    }


}