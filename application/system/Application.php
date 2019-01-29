<?php

namespace App\System;


use App\Lib\Config;
use App\Lib\Registry;
use App\Lib\Response;
use App\Lib\Router;

class Application {

    private const ADMIN_ALIAS = "admin";
    private $isAdminRequested = false;
    private $uri = '/';
    private $url = '/';
    private $registry;

    /**
     * Application constructor.
     */
    public function __construct()
    {
        $this->registry = new Registry();
        $this->registry->Application = $this;
        $this->registry->Response = new Response();
        $this->processURL();

        if($this->isAdminRequested) {
            require_once ADMIN_PATH . DS . 'config' . DS . 'admin_constants.php';
        }else {
            require_once WEB_PATH . DS . 'config' . DS . 'web_constants.php';
        }
        $this->registry->Config = new Config();
        $this->registry->Config->load(MAIN_CONFIG_FILENAME);
        $router = new Router($this->registry);
        $this->registry->Router = $router;

        $router->Dispatch();
        $this->registry->Response->OutPut();
    }

    private function processURL()
    {

        $_GET['url'] = !empty($_GET['url']) ? $_GET['url'] : '';
        $_GET['url'] = trim($_GET['url'], '/');
        $_GET['url'] = filter_var($_GET['url'], FILTER_SANITIZE_URL);
        $url = explode('/', $_GET['url']);

        // TODO Language Process
        if($url[0] == self::ADMIN_ALIAS) {
            $this->isAdminRequested = true;
            array_shift($url);
        }
        $sURL = $_GET['url'];
        if($this->isAdminRequested) {
            $sURL = substr($sURL, strlen(self::ADMIN_ALIAS) + 1);
        }

        $this->uri = !empty($sURL) ? $sURL : $this->uri;
        $this->url = rtrim(URL, '/') . $_SERVER['REQUEST_URI'];
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

}