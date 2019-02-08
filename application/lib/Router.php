<?php

namespace App\Lib;

use App\Lib\Action;
use App\Lib\Registry;

class Router {
    private $registry;
    private $routes = [];
    private $baseRoute = '';
    private $preRoutes = [];


    /**
     * Router constructor.
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    public function Dispatch()
    {

        if(count($this->preRoutes)) {
            foreach ($this->preRoutes as $preRoute) {
                /** @var Action $preAction */
                $preAction = $preRoute['action'];
                $result = $preAction->execute($this->registry);
                if($result instanceof Action) {
                    $action = $result;
                }
            }
        }
        if(empty($action)) {
            $uri = $this->registry->Application->getUri();
            if($uri == "" || $uri == "/") {
                $uri = 'home/index';
            }
            $action = new Action($uri);
            if(!$action->isStatus()) {
                $action = new Action('error/notFound',"web");
            }
        }
        do {
            $action = $action->execute($this->registry, array(
                'error_route'   => 'error/notFound',
                'error_pre_route' => 'web'
            ));
        }while($action instanceof \App\Lib\Action);
    }


    /**
     * @param Action $action
     * @param array $params
     * @param bool $mainOutPut
     */
    public function addPreRoute(Action $action, array $params = array(), $mainOutPut = false)
    {
        $this->preRoutes[] = array(
            'action'    => $action,
            'params'    => $params,
            'mainOutPut' => $mainOutPut
        );
    }

}