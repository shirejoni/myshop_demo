<?php
namespace App\System;

use App\Lib\Registry;

class Controller {

    protected $registry;
    private $twig;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
        $loader = new \Twig_Loader_Filesystem(VIEW_PATH);
        $this->twig = new \Twig_Environment($loader);
    }

    public function __get($name)
    {
        return $this->registry->{$name};
    }

    public function render($path, $data = array()) {
        $_ = array(
            'URL'           => URL,
            "CURRENT_URL"   => $this->Application->getUrl(),
            "Translate"     => $this->Language->all(),
        );
        $data = array_merge($_, $data);
        return $this->twig->render($path . ".twig", $data);
    }

    protected function load($name, ...$params) {

        $parts = explode("\\",$name);
        $modelID = strtolower(implode("_", $parts));
        if(!$this->registry->has($modelID)) {
            $className = array_pop($parts);
            $file = MODEL_PATH ;
            if(count($parts) > 0) {
                $file .= DS . strtolower(implode(DS, $parts));
            }
            $file .= DS . ucfirst($className) . ".php";
            if(file_exists($file)) {
                require_once $file;
                $className = MODEL_NAMESPACE . "\\" . ucfirst($className);
                $modelObject = new $className(...$params);
                $this->registry->{$modelID} = $modelObject;
            }else {
                throw new \Exception("An Unknown Model Call please check it name={$name}");
            }
        }
        return $this->registry->{$modelID};
    }



}