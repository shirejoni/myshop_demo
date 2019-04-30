<?php

namespace App\Web\Controller;

use App\Lib\Config;
use App\Lib\Database;
use App\System\Controller;

/**
 * @property Database Database
 * @property Config Config
 */
class ControllerInitStartup extends Controller {

    public function init() {
        $configs = $this->Database->getRows("SELECT * FROM config");
        $Config = $this->Config;
        foreach ($configs as $config) {
            if($config['serialized'] == 0) {
                $Config->set($config['key'], $config['value']);
            }else {

                $Config->set($config['key'], unserialize($config['value']));
            }
        }
    }
}