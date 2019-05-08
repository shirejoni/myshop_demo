<?php

namespace App\Web\Controller;

use App\Lib\Config;
use App\Lib\Database;
use App\model\Customer;
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

    public function customer() {
        if(isset($_SESSION['customer'])) {

            /** @var Customer $Customer */
            $Customer = $this->load('Customer', $this->registry);
            $Customer->getCustomerByID($_SESSION['customer']['customer_id']);
            $this->registry->Customer = $Customer;
            return array(
                'Customer'  => $Customer
            );
        }
    }
}