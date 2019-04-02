<?php


namespace App\Model;


use App\Lib\Database;
use App\System\Model;

/**
 * @property Database Database
 * @property Language Language
 */
class Length extends Model
{
    private $length_id;
    private $value;
    private $unit;
    private $language_id;
    private $name;
    private $rows = [];

    public function getLengths($data = []) {
        $data['language_id'] = isset($data['language_id']) ? $data['language_id'] : $this->Language->getLanguageID();
        $sql = "SELECT * FROM length l LEFT JOIN length_language ll ON l.length_id = ll.length_id WHERE
            ll.language_id = :lID ";
        $sort_data = array(
            'name',
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        }else {
            $data['sort'] = '';
            $sql .= " ORDER BY l.length_id";
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

    public function getLength($length_id, $lID = null) {
        $language_id = $this->Language->getLanguageID();
        if($lID != null && $lID != "all") {
            $language_id = $lID;
        }
        if($lID != "all") {
            $this->Database->query("SELECT * FROM length w JOIN length_language wl on w.length_id = wl.length_id 
            WHERE wl.language_id = :lID AND w.length_id = :wID ", array(
                'lID'   => $language_id,
                'wID'   => $length_id
            ));
            $row = $this->Database->getRow();
            if($row) {
                $this->length_id = $row['length_id'];
                $this->value = $row['value'];
                $this->unit = $row['unit'];
                $this->language_id = $row['language_id'];
                $this->name = $row['name'];
                return $row;
            }else {
                return false;
            }
        }else {
            $this->Database->query("SELECT * FROM length w JOIN length_language wl on w.length_id = wl.length_id 
            WHERE w.length_id = :wID ", array(
                'wID'   => $length_id
            ));
            $rows = $this->Database->getRows();
            if(count($rows) > 0) {

                $this->length_id = $rows[0]['length_id'];
                $this->value = $rows[0]['value'];
                $this->rows = $rows;
            }
            return $rows;
        }
    }

}