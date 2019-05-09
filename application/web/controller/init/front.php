<?php

namespace App\Web\Controller;

use App\Model\Category;
use App\System\Controller;

class ControllerInitFront extends Controller {
    public function category() {
        /** @var Category $Category */
        $Category = $this->load("Category", $this->registry);
        $topCategories = $Category->getCategories(array(
            'parent_id' => 0,
        ));
        foreach ($topCategories as $index => $topCategory) {
            $subCategories = $Category->getCategoryMenu(array(
                'path_id'   => $topCategory['category_id'],
                'sort'      => 'c2.sort_order'
            ));
            $topCategories[$index]['subCategories'] = $subCategories;
        }
        return array(
            'TopCategories' => $topCategories,
        );
    }

}