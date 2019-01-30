<?php

namespace App\Lib;


use App\System\Controller;

class Action {
    private $controller_path;
    private $controller_namespace;
    private $route;
    private $method;
    private $status = false;
    private $file_path;


    /**
     * Action constructor.
     * @param $route
     * @param bool $preRoute
     */
    public function __construct($route, $preRoute = false)
    {

        switch ($preRoute) {
            case "web" :
                $this->controller_path = WEB_PATH . DS . 'controller';
                $this->controller_namespace = "App\\Web\\Controller";
                break;
            case "admin" :
                $this->controller_path = ADMIN_PATH . DS . 'controller';
                $this->controller_namespace = "App\\Admin\\Controller";
                break;
            default :
                $this->controller_path =  CONTROLLER_PATH;
                $this->controller_namespace =  CONTROLLER_NAMESPACE;
        }
        $parts = explode("/", $route);
        while ($parts) {
            $file = $this->controller_path . DS . implode(DS, $parts) . '.php';
            if(file_exists($file)) {
                $this->file_path = $file;
                $this->route = implode('/', $parts);
                break;
            }else {
                $this->method = array_pop($parts);
            }
        }
        if(!empty($this->route) && !empty($this->method)) {
            $this->status = true;
        }

    }

    /**
     * @return bool
     */
    public function isStatus(): bool
    {
        return $this->status;
    }

    public function execute($registry, $option = array()) {
        if(substr($this->route, 0 ,2) == "__") {
            throw new \Exception("Calling Magic Method is not Allowed");
        }
        if(file_exists($this->file_path)) {
            require_once $this->file_path;
            $className = "\\" . $this->controller_namespace . "\\Controller";
            preg_replace_callback('/[a-zA-Z0-9]+/', function ($matches) use(&$className) {
                $className .= ucfirst($matches[0]);
            }, $this->route);
            $class = new $className($registry);
            if(method_exists($class, $this->method)) {
                return call_user_func_array([$class, $this->method], array());
            }else {
                if(!empty($option['error_route'])) {
                    $errorPreRoute = !empty($option['error_pre_route']) ? $option['error_pre_route'] : false;
                    $this->__construct($option['error_route'], $option['error_pre_route']);
                    if($this->isStatus()) {
                        return $this->execute($registry);
                    }
                }
                throw new \Exception("No Such Method In Controller Found {$this->route} : {$this->method} ");
            }
        }else {
            throw new \Exception("No Such Controller Found {$this->route} : {$this->method} ");
        }
    }
}