<?php


namespace App\Lib;


class Config
{
    private $data = array();

    /**
     *
     *
     * @param	string	$key
     *
     * @return	mixed
     */
    public function get($key) {
        return (isset($this->data[$key]) ? $this->data[$key] : null);
    }

    /**
     *
     *
     * @param	string	$key
     * @param	string	$value
     */
    public function set($key, $value) {
        $this->data[$key] = $value;
    }

    /**
     *
     *
     * @param	string	$key
     *
     * @return	mixed
     */
    public function has($key) {
        return isset($this->data[$key]);
    }

    /**
     *
     *
     * @param    string $filename
     * @throws \Exception
     */
    public function load($filename) {
        $file = CONFIG_PATH . DS . $filename . '.php';
        if (file_exists($file)) {
            $_ = array();

            require($file);

            $this->data = array_merge($this->data, $_);
        } else {
            throw new \Exception('Error: Could not load config ' . $filename . '!');
        }
    }
}