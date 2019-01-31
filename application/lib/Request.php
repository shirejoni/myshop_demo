<?php


namespace App\Lib;


class Request
{
    public $get = [];
    public $post = [];
    public $server = [];

    /**
     * Request constructor.
     * @param Registry $registry
     * @param $uri
     * @param $controller_path
     */
    public function __construct(Registry $registry, $uri, $controller_path)
    {
        $this->server = $_SERVER;
        $this->post = $_POST;
        $parts = explode('/', $uri);
        $file = $controller_path;
        foreach ($parts as $part) {
            $file .= DS . $part;
            if(is_dir($file)) {
                array_shift($parts);
            }else if (is_file($file . '.php')) {
                array_shift($parts);
                array_shift($parts);
                break;
            }
        }
        $this->get = array_merge($_GET, $parts);

    }
}