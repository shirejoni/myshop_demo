<?php
namespace App\Web\Controller;

use App\Lib\Response;
use App\Model\Amazing;
use App\model\Product;
use App\System\Controller;

/**
 * @property Response Response
 */
class ControllerHome extends Controller {
    public function index() {
        $data = [];
        /** @var Product $Product */
        $Product = $this->load('Product', $this->registry);
        $data['BestSellerProducts'] = $Product->getBestSellersProduct();
        $Image = $this->load("Image", $this->registry);
        foreach ($data['BestSellerProducts'] as $index => $product) {
            if (is_file(ASSETS_PATH . DS . substr($product['image'], strlen(ASSETS_URL)))) {
                $image = ASSETS_URL . $Image->resize(substr($product['image'], strlen(ASSETS_URL)), 400, 400);
            } else {
                $image = ASSETS_URL . $Image->resize('img/no-image.jpeg', 300, 200);
            }
            $data['BestSellerProducts'][$index]['image'] = $image;
        }
        $data['NewestProducts'] = $Product->getNewestProduct();
        foreach ($data['NewestProducts'] as $index => $product) {
            if (is_file(ASSETS_PATH . DS . substr($product['image'], strlen(ASSETS_URL)))) {
                $image = ASSETS_URL . $Image->resize(substr($product['image'], strlen(ASSETS_URL)), 400, 400);
            } else {
                $image = ASSETS_URL . $Image->resize('img/no-image.jpeg', 300, 200);
            }
            $data['NewestProducts'][$index]['image'] = $image;
        }
        /** @var Amazing $Amazing */
        $Amazing = $this->load('Amazing', $this->registry);
        $amazings_id = $Amazing->getEnabledAmazingsID();
        $data['Amazings'] = [];
        require_once LIB_PATH . DS . 'jdate/jdf.php';
        foreach ($amazings_id as $amzing_id) {
            $amazing = $Amazing->getAmazing($amzing_id);

            $randomProducts = array_rand($amazing['products_id'], 3);
            $products_id = [];
            foreach ($randomProducts as $key) {
                $products_id[] = $amazing['products_id'][$key];
            }
            $products = $Product->getProductsComplete(['products_id' => $products_id]);
            foreach ($products as $index => $product) {
                if (is_file(ASSETS_PATH . DS . substr($product['image'], strlen(ASSETS_URL)))) {
                    $image = ASSETS_URL . $Image->resize(substr($product['image'], strlen(ASSETS_URL)), 400, 400);
                } else {
                    $image = ASSETS_URL . $Image->resize('img/no-image.jpeg', 300, 200);
                }
                $products[$index]['image'] = $image;
            }
            $amazing['products'] = $products;
            $dateEnd = date('Y m d H i s',$amazing['date_end']);
            list($amazing['year'], $amazing['month'], $amazing['day'], $amazing['hour'], $amazing['minute'], $amazing['second']) = explode(' ', $dateEnd);
            $data['Amazings'][] = $amazing;
        }
        $this->Response->setOutPut($this->render('index', $data));
    }
}