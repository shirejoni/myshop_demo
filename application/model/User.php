<?php

namespace App\Model;

use App\Lib\Database;
use App\System\Model;

/**
 * @property  Database Database
 */
class User extends Model {
    private $user_id;
    private $email;
    private $first_name;
    private $last_name;
    private $user_group_id;
    private $image;
    private $code;
    private $ip;
    private $status;



    public function getUserByEmail($email) {
        $this->Database->query("SELECT * FROM users WHERE email = :email", array(
            'email' => $email
        ));
        $result = $this->Database->getRow();
        $this->user_id = $result['user_id'];
        $this->user_group_id = $result['user_group_id'];
        $this->first_name = $result['first_name'];
        $this->last_name = $result['last_name'];
        $this->email = $result['email'];
        $this->status = $result['status'];
        $this->image = $result['image'];
        $this->code = $result['code'];
        $this->ip = $result['ip'];
        return $result;
    }

    public function login($option = array())
    {
        if(!empty($this->email) && !empty($this->user_id)) {
            if(isset($option['ip']) && $option['ip'] != $this->ip) {
                if($this->edit($this->user_id, ['ip' => $option['ip']])) {
                    $this->ip = $option['ip'];
                }
            }
            $_SESSION['user'] = [];
            session_regenerate_id();
            $_SESSION['user'] = array(
                'user_id'   => $this->user_id,
                'email'     => $this->email,
                'status'        => $this->status,
            );

        }else {
            throw new \Exception("User->Login() : You Should First Get User info from Database after that login it");
        }

    }
    public function edit($user_id, $data) {
        $sql = "UPDATE users SET ";
        $params = [];
        if(isset($data['email'])) {
            $sql .= "email = :uEmail ";
            $params['uEmail'] = $data['email'];
        }
        if(isset($data['first_name'])) {
            $sql .= "first_name = :uFirstName ";
            $params['uFirstName'] = $data['first_name'];
        }
        if(isset($data['last_name'])) {
            $sql .= "last_name = :uLastName ";
            $params['uLastName'] = $data['last_name'];
        }
        if(isset($data['code'])) {
            $sql .= "code = :uCode ";
            $params['uCode'] = $data['code'];
        }
        if(isset($data['image'])) {
            $sql .= "image = :uImage ";
            $params['uImage'] = $data['image'];
        }
        if(isset($data['status'])) {
            $sql .= "status = :uStatus ";
            $params['uStatus'] = $data['status'];
        }
        if(isset($data['user_group_id'])) {
            $sql .= "user_group_id = :uUserGroupID ";
            $params['uUserGroupID'] = $data['user_group_id'];
        }
        if(isset($data['ip'])) {
            $sql .= "ip = :uIP ";
            $params['uIP'] = $data['ip'];
        }
        $sql .= " WHERE user_id = :uUserID ";
        $params['uUserID'] = $user_id;
        $this->Database->query($sql, $params);
        if($this->Database->numRows() > 0) {
            return $this->Database->insertId();
        }else {
            return false;
        }
    }
}