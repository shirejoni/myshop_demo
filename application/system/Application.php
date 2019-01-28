<?php

namespace App\System;


class Application {

    private const ADMIN_ALIAS = "admin";
    private $isAdminRequested = false;
    private $uri;
    private $url;

    /**
     * Application constructor.
     */
    public function __construct()
    {
        $this->processURL();

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
        $this->uri = $sURL;
        $this->url = rtrim(URL, '/') . $_SERVER['REQUEST_URI'];
    }
}