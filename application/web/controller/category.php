<?php

namespace App\Web\Controller;

use App\model\Product;
use App\System\Controller;

class ControllerCategory extends Controller {

    public function showList() {
        var_dump($this->data['params']);
        /** @var Product $Product */
        $Product = $this->load("Product", $this->registry);
        var_dump($Product->getProductsComplete(array(
            'category_id'   => '6',
            'filters_id'    => ['2']
        )));
    }

}