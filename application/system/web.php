<?php


/** @var \App\Lib\Router $Router */

$Router->all('.*', 'init/startup/init', 'web', false);
$Router->all('user/.*', 'init/login/index', 'web', false);
$Router->all('checkout/cart', 'init/login/index', 'web', false);
$Router->all('checkout/checkout/address', 'init/login/index', 'web', false);
$Router->all('checkout/checkout/applyAddress', 'init/login/index', 'web', false);
$Router->all('checkout/checkout/payment', 'init/login/index', 'web', false);
$Router->all('checkout/checkout/pay/(\d+)', 'init/login/index', 'web', false);
$Router->all('.*', 'init/startup/customer', 'web', false);
$Router->get('.*', 'init/front/category', 'web', false);

$Router->all('checkout/index', 'checkout/checkout/index', 'web', true);
$Router->all('checkout/cart', 'checkout/checkout/cart', 'web', true);
$Router->all('checkout/checkout/pay/(\d+)', 'checkout/checkout/pay', 'web', true);
$Router->get('product/(\d+)', 'product/index', 'web', true);
$Router->get('category/(\d+)', 'category/showList', 'web', true);
