<?php


namespace App\Model;


use App\Lib\Database;
use App\System\Model;
use Doctrine\Common\Cache\FilesystemCache;

/**
 * @property  FilesystemCache Cache
 * @property Database Database
 * @property Language Language
 */
class Attribute extends Model
{
    private const AttributeCacheName =  "attributes";
    private const AttributeCacheTime =  3200;
    private $attribute_group_id;
    private $language_id;
    private $attribute_sort_order;
    private $name;
    private $attributegroup_name;
    private $attribute_id;
    private $rows =[];

    public function insertAttribute($data, $attribute_id = null) {
        if(!$attribute_id) {
            $this->Database->query("INSERT INTO attribute (attribute_group_id, sort_order) VALUES (:aGID, :aSortOrder)", array(
                'aGID'=> $data['attributegroup_id'],
                'aSortOrder'=> $data['sort_order']
            ));
            $attribute_id = $this->Database->insertId();
        }
        foreach ($data['attribute_names'] as $language_id => $attribute_name) {
            $this->Database->query("INSERT INTO attribute_language (attribute_id, language_id, name) VALUES (:aID, :lID, :aName) ", array(
                'aID'   => $attribute_id,
                'lID'   => $language_id,
                'aName' => $attribute_name
            ));
        }
        $sort_data = array(
            '',
            'attributegroup_name',
            'al.name',
            'a.sort_order'
        );
        $languages = $this->Language->getLanguages();
        foreach ($languages as $language) {
            foreach ($sort_data as $sort) {
                if($this->Cache->contains(self::AttributeCacheName . "-" . $language['language_id'] . '-' . $sort . '-ASC')) {
                    $this->Cache->delete(self::AttributeCacheName . "-" . $language['language_id'] . '-' . $sort . '-ASC');
                }
                if($this->Cache->contains(self::AttributeCacheName . "-" . $language['language_id'] . '-' . $sort . '-DESC')) {
                    $this->Cache->delete(self::AttributeCacheName . "-" . $language['language_id'] . '-' . $sort . '-DESC');
                }
            }
        }
        return $attribute_id;
    }

    public function getAttributes($data = array()) {
        $data['sort'] = isset($data['sort']) ? $data['sort'] : '';
        $data['order'] = isset($data['order']) ? strtoupper($data['order']) : 'ASC';
        $data['language_id'] = isset($data['language_id']) ? $data['language_id'] : $this->Language->getLanguageID();
        if(!$this->Cache->contains(self::AttributeCacheName . "-" . $data['language_id'] . "-" . $data['sort'] . '-' . $data['order']) && !isset($data['start']) && !isset($data['limit'])) {

            $sql = "SELECT *, (SELECT agl.name FROM attribute_group_language agl WHERE agl.attribute_group_id = a.attribute_group_id AND agl.language_id = al.language_id) AS attributegroup_name 
            FROM attribute a JOIN  attribute_language al on a.attribute_id = al.attribute_id WHERE 
            al.language_id = :lID ";
            $sort_data = array(
                'attributegroup_name',
                'al.name',
                'a.sort_order'
            );

            if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
                $sql .= " ORDER BY " . $data['sort'];
            }else {
                $data['sort'] = '';
                $sql .= " ORDER BY a.attribute_id";
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
            if(!isset($data['start']) && !isset($data['limit'])) {
                $this->Cache->save(self::AttributeCacheName . "-" . $data['language_id'] . "-" . $data['sort'] . '-' . $data['order'], $rows, self::AttributeCacheTime);
            }
        }else {
            $rows = $this->Cache->fetch(self::AttributeCacheName . "-" . $data['language_id'] . '-' . $data['sort'] . '-' . $data['order']);
        }
        return $rows;
    }

    public function getAttributeByID($attribute_id, $lID = null) {
        $language_id = $this->Language->getLanguageID();
        if($lID != null && $lID != "all") {
            $language_id = $lID;
        }
        if($lID != "all") {
            $this->Database->query("SELECT *, (SELECT agl.name FROM attribute_group_language agl WHERE agl.attribute_group_id = a.attribute_group_id AND agl.language_id = al.language_id) AS attributegroup_name 
            FROM attribute a JOIN  attribute_language al on a.attribute_id = al.attribute_id WHERE 
            al.language_id = :lID AND a.attribute_id = :aID ", array(
                'lID'   => $language_id,
                'aID'   => $attribute_id
            ));
            $row = $this->Database->getRow();
            $this->attribute_id = $row['attribute_id'];
            $this->attribute_group_id = $row['attribute_group_id'];
            $this->attributegroup_name = $row['attributegroup_name'];
            $this->attribute_sort_order = $row['sort_order'];
            $this->language_id = $row['language_id'];
            $this->name = $row['name'];
            $this->rows = [];
            $this->rows[0] = $row;
            return $row;
        }else {
            $this->Database->query("SELECT *, (SELECT agl.name FROM attribute_group_language agl WHERE agl.attribute_group_id = a.attribute_group_id AND agl.language_id = al.language_id) AS attributegroup_name 
            FROM attribute a JOIN  attribute_language al on a.attribute_id = al.attribute_id WHERE 
            a.attribute_id = :aID ", array(
                'aID'   => $attribute_id
            ));
            $rows = $this->Database->getRows();
            $this->attribute_group_id = $rows[0]['attribute_group_id'];
            $this->attribute_id = $rows[0]['attribute_id'];
            $this->attribute_sort_order = $rows[0]['sort_order'];
            $this->rows = $rows;
            return $rows;
        }
    }

    public function deleteAttribute($attribute_id, $data = []) {
        if(isset($data['attribute_names']) && count($data['attribute_names']) > 0) {
            foreach ($data['attribute_names'] as $language_id => $attribute_name) {
                $this->Database->query("DELETE FROM attribute_language WHERE language_id = :lID AND attribute_id = :aID ", array(
                    'lID'   => $language_id,
                    'aID'   => $attribute_id
                ));
            }
        }else {
            $this->Database->query("DELETE FROM attribute WHERE attribute_id = :aID ", array(
                'aID'   => $attribute_id
            ));
        }
        $sort_data = array(
            '',
            'attributegroup_name',
            'al.name',
            'a.sort_order'
        );
        $languages = $this->Language->getLanguages();
        foreach ($languages as $language) {
            foreach ($sort_data as $sort) {
                if($this->Cache->contains(self::AttributeCacheName . "-" . $language['language_id'] . '-' . $sort . '-ASC')) {
                    $this->Cache->delete(self::AttributeCacheName . "-" . $language['language_id'] . '-' . $sort . '-ASC');
                }
                if($this->Cache->contains(self::AttributeCacheName . "-" . $language['language_id'] . '-' . $sort . '-DESC')) {
                    $this->Cache->delete(self::AttributeCacheName . "-" . $language['language_id'] . '-' . $sort . '-DESC');
                }
            }
        }
        return $this->Database->numRows();
    }

    public function editAttribute($attribute_id, $data) {
        if(count($data) > 0) {
            $sql = "UPDATE attribute SET ";
            $params = [];
            $query = [];
            if(isset($data['sort_order'])) {
                $query[] = "sort_order = :aSortOrder ";
                $params['aSortOrder'] = $data['sort_order'];
            }
            if(isset($data['attributegroup_id'])) {
                $query[] = "attribute_group_id = :aGSortOrder ";
                $params['aGSortOrder'] = $data['attributegroup_id'];
            }
            $sql .= implode(" , ", $query);
            $sql .= " WHERE attribute_id = :aID ";
            $params['aID'] = $attribute_id;
            if(count($query) > 0) {
                $this->Database->query($sql, $params);
            }
            if(isset($data['attribute_names'])) {
                foreach ($data['attribute_names'] as $language_id => $attribute_name) {
                    $this->Database->query("UPDATE attribute_language SET name = :aName WHERE attribute_id = :aID AND language_id = :lID ", array(
                        'aID'   => $attribute_id,
                        'aName' => $attribute_name,
                        'lID'   => $language_id
                    ));
                }
            }
            if($this->Database->numRows() > 0) {
                $sort_data = array(
                    '',
                    'attributegroup_name',
                    'al.name',
                    'a.sort_order'
                );
                $languages = $this->Language->getLanguages();
                foreach ($languages as $language) {
                    foreach ($sort_data as $sort) {
                        if($this->Cache->contains(self::AttributeCacheName . "-" . $language['language_id'] . '-' . $sort . '-ASC')) {
                            $this->Cache->delete(self::AttributeCacheName . "-" . $language['language_id'] . '-' . $sort . '-ASC');
                        }
                        if($this->Cache->contains(self::AttributeCacheName . "-" . $language['language_id'] . '-' . $sort . '-DESC')) {
                            $this->Cache->delete(self::AttributeCacheName . "-" . $language['language_id'] . '-' . $sort . '-DESC');
                        }
                    }
                }
            }
            return $this->Database->numRows() > 0 ? true : false;
        }
        return false;
    }
}