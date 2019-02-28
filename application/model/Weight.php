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

}