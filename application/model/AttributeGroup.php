<?php


namespace App\Model;


use App\Lib\Database;
use App\System\Model;
use Twig\Cache\FilesystemCache;

/**
 * @property FilesystemCache Cache
 * @property Database Database
 * @property Language Language
 */
class AttributeGroup extends Model
{
    private const AttributeGroupsCacheName = "attributegroups";
    private const AttributeGroupsCacheTime = 3200;
    private $attribute_group_id;
    private $attribute_group_sort_order;
    private $language_id;
    private $attribute_group_name;
    private $rows = [];


    public function getAttributeGroups($data = array()) {
        $data['sort'] = isset($data['sort']) ? $data['sort'] : '';
        $data['order'] = isset($data['order']) ? strtoupper($data['order']) : 'ASC';
        $data['language_id'] = isset($data['language_id']) ? $data['language_id'] : $this->Language->getLanguageID();
        if(!$this->Cache->contains(self::AttributeGroupsCacheName . "-" . $data['language_id'] . "-" . $data['sort'] . '-' . $data['order']) && !isset($data['start']) && !isset($data['limit'])) {

            $sql = "SELECT * FROM attribute_group ag JOIN attribute_group_language agl on ag.attribute_group_id = agl.attribute_group_id
            WHERE agl.language_id = :lID ";
            $sort_data = array(
                'name',
                'sort_order'
            );

            if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
                $sql .= " ORDER BY " . $data['sort'];
            }else {
                $sql .= " ORDER BY ag.attribute_group_id";
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
                $this->Cache->save(self::AttributeGroupsCacheName . "-" . $data['language_id'] . "-" . $data['sort'] . '-' . $data['order'], $rows, self::AttributeGroupsCacheTime);
            }
        }else {
            $rows = $this->Cache->fetch(self::AttributeGroupsCacheName . "-" . $data['language_id'] . '-' . $data['sort'] . '-' . $data['order']);
        }
        return $rows;
    }
    
    public function insertAttributeGroup($data, $attribute_group_id = null) {
        if(!$attribute_group_id) {
            $this->Database->query("INSERT INTO attribute_group (sort_order) VALUES (:aGSortOrder)", array(
                'aGSortOrder'=> $data['sort_order']
            ));
            $attribute_group_id = $this->Database->insertId();
        }
        foreach ($data['attributegroup_names'] as $language_id => $attributegroup_name) {
            $this->Database->query("INSERT INTO attribute_group_language (attribute_group_id, language_id, name) VALUES (:aGID, :lID, :aGName) ", array(
                'aGID'   => $attribute_group_id,
                'lID'   => $language_id,
                'aGName' => $attributegroup_name
            ));
        }
        $sort_data = array(
            '',
            'name',
            'sort_order'
        );
        $languages = $this->Language->getLanguages();
        foreach ($languages as $language) {
            foreach ($sort_data as $sort) {
                if($this->Cache->contains(self::AttributeGroupsCacheName . "-" . $language['language_id'] . '-' . $sort . '-ASC')) {
                    $this->Cache->delete(self::AttributeGroupsCacheName . "-" . $language['language_id'] . '-' . $sort . '-ASC');
                }
                if($this->Cache->contains(self::AttributeGroupsCacheName . "-" . $language['language_id'] . '-' . $sort . '-DESC')) {
                    $this->Cache->delete(self::AttributeGroupsCacheName . "-" . $language['language_id'] . '-' . $sort . '-DESC');
                }
            }
        }
        return $attribute_group_id;
    }
    
    public function getAttributeGroupByID($attribute_group_id, $lID = null) {
        $language_id = $this->Language->getLanguageID();
        if($lID != null && $lID != "all") {
            $language_id = $lID;
        }
        if($lID != "all") {
            $this->Database->query("SELECT * FROM attribute_group ag JOIN attribute_group_language agl on ag.attribute_group_id = agl.attribute_group_id
            WHERE agl.language_id = :lID AND ag.attribute_group_id = :aGID ", array(
                'lID'   => $language_id,
                'aGID'   => $attribute_group_id
            ));
            $row = $this->Database->getRow();
            $this->attribute_group_id = $row['attribute_group_id'];
            $this->attribute_group_sort_order = $row['sort_order'];
            $this->language_id = $row['language_id'];
            $this->attribute_group_name = $row['name'];
            $this->rows = [];
            $this->rows[0] = $row;
            return $row;
        }else {
            $this->Database->query("SELECT * FROM attribute_group ag JOIN attribute_group_language agl on ag.attribute_group_id = agl.attribute_group_id
            WHERE ag.attribute_group_id = :aGID ", array(
                'aGID'   => $attribute_group_id
            ));
            $rows = $this->Database->getRows();
            $this->attribute_group_id = $rows[0]['attribute_group_id'];
            $this->attribute_group_sort_order = $rows[0]['sort_order'];
            $this->rows = $rows;
            return $rows;
        }
    }

    public function deleteAttributeGroup($attribute_group_id, $data = [])
    {
        if(isset($data['attributegroup_names']) && count($data['attributegroup_names']) > 0) {
            foreach ($data['attributegroup_names'] as $language_id => $attributegroup_name) {
                $this->Database->query("DELETE FROM attribute_group_language WHERE language_id = :lID AND attribute_group_id = :aGID ", array(
                    'lID'   => $language_id,
                    'aGID'   => $attribute_group_id
                ));
            }
        }else {
            $this->Database->query("DELETE FROM attribute_group WHERE attribute_group_id = :aGID ", array(
                'aGID'   => $attribute_group_id
            ));
        }
        $sort_data = array(
            '',
            'name',
            'sort_order'
        );
        $languages = $this->Language->getLanguages();
        foreach ($languages as $language) {
            foreach ($sort_data as $sort) {
                if($this->Cache->contains(self::AttributeGroupsCacheName . "-" . $language['language_id'] . '-' . $sort . '-ASC')) {
                    $this->Cache->delete(self::AttributeGroupsCacheName . "-" . $language['language_id'] . '-' . $sort . '-ASC');
                }
                if($this->Cache->contains(self::AttributeGroupsCacheName . "-" . $language['language_id'] . '-' . $sort . '-DESC')) {
                    $this->Cache->delete(self::AttributeGroupsCacheName . "-" . $language['language_id'] . '-' . $sort . '-DESC');
                }
            }
        }
        return $this->Database->numRows();
    }

    public function editAttributeGroup($attribute_group_id, $data) {
        if(count($data) > 0) {
            $sql = "UPDATE attribute_group SET ";
            $params = [];
            $query = [];
            if(isset($data['sort_order'])) {
                $query[] = "sort_order = :aGSortOrder ";
                $params['aGSortOrder'] = $data['sort_order'];
            }
            $sql .= implode(" , ", $query);
            $sql .= " WHERE attribute_group_id = :aGID ";
            $params['aGID'] = $attribute_group_id;
            if(count($query) > 0) {
                $this->Database->query($sql, $params);
            }
            if(isset($data['attributegroup_names'])) {
                foreach ($data['attributegroup_names'] as $language_id => $attributegroup_name) {
                    $this->Database->query("UPDATE attribute_group_language SET name = :aGName WHERE attribute_group_id = :aGID AND language_id = :lID ", array(
                        'aGID'   => $attribute_group_id,
                        'aGName' => $attributegroup_name,
                        'lID'   => $language_id
                    ));
                }
            }
            if($this->Database->numRows() > 0) {
                $sort_data = array(
                    '',
                    'name',
                    'sort_order'
                );
                $languages = $this->Language->getLanguages();
                foreach ($languages as $language) {
                    foreach ($sort_data as $sort) {
                        if($this->Cache->contains(self::AttributeGroupsCacheName . "-" . $language['language_id'] . '-' . $sort . '-ASC')) {
                            $this->Cache->delete(self::AttributeGroupsCacheName . "-" . $language['language_id'] . '-' . $sort . '-ASC');
                        }
                        if($this->Cache->contains(self::AttributeGroupsCacheName . "-" . $language['language_id'] . '-' . $sort . '-DESC')) {
                            $this->Cache->delete(self::AttributeGroupsCacheName . "-" . $language['language_id'] . '-' . $sort . '-DESC');
                        }
                    }
                }
            }
            return $this->Database->numRows() > 0 ? true : false;
        }
        return false;
    }
}