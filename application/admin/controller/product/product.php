<?php

namespace App\Admin\Controller;

use App\Lib\Config;
use App\Lib\Database;
use App\Lib\Request;
use App\Lib\Response;
use App\Model\Language;
use App\Model\Length;
use App\Model\Stock;
use App\Model\Weight;
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
        /** @var Stock $Stock */
        $Stock = $this->load("Stock", $this->registry);
        /** @var Weight $Weight */
        $Weight = $this->load("Weight", $this->registry);
        /** @var Length $Length */
        $Length = $this->load("Length", $this->registry);

        $data['Languages'] = $Language->getLanguages();
        $data['StocksStatus'] = $Stock->getStocks();
        $data['Weights'] = $Weight->getWeights();
        $data['Lengths'] = $Length->getLengths();
        $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
        $this->Response->setOutPut($this->render('product/product/add', $data));
    }

}