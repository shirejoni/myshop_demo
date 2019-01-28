<?php
define("DS", DIRECTORY_SEPARATOR);
define("ROOT_PATH", dirname(__DIR__));
define("APP_PATH", ROOT_PATH . DS . 'application');

require_once ROOT_PATH . DS . 'vendor' . DS . "autoload.php";


try {
    $application = new \App\System\Application();
}catch (Exception $e) {
    echo $e->getMessage();
}
