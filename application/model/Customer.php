<?php


namespace App\model;


use App\Lib\Database;
use App\System\Model;

/**
 * @property Database Database
 */
class Customer extends Model
{
    private $customer_id;
    private $first_name;
    private $last_name;
    private $email;
    private $mobile;
    private $cart;
    private $wishlist;
    private $newsletter;
    private $address_id;
    private $status;
    private $token;
    private $code;
    private $date_added;
    private $language_id;

    public function getCustomerByEmail($email) {
        $this->Database->query("SELECT * FROM customer WHERE email = :email", array(
            'email' => $email
        ));
        if($this->Database->hasRows()) {
            $result = $this->Database->getRow();
            $this->customer_id = $result['customer_id'];
            $this->first_name = $result['first_name'];
            $this->last_name = $result['last_name'];
            $this->language_id = $result['language_id'];
            $this->email = $result['email'];
            $this->mobile = $result['mobile'];
            $this->cart = json_decode($result['cart']);
            $this->wishlist = json_decode($result['wishlist']);
            $this->newsletter = $result['newsletter'];
            $this->status = $result['status'];
            $this->token = $result['token'];
            $this->code = $result['code'];
            $this->address_id = $result['address_id'];
            return $result;
        }
        return false;
    }

    public function getCustomerByMobile($mobile) {
        $this->Database->query("SELECT * FROM customer WHERE mobile = :mobile", array(
            'mobile' => $mobile
        ));
        if($this->Database->hasRows()) {
            $result = $this->Database->getRow();
            $this->customer_id = $result['customer_id'];
            $this->first_name = $result['first_name'];
            $this->last_name = $result['last_name'];
            $this->language_id = $result['language_id'];
            $this->email = $result['email'];
            $this->mobile = $result['mobile'];
            $this->cart = json_decode($result['cart']);
            $this->wishlist = json_decode($result['wishlist']);
            $this->newsletter = $result['newsletter'];
            $this->status = $result['status'];
            $this->token = $result['token'];
            $this->code = $result['code'];
            $this->address_id = $result['address_id'];
            return $result;
        }
        return false;
    }

    public function getCustomerByID($customer_id) {
        $this->Database->query("SELECT * FROM customer WHERE customer_id = :customer_id", array(
            'customer_id' => $customer_id
        ));
        if($this->Database->hasRows()) {
            $result = $this->Database->getRow();
            $this->customer_id = $result['customer_id'];
            $this->first_name = $result['first_name'];
            $this->last_name = $result['last_name'];
            $this->language_id = $result['language_id'];
            $this->email = $result['email'];
            $this->mobile = $result['mobile'];
            $this->cart = json_decode($result['cart']);
            $this->wishlist = json_decode($result['wishlist']);
            $this->newsletter = $result['newsletter'];
            $this->status = $result['status'];
            $this->token = $result['token'];
            $this->code = $result['code'];
            $this->address_id = $result['address_id'];
            return $result;
        }
        return false;
    }

    public function insertCustomer($data) {
        $this->Database->query("INSERT INTO customer (language_id, first_name, last_name, email, mobile, password, cart, wishlist, newsletter, address_id, status, date_added) VALUES 
        (:lID, :cFName, :cLName, :cEmail, :cMobile, :cPassword, :cCart, :cWishlist, :cNews, :cAID, :cStatus, :cDateAdded)", array(
            'lID'   => $data['language_id'],
            'cFName'    => $data['first_name'],
            'cLName'    => $data['last_name'],
            'cEmail'    => $data['email'],
            'cMobile'   => $data['mobile'],
            'cPassword' => password_hash($data['password'], PASSWORD_DEFAULT),
            'cCart'     => json_encode([]),
            'cWishlist' => json_encode([]),
            'cNews'     => 1,
            'cAID'      => 0,
            'cStatus'   => 1,
            'cDateAdded'=> time()
        ));
        return $this->Database->insertId();
    }

    public function login($option = array())
    {
        if(!empty($this->email) && !empty($this->customer_id)) {
            $_SESSION['customer'] = [];
            $_old_session = session_id();
            session_regenerate_id();
            $_SESSION['old_session_id'] = $_old_session;
            $_SESSION['customer'] = array(
                'customer_id'   => $this->customer_id,
                'email'     => $this->email,
                'status'        => $this->status,
            );

        }else {
            throw new \Exception("Customer->Login() : You Should First Get User info from Database after that login it");
        }

    }

    public function edit($customer_id, $data) {
        $sql = "UPDATE customer SET ";
        $params = [];
        $query = [];

        if(isset($data['language_id'])) {
            $query[] = "language_id = :language_id ";
            $params['language_id'] = $data['language_id'];
        }
        if(isset($data['first_name'])) {
            $query[] = "first_name = :cFName ";
            $params['cFName'] = $data['first_name'];
        }
        if(isset($data['last_name'])) {
            $query[] = "last_name = :cLName ";
            $params['cLName'] = $data['last_name'];
        }
        if(isset($data['mobile'])) {
            $query[] = "mobile = :cMobile ";
            $params['cMobile'] = $data['mobile'];
        }
        if(isset($data['password'])) {
            $query[] = "password = :cPassword ";
            $params['cPassword'] = $data['password'];
        }
        if(isset($data['cart'])) {
            $query[] = "cart = :cCart ";
            $params['cCart'] = json_encode($data['cart']);
        }
        if(isset($data['wishlist'])) {
            $query[] = "wishlist = :cWishlist ";
            $params['cWishlist'] = json_encode($data['wishlist']);
        }
        if(isset($data['newsletter'])) {
            $query[] = "newsletter = :cNews ";
            $params['cNews'] = $data['newsletter'];
        }
        if(isset($data['address_id'])) {
            $query[] = "address_id = :cAID ";
            $params['cAID'] = $data['address_id'];
        }
        if(isset($data['status'])) {
            $query[] = "status = :cStatus ";
            $params['cStatus'] = $data['status'];
        }
        if(isset($data['token'])) {
            $query[] = "token = :cToken ";
            $params['cToken'] = $data['token'];
        }
        if(isset($data['code'])) {
            $query[] = "code = :cCode ";
            $params['cCode'] = $data['code'];
        }
        $sql .= implode(" , ", $query);
        $sql .= " WHERE customer_id = :cID ";
        $params['cID'] = $customer_id;
        if(count($query) > 0) {
            $this->Database->query($sql, $params);
        }
    }

    public function getCustomerFavorite($customer_id) {
        $this->Database->query("SELECT * FROM customer_favorite cf WHERE cf.customer_id = :cID", array(
            'cID'   => $customer_id
        ));
        $products_id = [];

        foreach ($this->Database->getRows() as $row) {
            $products_id[] = $row['product_id'];
        }
        return $products_id;
    }

    public function deleteCustomerFavorite($customer_id, $product_id) {
        $this->Database->query("DELETE FROM customer_favorite WHERE customer_id = :cID AND product_id = :pID", array(
            'cID'   => $customer_id,
            'pID'   => $product_id
        ));
        return $this->Database->numRows();
    }
    public function insertCustomerFavorite($customer_id, $product_id) {
        $this->Database->query("INSERT INTO customer_favorite (customer_id, product_id) VALUES (:cID, :pID)", array(
            'cID'   => $customer_id,
            'pID'   => $product_id
        ));
        return $this->Database->insertId();
    }

    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->customer_id;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return mixed
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @return mixed
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @return mixed
     */
    public function getWishlist()
    {
        return $this->wishlist;
    }

    /**
     * @return mixed
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }

    /**
     * @return mixed
     */
    public function getAddressId()
    {
        return $this->address_id;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return mixed
     */
    public function getDateAdded()
    {
        return $this->date_added;
    }

    /**
     * @return mixed
     */
    public function getLanguageId()
    {
        return $this->language_id;
    }


}