<?php

namespace App\Model;

use App\Lib\Database;
use App\System\Model;

/**
 * @property Database Database
 */
class Amazing extends Model {

    public function insertAmazing(array $data)
    {
        $this->Database->query('INSERT INTO amazing ( name, discount, type, date_start, date_end, status, date_added) VALUES 
        (:aName, :aDiscount, :aType, :aDStart, :aDEnd, :aStatus, :aDAdded)', array(
            'aName' => $data['name'],
            'aDiscount' => $data['discount'],
            'aType' => $data['type'],
            'aDStart' => $data['date_start'],
            'aDEnd' => $data['date_end'],
            'aStatus'       => $data['status'],
            'aDAdded'         => time()
        ));
        $amazing_id = $this->Database->insertId();
        if(isset($data['products_id'])) {
            foreach ($data['products_id'] as $product_id) {
                $this->Database->query("INSERT INTO amazing_product (amazing_id, product_id) VALUES (:aID, :pID)", array(
                    'aID'   => $amazing_id,
                    'pID'   => $product_id
                ));
            }
        }

        return $amazing_id;
    }

    public function getAmazings($data = array())
    {
        $data['sort'] = $data['sort'] ?? '';
        $data['order'] = isset($data['order']) ? strtoupper($data['order']) : 'ASC';

        $sql = 'SELECT * FROM amazing a';
        $sort_data = array(
            'name',
            'date_start',
            'date_end',
            'quantity'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= ' ORDER BY ' . $data['sort'];
        }else {
            $data['sort'] = '';
            $sql .= ' ORDER BY a.amazing_id';
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= ' DESC';
        } else {
            $sql .= ' ASC';
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= ' LIMIT ' . (int)$data['start'] . ',' . (int)$data['limit'];
        }
        $this->Database->query($sql);
        $rows = $this->Database->getRows();


        return $rows;
    }

    public function getAmazing(int $amazing_id)
    {
        $this->Database->query('SELECT * FROM amazing WHERE amazing_id = :aID', array(
            'aID'   => $amazing_id
        ));
        $row = $this->Database->getRow();
        $this->Database->query("SELECT product_id FROM amazing_product WHERE amazing_id = :aID", array(
            'aID'   => $amazing_id
        ));
        $row['products_id'] = [];
        foreach ($this->Database->getRows() as $r) {
            $row['products_id'][] = $r['product_id'];
        }
        $this->rows = [];
        $this->rows[0] = $row;
        return $row;
    }

    public function deleteAmazing($amazing_id) {
        $this->Database->query('DELETE FROM amazing WHERE amazing_id = :aID', array(
            'aID'   => $amazing_id
        ));
        return $this->Database->numRows();
    }

    public function editAmazing(int $amazing_id, array $data)
    {
        if(count($data) > 0) {
            $sql = "UPDATE amazing SET ";
            $params = [];
            $query = [];
            if(isset($data['name'])) {
                $query[] = "name = :cName ";
                $params['cName'] = $data['name'];
            }
            if(isset($data['discount'])) {
                $query[] = "discount = :cDiscount ";
                $params['cDiscount'] = $data['discount'];
            }
            if(isset($data['type'])) {
                $query[] = "type = :cType ";
                $params['cType'] = $data['type'];
            }
            if(isset($data['date_start'])) {
                $query[] = "date_start = :cDStart ";
                $params['cDStart'] = $data['date_start'];
            }
            if(isset($data['date_end'])) {
                $query[] = "date_end = :cDEnd ";
                $params['cDEnd'] = $data['date_end'];
            }
            if(isset($data['status'])) {
                $query[] = "status = :cStatus ";
                $params['cStatus'] = $data['status'];
            }
            $sql .= implode(" , ", $query);
            $sql .= " WHERE amazing_id = :cID ";
            $params['cID'] = $amazing_id;
            if(count($query) > 0) {
                $this->Database->query($sql, $params);
            }
            if(isset($data['products_id'])) {
                $this->Database->query("DELETE FROM amazing_product WHERE amazing_id = :cID", array(
                    'cID'   => $amazing_id
                ));
                foreach ($data['products_id'] as $product_id) {
                    $this->Database->query("INSERT INTO amazing_product (amazing_id, product_id) VALUES (:cID, :pID)", array(
                        'cID'   => $amazing_id,
                        'pID'   => $product_id
                    ));
                }
            }

            return $this->Database->numRows() > 0 ? true : false;
        }
        return false;
    }

    public function getEnabledAmazingsID()
    {
        $this->Database->query('SELECT amazing_id FROM amazing WHERE status = 1 AND date_start < :aTime AND date_end > :aTime ORDER BY date_end ASC', array(
            'aTime' => time()
        ));
        $rows = $this->Database->getRows();
        $amazings_id = [];
        foreach ($rows as $row) {
            $amazings_id[] = $row['amazing_id'];
        }
        return $amazings_id;
    }
}