<?php

namespace App\System;

use App\Lib\Registry;

class Model {
    private $registry;

    /**
     * User constructor.
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    public function __get($name)
    {
        return $this->registry->{$name};
    }


}