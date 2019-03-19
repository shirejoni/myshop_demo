<?php


namespace App\Model;


use App\Lib\Database;
use App\System\Model;

/**
 * @property Database Database
 * @property Language Language
 */
class Option extends Model
{
    private $option_id;
    private $option_sort_order;
    private $language_id;
    private $option_name;
    private $rows = [];
    private $optionValues;

    public function getOptionGroups($data = []) {
        $data['sort'] = isset($data['sort']) ? $data['sort'] : '';
        $data['order'] = isset($data['order']) ? strtoupper($data['order']) : 'ASC';
        $data['language_id'] = isset($data['language_id']) ? $data['language_id'] : $this->Language->getLanguageID();

        $sql = "SELECT * FROM `option` o JOIN `option_language` ol on o.option_id = ol.option_id WHERE
        ol.language_id = :lID ";
        $sort_data = array(
            'name',
            'sort_order'
        );

        if(!empty($data['filter_name'])) {
            $sql .= " AND ol.name LIKE :fName ";
        }

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        }else {
            $data['sort'] = '';
            $sql .= " ORDER BY o.option_id";
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
        $params =  array(
            'lID'   => $data['language_id'],
        );
        if(!empty($data['filter_name'])) {
            $params['fName'] = $data['filter_name'] . '%';
        }

        $this->Database->query($sql, $params);
        $rows = $this->Database->getRows();


        return $rows;
    }

    public function getOptionByID($option_id, $lID = null) {
        $language_id = $this->Language->getLanguageID();
        if($lID != null && $lID != "all") {
            $language_id = $lID;
        }
        if($lID != "all") {
            $this->Database->query("SELECT * FROM `option` o JOIN option_language ol on o.option_id = ol.option_id
            WHERE ol.language_id = :lID AND o.option_id = :oID", array(
                'lID'   => $language_id,
                'oID'   => $option_id
            ));
            $row = $this->Database->getRow();
            $this->option_id = $row['option_id'];
            $this->option_sort_order = $row['sort_order'];
            $this->language_id = $row['language_id'];
            $this->option_name = $row['name'];
            $this->rows = [];
            $this->rows[0] = $row;
            $this->Database->query("SELECT * FROM option_value ov JOIN option_value_language ovl on ov.option_value_id = ovl.option_value_id WHERE
            ov.option_id = :oID AND ovl.language_id = :lID", array(
                'oID'  => $option_id,
                'lID'   => $language_id
            ));
            $this->optionValues = $this->Database->getRows();
            $row['options'] = $this->optionValues;
            return $row;
        }else {
            $this->Database->query("SELECT * FROM `option` o JOIN option_language ol on o.option_id = ol.option_id
            WHERE o.option_id = :oID", array(
                'oID'   => $option_id
            ));
            $rows = $this->Database->getRows();
            $this->option_id = $rows[0]['option_id'];
            $this->option_sort_order = $rows[0]['sort_order'];
            $this->rows = $rows;
            $this->Database->query("SELECT * FROM option_value ov JOIN option_value_language ovl on ov.option_value_id = ovl.option_value_id WHERE
            ov.option_id = :oID", array(
                'oID'  => $option_id
            ));
            $this->optionValues = $this->Database->getRows();
            $this->rows['options'] = $this->optionValues;
            return $rows;
        }
    }

    public function insertOptionGroup($data, $option_id = null) {
        if(!$option_id) {
            $this->Database->query("INSERT INTO `option` (option_type, sort_order) VALUES (:oType,:oSortOrder)", array(
                'oSortOrder'=> $data['sort_order'],
                'oType'=> $data['type']
            ));
            $option_id = $this->Database->insertId();
        }
        foreach ($data['option_group_names'] as $language_id => $option_name) {
            $this->Database->query("INSERT INTO option_language (option_id, language_id, name) VALUES (:oID, :lID, :oName) ", array(
                'oID'   => $option_id,
                'lID'   => $language_id,
                'oName' => $option_name
            ));
        }

        return $option_id;
    }

    public function insertOptionValues($option_id, $data) {
        foreach ($data['options'] as $sort_order => $item) {
            $this->Database->query("INSERT INTO option_value (option_id, image, sort_order) VALUES (:oID, :oImage, :oSortOrder)", array(
                'oID'  => $option_id,
                'oImage'    => $data['option_image'][$sort_order],
                'oSortOrder'   => $sort_order
            ));
            $option_value_id = $this->Database->insertId();
            foreach ($item as $language_id => $filter_name) {
                $this->Database->query("INSERT INTO option_value_language (option_value_id, language_id, name) VALUES (:oVID, :lID, :oVName)", array(
                    'oVID'   => $option_value_id,
                    'lID'   => $language_id,
                    'oVName' => $filter_name
                ));

            }
        }
    }

    public function deleteOption($option_id, $data = []) {
        if(isset($data['option_group_names']) && count($data['option_group_names']) > 0) {
            foreach ($data['option_group_names'] as $language_id => $option_name) {
                $this->Database->query("DELETE FROM option_language WHERE language_id = :lID AND option_id = :oID ", array(
                    'lID'   => $language_id,
                    'oID'   => $option_id
                ));
            }
        }else {
            $this->Database->query("DELETE FROM `option` WHERE option_id = :oID ", array(
                'oID'   => $option_id
            ));
        }

        return $this->Database->numRows();
    }

    public function editOption($option_id, $data) {
        if(count($data) > 0) {
            $sql = "UPDATE `option` SET ";
            $params = [];
            $query = [];

            if(isset($data['sort_order'])) {
                $query[] = "sort_order = :oSortOrder ";
                $params['oSortOrder'] = $data['sort_order'];
            }

            $sql .= implode(" , ", $query);
            $sql .= " WHERE option_id = :oID ";
            $params['oID'] = $option_id;
            if(count($query) > 0) {
                $this->Database->query($sql, $params);
            }
            if(isset($data['option_group_names'])) {
                foreach ($data['option_group_names'] as $language_id => $option_group_name) {
                    $this->Database->query("UPDATE option_language SET name = :oName WHERE option_id = :oID AND language_id = :lID ", array(
                        'oID'   => $option_id,
                        'oName' => $option_group_name,
                        'lID'   => $language_id
                    ));
                }
            }
            $this->Database->query("DELETE FROM option_value WHERE option_id = :oID", array(
                'oID'  => $option_id
            ));
            foreach ($data['options'] as $sort_order => $item) {
                $this->Database->query("INSERT INTO option_value (option_id, image, sort_order) VALUES (:oID, :oImage, :oSortOrder)", array(
                    'oID'  => $option_id,
                    'oImage'    => $data['option_image'][$sort_order],
                    'oSortOrder'   => $sort_order
                ));
                $option_value_id = $this->Database->insertId();
                foreach ($item as $language_id => $filter_name) {
                    $this->Database->query("INSERT INTO option_value_language (option_value_id, language_id, name) VALUES (:oVID, :lID, :oVName)", array(
                        'oVID'   => $option_value_id,
                        'lID'   => $language_id,
                        'oVName' => $filter_name
                    ));

                }
            }

            return $this->Database->numRows() > 0 ? true : false;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getOptionId()
    {
        return $this->option_id;
    }

    /**
     * @return mixed
     */
    public function getOptionSortOrder()
    {
        return $this->option_sort_order;
    }

    /**
     * @return mixed
     */
    public function getLanguageId()
    {
        return $this->language_id;
    }

    /**
     * @return mixed
     */
    public function getOptionName()
    {
        return $this->option_name;
    }

    /**
     * @return array
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    /**
     * @return mixed
     */
    public function getOptionValues()
    {
        return $this->optionValues;
    }


}