<?php


namespace App\Web\Controller;

use App\Lib\Action;
use App\Lib\Request;
use App\Lib\Response;
use App\Model\Language;
use App\model\Product;
use App\Model\Review;
use App\System\Controller;

/**
 * @property Request Request
 * @property Language Language
 * @property Response Response
 * */
class ControllerReview extends Controller {

    public function add() {
        $data = [];
        $messages = [];
        $error = false;
        if(isset($this->Request->post['comment-post'])) {
            /** @var Product $Product */
            $Product = $this->load("Product", $this->registry);
            $product_id = isset($this->Request->post['product-id']) && (int) $this->Request->post['product-id'] ? $this->Request->post['product-id'] : 0;
            if($product_id && $Product->getProduct($product_id)) {
                $data['product_id'] = $product_id;
            }else {
                $error = true;
                $messages[] = $this->Language->get('error_done');
            }
            if(!empty($this->Request->post['comment-name'])) {
                $data['author'] = $this->Request->post['comment-name'];
            }
            if(!empty($this->Request->post['comment-description'])) {
                $data['text'] = $this->Request->post['comment-description'];
            }else {
                $error = true;
                $messages[] = $this->Language->get('error_product_comment_description_empty');
            }
            if(isset($this->Request->post['comment-rating'])) {
                $rating = (int) $this->Request->post['comment-rating'];
                $data['rate'] = $rating <= 5 && $rating >= 0 ? $rating : 0;
            }else {
                $data['rate'] = 0;
            }

            // TODO : User Author and Customer id
            $data['customer_id'] = 0;
            $data['status'] = 0;
            $data['date_added'] = time();
            $data['date_updated'] = time();
            if(empty($data['author'])) {
                $error = true;
                $messages[] = $this->Language->get('error_product_comment_author_empty');
            }
            $json = [];
            if(!$error) {
                /** @var Review $Review */
                $Review = $this->load("Review", $this->registry);
                $Review->insertReview($data);
                $json['status'] = 1;
                $json['messages'] = [$this->Language->get('message_success_done')];
                $data['Product']['reviews'] = $Product->getReviews($data['product_id']);
                $json['data'] = $this->render('product/product_reviews', $data);
            }

            if($error) {
                $json['status'] = 0;
                $json['messages'] = $messages;
            }
            $this->Response->setOutPut(json_encode($json));
            return;
        }
        return new Action('error/notFound', 'web');
    }

}