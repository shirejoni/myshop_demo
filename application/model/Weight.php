<?php


namespace App\Model;


use App\Lib\Database;
use App\System\Model;

/**
 * @property Database Database
 * @property Language Language
 */
class Weight extends Model
{
    private $weight_id;
    private $value;
    private $language_id;
    private $name;
    private $unit;
    private $rows = [];

    public function getWeights($data = []) {
        $data['language_id'] = isset($data['language_id']) ? $data['language_id'] : $this->Language->getLanguageID();
        $sql = "SELECT * FROM weight w LEFT JOIN weight_language wl ON w.weight_id = wl.weight_id WHERE
            wl.language_id = :lID ";
        $sort_data = array(
            'name',
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        }else {
            $data['sort'] = '';
            $sql .= " ORDER BY w.weight_id";
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

    public function getWeight($weight_id, $lID = null) {
        $language_id = $this->Language->getLanguageID();
        if($lID != null && $lID != "all") {
            $language_id = $lID;
        }
        if($lID != "all") {
            $this->Database->query("SELECT * FROM weight w JOIN weight_language wl on w.weight_id = wl.weight_id 
            WHERE wl.language_id = :lID AND w.weight_id = :wID ", array(
                'lID'   => $language_id,
                'wID'   => $weight_id
            ));
            $row = $this->Database->getRow();
            if($row) {
                $this->weight_id = $row['weight_id'];
                $this->value = $row['value'];
                $this->unit = $row['unit'];
                $this->language_id = $row['language_id'];
                $this->name = $row['name'];
                return $row;
            }else {
                return false;
            }
        }else {
            $this->Database->query("SELECT * FROM weight w JOIN weight_language wl on w.weight_id = wl.weight_id 
            WHERE w.weight_id = :wID ", array(
                'wID'   => $weight_id
            ));
            $rows = $this->Database->getRows();
            if(count($rows) > 0) {
                $this->weight_id = $rows[0]['weight_id'];
                $this->value = $rows[0]['value'];
                $this->rows = $rows;

            }
            return $rows;
        }
    }

}