<?php

namespace App\Admin\Controller;

use App\Lib\Config;
use App\Lib\Database;
use App\Lib\Request;
use App\Lib\Response;
use App\Model\Language;
use App\System\Controller;


/**
 * @property Response Response
 * @property Request Request
 * @property Database Database
 * @property Language Language
 * @property Config Config
 */
class ControllerProductProduct extends Controller {

    public function index()
    {

    }

    public function add()
    {
        $data = [];
        $Language = $this->Language;
        $data['Languages'] = $Language->getLanguages();
        $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
        $this->Response->setOutPut($this->render('product/product/add', $data));
    }

}