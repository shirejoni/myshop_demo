<?php

namespace App\Lib;

use App\Lib\Action;
use App\Lib\Registry;

class Router {
    private $registry;
    private $baseRoute = '';
    private $preRoutes = [];
    private $routes = [];
    private $runRoutes = [];
    /**
     * @var array
     */
    private $funcRoutes = [];
    /**
     * @var bool
     */
    private $isResponded = false;


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
            $method = $this->getRequestMethod();
            if(isset($this->routes[$method])) {
                $this->handle($this->routes[$method], $uri);
            }
            foreach ($this->funcRoutes as $funcRoute) {
                $result = call_user_func_array($funcRoute['fn'], $funcRoute['params']);
                if($result instanceof Action) {
                    $action = $result;
                }
                if($funcRoute['mainOutPut'] == true) {
                    $this->isResponded = true;
                }
            }

            if(empty($action) || !isset($action)) {

                foreach ($this->runRoutes as $runRoute) {

                    /** @var Action $preAction */
                    $preAction = $runRoute['action'];
                    $result = $preAction->execute($this->registry);
                    if($result instanceof Action) {
                        $action = $result;
                    }
                    if($runRoute['mainOutPut'] == true) {
                        $this->isResponded = true;
                    }
                }
            }

            if($uri == "" || $uri == "/") {
                $uri = 'home/index';
            }
            if(!$this->isResponded && !isset($action)) {
                $action = new Action($uri);
                if(!$action->isStatus()) {
                    $action = new Action('error/notFound',"web");
                }
            }
        }
        if(isset($action)) {
            while($action instanceof \App\Lib\Action) {
                $action = $action->execute($this->registry, array(
                    'error_route'   => 'error/notFound',
                    'error_pre_route' => 'web'
                ));
            };
        }
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

    public function match($methods, $pattern, $fn, $preRoute = false, $mainOutPut = false)
    {
        $pattern = $this->baseRoute . '/' . trim($pattern, '/');
        $pattern = $this->baseRoute ? rtrim($pattern, '/') : $pattern;
        foreach (explode('|', $methods) as $method) {
            $this->routes[$method][] = [
                'pattern' => ltrim($pattern, '/'),
                'fn' => $fn,
                'preRoute'      => $preRoute ? $preRoute : '',
                'mainOutPut'    =>  $mainOutPut ? true : false,
            ];
        }
    }

    /**
     * Shorthand for a route accessed using any method.
     *
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $fn The handling function to be executed
     * @param bool $preRoute
     * @param bool $mainOutPut
     */
    public function all($pattern, $fn, $preRoute = false, $mainOutPut = false)
    {
        $this->match('GET|POST|PUT|DELETE|OPTIONS|PATCH|HEAD', $pattern, $fn, $preRoute, $mainOutPut);
    }

    /**
     * Shorthand for a route accessed using GET.
     *
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $fn The handling function to be executed
     * @param bool $preRoute
     * @param bool $mainOutPut
     */
    public function get($pattern, $fn, $preRoute = false, $mainOutPut = false)
    {
        $this->match('GET', $pattern, $fn, $preRoute, $mainOutPut);
    }
    /**
     * Shorthand for a route accessed using POST.
     *
     * @param string          $pattern A route pattern such as /about/system
     * @param object|callable $fn      The handling function to be executed
     */
    public function post($pattern, $fn, $preRoute = false, $mainOutPut = false)
    {
        $this->match('POST', $pattern, $fn, $preRoute, $mainOutPut);
    }

    public function mount($baseRoute, $fn)
    {
        // Track current base route
        $curBaseRoute = $this->baseRoute;
        // Build new base route string
        $this->baseRoute .= $baseRoute;
        // Call the callable
        call_user_func($fn);
        // Restore original base route
        $this->baseRoute = $curBaseRoute;
    }

    private function handle($routes, $uri, $quitAfterRun = false)
    {
        // Counter to keep track of the number of routes we've handled
        $numHandled = 0;
        // Loop all routes
        foreach ($routes as $route) {
            // Replace all curly braces matches {} into word patterns (like Laravel)
            $route['pattern'] = preg_replace('/\/{(.*?)}/', '/(.*?)', $route['pattern']);
            // we have a match!

            if (preg_match_all('#^' . $route['pattern'] . '$#', $uri, $matches, PREG_OFFSET_CAPTURE)) {
                // Rework matches to only contain the matches, not the orig string
                $matches = array_slice($matches, 1);
                // Extract the matched URL parameters (and only the parameters)
                $params = array_map(function ($match, $index) use ($matches) {
                    // We have a following parameter: take the substring from the current param position until the next one's position (thank you PREG_OFFSET_CAPTURE)
                    if (isset($matches[$index + 1]) && isset($matches[$index + 1][0]) && is_array($matches[$index + 1][0])) {
                        return trim(substr($match[0][0], 0, $matches[$index + 1][0][1] - $match[0][1]), '/');
                    } // We have no following parameters: return the whole lot
                    return isset($match[0][0]) ? trim($match[0][0], '/') : null;
                }, $matches, array_keys($matches));

                // Call the handling function with the URL parameters if the desired input is callable
                $this->invoke($route['fn'], $route['preRoute'], $route['mainOutPut'], $params);
                ++$numHandled;
                // If we need to quit, then quit
                if ($quitAfterRun) {
                    break;
                }
            }
        }
        // Return the number of routes handled
        return $numHandled;
    }

    private function invoke($fn, $preRoute, $mainOutPut, $params = [])
    {
        if (is_callable($fn)) {
            $this->funcRoutes[] = array(
                'fn'            => $fn,
                'params'        => $params,
                'mainOutPut'    => $mainOutPut
            );

        }
        // If not, check the existence of special parameters
        else {
            $route = $fn;
            $action = new Action($route, $preRoute);
            $action->setData('params', $params);

            if($action->isStatus()) {
                $this->runRoutes[] = array(
                    'action'    => $action,
                    'params'    => $params,
                    'mainOutPut'=> $mainOutPut
                );
            }
        }
    }

    public function getRequestMethod()
    {
        // Take the method as found in $_SERVER
        $method = $_SERVER['REQUEST_METHOD'];
        // If it's a HEAD request override it to being GET and prevent any output, as per HTTP Specification
        // @url http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.4
        if ($_SERVER['REQUEST_METHOD'] == 'HEAD') {
            ob_start();
            $method = 'GET';
        }
        // If it's a POST request, check for a method override header
        elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $headers = $this->getRequestHeaders();
            if (isset($headers['X-HTTP-Method-Override']) && in_array($headers['X-HTTP-Method-Override'], ['PUT', 'DELETE', 'PATCH'])) {
                $method = $headers['X-HTTP-Method-Override'];
            }
        }
        return $method;
    }
    public function getRequestHeaders()
    {
        $headers = [];
        // If getallheaders() is available, use that
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            // getallheaders() can return false if something went wrong
            if ($headers !== false) {
                return $headers;
            }
        }
        // Method getallheaders() not available or went wrong: manually extract 'm
        foreach ($_SERVER as $name => $value) {
            if ((substr($name, 0, 5) == 'HTTP_') || ($name == 'CONTENT_TYPE') || ($name == 'CONTENT_LENGTH')) {
                $headers[str_replace([' ', 'Http'], ['-', 'HTTP'], ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }



}