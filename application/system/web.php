<?php


/** @var \App\Lib\Router $Router */
$Router->get('product/(\d+)', 'product/index', 'web', true);

$Router->all('user/.*', 'init/startup/init', 'web', false);
$Router->all('user/.*', 'init/login/index', 'web', false);