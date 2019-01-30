<?php
define("DS", DIRECTORY_SEPARATOR);
define("ROOT_PATH", dirname(__DIR__));
define("APP_PATH", ROOT_PATH . DS . 'application');

require_once APP_PATH . DS . 'config' . DS . 'constant.php';

require_once ROOT_PATH . DS . 'vendor' . DS . "autoload.php";


try {
    $application = new \App\System\Application();
}catch (Exception $e) {
    echo "<p style='border:5px solid red;background-color:#fff;padding:12px;font-family: verdana, sans-serif;'><strong>Database Error:</strong><br/>{$e->getMessage()}</p>";

}
