<?php

namespace App\System;


use App\Lib\Action;
use App\Lib\Config;
use App\Lib\Database;
use App\Lib\Registry;
use App\Lib\Request;
use App\Lib\Response;
use App\Lib\Router;
use App\Lib\Session;
use App\Model\Language;
use Doctrine\Common\Cache\FilesystemCache;

class Application {

    private const ADMIN_ALIAS = "admin";
    private $isAdminRequested = false;
    private $uri = '/';
    private $url = '/';
    private $registry;
    private $languageID = false;
    private $requestUrl;
    private $isCkfinderRequested = false;

    /**
     * Application constructor.
     */
    public function __construct()
    {
        require_once SYSTEM_PATH . DS . 'common_function.php';
        $this->registry = new Registry();
        $this->registry->Application = $this;
        $this->registry->Response = new Response();
        $this->registry->Response->startResponse();
        $database = new Database(DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD);
        $this->registry->Database = $database;
        $cache = new FilesystemCache(CACHE_PATH);
        $this->registry->Cache = $cache;
        $this->registry->Language = new Language($this->registry);
        $this->processURL();
        session_set_save_handler(new Session($database));
        session_start(array(
            'use_strict_mode' => '1',
            'cookie_httponly' => '1'
        ));
        if(!$this->languageID) {
            if(isset($_SESSION['language_code']) && !empty($_SESSION['language_code'])) {
                $langauge = $this->registry->Language->getLanguageByCode($_SESSION['language_code']);
                $this->languageID = $langauge ? $langauge['language_id'] : false;
            }

            // TODO Cookie Process get Language
        }
        if($this->isAdminRequested) {
            require_once ADMIN_PATH . DS . 'config' . DS . 'admin_constants.php';
        }else {
            require_once WEB_PATH . DS . 'config' . DS . 'web_constants.php';
        }
        $config = new Config();
        $this->registry->Config = $config;
        $this->registry->Config->load(MAIN_CONFIG_FILENAME);
        $router = new Router($this->registry);
        $this->registry->Router = $router;
        foreach ($config->get("pre_actions") as $preAction) {
            $router->addPreRoute(new Action($preAction));
        }
        $this->registry->Request = new Request($this->registry, $this->uri, CONTROLLER_PATH);
        if($this->languageID) {
            $this->registry->Language->setLanguageByID($this->languageID);
        }
        $this->registry->Language->load($config->get("default_language_file_path"));
        $router->Dispatch();
        $this->registry->Response->OutPut();
    }

    private function processURL()
    {

        $_GET['url'] = !empty($_GET['url']) ? $_GET['url'] : '';
        $_GET['url'] = trim($_GET['url'], '/');
        $_GET['url'] = filter_var($_GET['url'], FILTER_SANITIZE_URL);
        $url = explode('/', $_GET['url']);

        $languages = $this->registry->Language->getLanguages();
        if(array_key_exists($url[0],$languages)) {
            $this->languageID = $languages[$url[0]]['language_id'];
            $languageCode = array_shift($url);
        }
        if($url[0] == self::ADMIN_ALIAS) {
            $this->isAdminRequested = true;
            array_shift($url);
        }
        $sURL = $_GET['url'];
        if(isset($languageCode)) {
            $sURL = substr($sURL, strlen($languageCode) + 1);
        }
        if($this->isAdminRequested) {
            $sURL = substr($sURL, strlen(self::ADMIN_ALIAS) + 1);
        }
        $this->uri = !empty($sURL) ? $sURL : $this->uri;
        $this->url = URL . $_GET['url'];
        $this->requestUrl = rtrim(URL, '/') . $_SERVER['REQUEST_URI'];
        if($_GET['url'] == "assets/ckfinder/core/connector/php/connector.php") {
            $this->isCkfinderRequested = true;
            $this->isAdminRequested = true;
            $this->uri = CKFINDER_ROUT;
        }
    }


    /**
     * @return bool
     */
    public function isAdminRequested(): bool
    {
        return $this->isAdminRequested;
    }

    /**
     * @return mixed
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return mixed
     */
    public function getRequestUrl()
    {
        return $this->requestUrl;
    }

}