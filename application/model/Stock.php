<?php


namespace App\Model;


use App\Lib\Database;
use App\System\Model;

/**
 * @property Database Database
 */
class Stock extends Model
{
    private $stock_status_id;
    private $language_id;
    private $name;
    private $rows = [];

    public function getStocks($data = []) {
        $data['language_id'] = isset($data['language_id']) ? $data['language_id'] : $this->Language->getLanguageID();
        $sql = "SELECT * FROM stock_status s WHERE
            s.language_id = :lID ";
        $sort_data = array(
            'name',
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        }else {
            $data['sort'] = '';
            $sql .= " ORDER BY s.stock_status_id";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }
        $this->Database->query($sql, array(
            'lID'   => $data['language_id'],
        ));
        $rows = $this->Database->getRows();
        return $rows;
    }

    public function getStock($stock_id, $lID = null) {
        $language_id = $this->Language->getLanguageID();
        if($lID != null && $lID != "all") {
            $language_id = $lID;
        }
        if($lID != "all") {
            $this->Database->query("SELECT * FROM stock_status
            WHERE language_id = :lID AND stock_status_id = :sID ", array(
                'lID'   => $language_id,
                'sID'   => $stock_id
            ));
            $row = $this->Database->getRow();
            if($row) {
                $this->stock_status_id = $row['stock_status_id'];
                $this->language_id = $row['language_id'];
                $this->name = $row['name'];
                return $row;
            }else {
                return false;
            }
        }else {
            $this->Database->query("SELECT * FROM stock_status
            WHERE  stock_status_id = :sID ", array(
                'aGID'   => $stock_id
            ));
            $rows = $this->Database->getRows();
            if(count($rows) > 0) {
                $this->stock_status_id = $rows[0]['stock_status_id'];
                $this->rows = $rows;
            }
            return $rows;
        }
    }

}