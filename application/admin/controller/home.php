<?php

namespace App\Admin\Controller;

use App\System\Controller;

class ControllerHome extends Controller {

    public function index() {
        echo $this->render('home/index');
    }


}