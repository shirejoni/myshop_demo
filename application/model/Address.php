<?php


namespace App\model;


use App\Lib\Database;
use App\System\Model;

/**
 * @property Database Database
 */
class Address extends Model
{
    public function getProvinces() {
        $this->Database->query("SELECT * FROM province ORDER BY name ASC");
        return $this->Database->getRows();
    }

    public function getProvinceCities($province_id) {
        $this->Database->query("SELECT * FROM city WHERE province_id = :pID ORDER BY name ASC", array(
            'pID'   => $province_id
        ));
        if($this->Database->hasRows()) {
            return $this->Database->getRows();
        }
        return false;
    }

    public function getProvince($province_id) {
        $this->Database->query("SELECT * FROM province WHERE id = :pID", array(
            'pID'   => $province_id
        ));
        if($this->Database->hasRows()) {
            return $this->Database->getRow();
        }
        return false;
    }

    public function getCity($city_id) {
        $this->Database->query("SELECT * FROM city WHERE id = :cID", array(
            'cID'   => $city_id
        ));
        if($this->Database->hasRows()) {
            return $this->Database->getRow();
        }
        return false;
    }

    public function insertAddress($data) {
        $this->Database->query("INSERT INTO address (customer_id, first_name, last_name, city, province_id, address, zip_code) VALUES 
        (:cID, :aFName, :aLName, :aCity, :aPID, :aAddress, :aZipCode)", array(
            'cID'   => $data['customer_id'],
            'aFName'=> $data['first_name'],
            'aLName'=> $data['last_name'],
            'aCity' => $data['city_id'],
            'aPID'  => $data['province_id'],
            'aAddress'  => $data['address'],
            'aZipCode'  => $data['zip_code']
        ));
        return $this->Database->insertId();
    }
}