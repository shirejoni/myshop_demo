<?php

namespace App\Admin\Controller;

use App\Lib\Action;
use App\Lib\Request;
use App\Lib\Response;
use App\model\Address;
use App\model\Customer;
use App\Model\Language;
use App\Model\Order;
use App\System\Controller;
use function GuzzleHttp\Promise\is_settled;

/**
 * @property Request Request
 * @property Response Response
 * @property Language Language
 */
class ControllerOrderOrder extends Controller {

    public function index(): void
    {
        $data = [];
        /** @var Order $Order */
        $Order = $this->load('Order', $this->registry);
        $data['Orders'] = $Order->getOrders();
        require_once LIB_PATH . DS . 'jdate' . DS . 'jdf.php';
        foreach ($data['Orders'] as $index => $order) {
            $data['Orders'][$index]['date_updated'] = jdate('Y/m/d H:i:s', $order['date_updated']);
            $data['Orders'][$index]['date_added'] = jdate('Y/m/d H:i:s', $order['date_added']);
        }
        $Language = $this->Language;
        $data['Languages'] = $Language->getLanguages();
        $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
        $this->Response->setOutPut($this->render('order/index', $data));
    }

    public function edit()
    {
        $data = array();
        $error = false;
        $messages = [];
        if(isset($this->Request->get[0])) {
            $order_id = $this->Request->get[0];
            /** @var Order $Order */
            $Order = $this->load('Order', $this->registry);
            $order = $Order->getOrder((int) $order_id);
            if($order_id && (int) $order_id && $order['order_status_id'] >= 2) {
                $customer_id = $order['customer_id'];
                /** @var Customer $Customer */
                $Customer = $this->load('Customer', $this->registry);
                $customer = $Customer->getCustomerByID($customer_id);
                $data['Customer'] = $customer;
                require_once LIB_PATH . DS . 'jdate' . DS . 'jdf.php';
                $order['date_added_formatted'] = jdate('Y/m/d H:i:s', $order['date_added']);
                /** @var Address $Address */
                $Address = $this->load('Address', $this->registry);
                $province = $Address->getProvince($order['payment_province_id']);
                $order['province_name'] = $province['name'];
                $city = $Address->getCity($order['payment_city_id']);
                $order['city_name'] = $city['name'];
                $products = $Order->getOrderProducts($order['order_id']);
                foreach ($products as $index => $product) {
                    $products[$index]['price_formatted'] = number_format($product['price']);
                    $products[$index]['total_formatted'] = number_format($product['total']);
                }
                $order['total_formatted'] = number_format($order['total']);
                $order['off'] = $Order->getOrderTotal($order_id);
                $order['order_histories'] = $Order->getOrderHistories($order_id);
                foreach ($order['order_histories'] as $index => $order_history) {
                    $order['order_histories'][$index]['date_added_formatted'] =  jdate('Y/m/d H:i:s', $order_history['date_added']);
                }
                $order['products'] = $products;
                $data['Order'] = $order;
                $data['OrderStatuses'] = $Order->getOrderStatuses();
                $this->Response->setOutPut($this->render('order/edit', $data));
                return;
            }
        }
        return new Action('error/notFound', 'web');
    }

    public function addorderhistory()
    {
        if(isset($this->Request->post['order_id'], $this->Request->post['order_status_id'])) {
            /** @var Order $Order */
            $Order = $this->load('Order', $this->registry);
            $order = $Order->getOrder($this->Request->post['order_id']);
            $orderStatuses = $Order->getOrderStatuses();
            if($order && in_array_r($this->Request->post['order_status_id'], $orderStatuses, false)) {
                $Order->insertOrderHistory([
                    'order_id'  => $order['order_id'],
                    'order_status_id'  => $this->Request->post['order_status_id'],
                    'date_added'        => time()
                ]);
                $json = [];
                $json['status'] = 1;
                $json['messages'] = [$this->Language->get('message_success_done')];
                $json['redirect'] = ADMIN_URL . 'order/order/index';
                $this->Response->setOutPut(json_encode($json));
                return;
            }
        }
        return new Action('error/notFound', 'web');
    }

}