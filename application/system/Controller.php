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
            'URL'   => URL,
        );
        $data = array_merge($_, $data);
        return $this->twig->render($path . ".twig", $data);
    }



}