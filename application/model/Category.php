<?php

namespace App\Model;

use App\Lib\Database;
use App\System\Model;

/**
 * @property Database Database
 * @property Language Language
 */
class Category extends Model {

    private $category_id;
    private $category_name;
    private $sort_order;
    private $language_id;
    private $top;
    private $level;
    private $status;
    private $date_added;
    private $date_updated;
    private $parent_id;
    private $category_filters = [];
    private $category_path = [];
    private $rows = [];


    public function insertCategory($data, $category_id = null) {
        if(!$category_id) {
            $this->Database->query("INSERT INTO category (parent_id, level, sort_order, status, date_added, date_updated) VALUES 
            (:cParentID,  :cLevel, :cSortOrder, :cStatus, :cDataAdded, :cDateUpdate)", array(
                'cParentID'=> $data['category_parent_id'],
                'cLevel'=> $data['level'],
                'cSortOrder'=> $data['sort_order'],
                'cStatus'=> 0,
                'cDataAdded'=> time(),
                'cDateUpdate'=> time()
            ));
            $category_id = $this->Database->insertId();
            $this->Database->query("SELECT * FROM category_path WHERE category_id = :cID ORDER BY level ASC", array(
                'cID'   => $data['category_parent_id']
            ));
            $rows = $this->Database->getRows();
            $level = 0;
            foreach ($rows as $row) {
                $this->Database->query("INSERT INTO category_path (category_id, path_id, level) VALUES (:cID, :cPID, :cLevel)", array(
                    'cID'   => $category_id,
                    'cPID'  => $row['path_id'],
                    'cLevel'=> $level
                ));
                $level++;
            }
            $this->Database->query("INSERT INTO category_path (category_id, path_id, level) VALUES (:cID, :cPID, :cLevel)", array(
                'cID'   => $category_id,
                'cPID'  => $category_id,
                'cLevel'=> $level
            ));
            foreach ($data['filters'] as $filte_id) {
                $this->Database->query("INSERT INTO category_filter (category_id, filter_id) VALUES (:cID, :fID)", array(
                    'cID'   => $category_id,
                    'fID'  => $filte_id,
                ));
            }
        }
        foreach ($data['category_names'] as $language_id => $category_name) {
            $this->Database->query("INSERT INTO category_language (category_id, language_id, name) VALUES (:cID, :lID, :cName) ", array(
                'cID'   => $category_id,
                'lID'   => $language_id,
                'cName' => $category_name
            ));
        }


        return $category_id;
    }

    public function getCategories($data = []) {
        $data['sort'] = isset($data['sort']) ? $data['sort'] : '';
        $data['order'] = isset($data['order']) ? strtoupper($data['order']) : 'ASC';
        $data['language_id'] = isset($data['language_id']) ? $data['language_id'] : $this->Language->getLanguageID();

        $sql = "SELECT * FROM category c JOIN category_language cl on c.category_id = cl.category_id 
        WHERE cl.language_id = :lID ";
        $sort_data = array(
            'name',
            'sort_order'
        );
        if(!empty($data['filter_name'])) {

            $sql .= " AND cl.name LIKE :fName ";
        }
        if(isset($data['parent_id'])) {

            $sql .= " AND c.parent_id = :cPID ";
        }

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        }else {
            $data['sort'] = '';
            $sql .= " ORDER BY c.category_id ";
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
        if(isset($data['filter_name'])) {
            $params['fName'] = $data['filter_name'] . '%';
        }
        if(isset($data['parent_id'])) {
            $params['cPID'] = $data['parent_id'];
        }
        $this->Database->query($sql,$params);
        $rows = $this->Database->getRows();
        return $rows;

    }

    public function getCategoryByID($category_id, $lID = null) {
        $language_id = $this->Language->getLanguageID();
        if($lID != null && $lID != "all") {
            $language_id = $lID;
        }
        if($lID != "all") {
            $this->Database->query("SELECT * FROM category c JOIN category_language cl on c.category_id = cl.category_id
            WHERE cl.language_id = :lID AND c.category_id = :cID", array(
                'lID'   => $language_id,
                'cID'   => $category_id
            ));
            $row = $this->Database->getRow();
            $this->category_id = $row['category_id'];
            $this->parent_id = $row['parent_id'];
            $this->top = $row['top'];
            $this->level = $row['level'];
            $this->sort_order = $row['sort_order'];
            $this->status = $row['status'];
            $this->date_added = $row['date_added'];
            $this->date_updated = $row['date_updated'];
            $this->language_id = $row['language_id'];
            $this->category_name = $row['name'];
            $this->rows = [];
            $this->Database->query("SELECT * FROM category_filter WHERE category_id = :cID ", array(
                'cID'   => $category_id
            ));
            $this->category_filters = $this->Database->getRows();
            $row['filters'] = $this->category_filters;
            $this->Database->query("SELECT * FROM category_path WHERE category_id = :cID ", array(
                'cID'   => $category_id
            ));
            $this->category_path = $this->Database->getRows();
            $row['paths'] = $this->category_path;
            $this->rows[0] = $row;
            return $row;
        }else {
            $this->Database->query("SELECT * FROM category c JOIN category_language cl on c.category_id = cl.category_id
            WHERE c.category_id = :cID", array(
                'cID'   => $category_id
            ));
            $rows = $this->Database->getRows();
            $this->category_id = $rows[0]['category_id'];
            $this->parent_id = $rows[0]['parent_id'];
            $this->top = $rows[0]['top'];
            $this->level = $rows[0]['level'];
            $this->sort_order = $rows[0]['sort_order'];
            $this->status = $rows[0]['status'];
            $this->date_added = $rows[0]['date_added'];
            $this->date_updated = $rows[0]['date_updated'];
            $this->rows = [];
            $this->Database->query("SELECT * FROM category_filter WHERE category_id = :cID ", array(
                'cID'   => $category_id
            ));
            $this->category_filters = $this->Database->getRows();
            $rows['filters'] = $this->category_filters;
            $this->Database->query("SELECT * FROM category_path WHERE category_id = :cID ", array(
                'cID'   => $category_id
            ));
            $this->category_path = $this->Database->getRows();
            $rows['paths'] = $this->category_path;
            $this->rows = $rows;
            return $rows;
        }
    }

    /**
     * @return array
     */
    public function getCategoryFilters(): array
    {
        $filters_id = [];
        foreach ($this->category_filters as $category_filter) {
            $filters_id[] = $category_filter['filter_id'];
        }
        if($filters_id) {
            $something = function () {
                return "?";
            };
            $place_holder = [];
            $place_holder_value  =[];
            $i = 0;
            foreach ($filters_id as $filter_id) {
                $place_holder[] = ":param" . $i;
                $place_holder_value["param" . $i] = $filter_id;
                $i++;
            }

            $params = $place_holder_value;
            $params['lID'] = $this->Language->getLanguageID();
            $this->Database->query("SELECT DISTINCT f.filter_group_id, fgl.name, fg.sort_order FROM filter f LEFT JOIN filter_group fg on f.filter_group_id = fg.filter_group_id
            LEFT JOIN filter_group_langauge fgl on fg.filter_group_id = fgl.filter_group_id WHERE f.filter_id IN (" . implode(',', $place_holder) . ") AND fgl.language_id = :lID GROUP BY f.filter_group_id ORDER BY fg.sort_order, fgl.name", $params);
            if(!$this->Database->hasRows()) {
                $params['lID'] = $this->Language->getDefaultLanguageID();
                $this->Database->query("SELECT DISTINCT f.filter_group_id, fgl.name, fg.sort_order FROM filter f LEFT JOIN filter_group fg on f.filter_group_id = fg.filter_group_id
            LEFT JOIN filter_group_langauge fgl on fg.filter_group_id = fgl.filter_group_id WHERE f.filter_id IN (" . implode(',', $place_holder) . ") AND fgl.language_id = :lID GROUP BY f.filter_group_id ORDER BY fg.sort_order, fgl.name", $params);
            }

            $filter_group_data = [];
            foreach ($this->Database->getRows() as $row) {
                $filter_data = [];
                $this->Database->query("SELECT *,COALESCE(fl.name, fl2.name) as name FROM filter f LEFT JOIN filter_language fl on f.filter_id = fl.filter_id AND fl.language_id = :lID
                LEFT JOIN filter_language fl2 ON f.filter_id = fl2.filter_id AND fl2.language_id = :lDID WHERE f.filter_group_id = :fGID ORDER BY f.sort_order, f.filter_id", array(
                    'fGID'  => $row['filter_group_id'],
                    'lID'   => $this->Language->getLanguageID(),
                    'lDID'  => $this->Language->getDefaultLanguageID()
                ));
                if($this->Database->hasRows()) {
                    $filter_data = $this->Database->getRows();
                }
                $filter_group_data[] = array(
                    'filter_group_id'   => $row['filter_group_id'],
                    'name'              => $row['name'],
                    'sort_order'        => $row['sort_order'],
                    'filter_items'      => $filter_data,
                );
            }
            return $filter_group_data;
        }
        return false;
    }

    public function getCategoriesComplete($data =  [])
    {
        $data['sort'] = isset($data['sort']) ? $data['sort'] : '';
        $data['order'] = isset($data['order']) ? strtoupper($data['order']) : 'ASC';
        $data['language_id'] = isset($data['language_id']) ? $data['language_id'] : $this->Language->getLanguageID();

        $sql = "SELECT c.category_id, cl2.name, GROUP_CONCAT(cl1.name ORDER BY cp.level SEPARATOR ',') as `full_name`,
        c.parent_id, c.sort_order, c.status, c.level, c.date_added, c.date_updated, c.top, cl2.language_id, cp.level  FROM category c JOIN category_path cp ON (c.category_id = cp.category_id) JOIN category_language cl1 
        ON cl1.category_id = cp.path_id JOIN category_language cl2 ON cl2.category_id = cp.category_id WHERE 
        cl1.language_id = :lID AND cl2.language_id = :lID ";
        $sort_data = array(
            'name',
            'sort_order'
        );
        if(!empty($data['filter_name'])) {

            $sql .= " AND cl2.name LIKE :fName ";
        }

        $sql .= " GROUP BY cp.category_id ";

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        }else {
            $data['sort'] = '';
            $sql .= " ORDER BY cp.category_id ";
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
        if(isset($data['filter_name'])) {
            $params['fName'] = $data['filter_name'] . '%';
        }
        $this->Database->query($sql,$params);
        $rows = $this->Database->getRows();
        return $rows;
    }

    public function getCategoryMenu($data) {
        $data['sort'] = isset($data['sort']) ? $data['sort'] : '';
        $data['order'] = isset($data['order']) ? strtoupper($data['order']) : 'ASC';
        $data['language_id'] = isset($data['language_id']) ? $data['language_id'] : $this->Language->getLanguageID();

        $sql = "SELECT *, c2.level as level FROM category_path cp LEFT JOIN category c2 on cp.category_id = c2.category_id LEFT  JOIN category_language cl on c2.category_id = cl.category_id
        WHERE cp.path_id = :cID AND cl.language_id = :lID AND c2.parent_id != 0";

        if(!empty($data['filter_name'])) {

            $sql .= " AND cl.name LIKE :fName ";
        }


        $sql .= " ORDER BY c2.sort_order, c2.level ";



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
            'cID'   => $data['path_id'],
        );
        if(isset($data['filter_name'])) {
            $params['fName'] = $data['filter_name'] . '%';
        }
        $this->Database->query($sql,$params);
        $rows = $this->Database->getRows();
        return $rows;
    }

    public function deleteCategory($category_id, $data = []) {
        if(isset($data['category_names']) && count($data['category_names']) > 0) {
            foreach ($data['category_names'] as $language_id => $category_name) {
                $this->Database->query("DELETE FROM category_language WHERE language_id = :lID AND category_id = :cID ", array(
                    'lID'   => $language_id,
                    'cID'   => $category_id
                ));
            }
        }else {
            $this->Database->query("SELECT * FROM category_path WHERE path_id = :cPID ", array(
               'cPID'    => $category_id
            ));
            $rows = $this->Database->getRows();
            foreach ($rows as $cId) {
                $this->Database->query("DELETE FROM category WHERE category_id = :cID ", array(
                    'cID'   => $cId['category_id']
                ));
            }
            $this->Database->query("DELETE FROM category WHERE category_id = :cID ", array(
                'cID'   => $category_id
            ));

        }

        return $this->Database->numRows();
    }

    public function editCategory($category_id, $data) {
        if(count($data) > 0) {
            $sql = "UPDATE category SET ";
            $params = [];
            $query = [];

            if(isset($data['sort_order'])) {
                $query[] = "sort_order = :cSortOrder ";
                $params['cSortOrder'] = $data['sort_order'];
            }
            if(isset($data['category_parent_id'])) {
                $query[] = "parent_id = :cPID ";
                $params['cPID'] = $data['category_parent_id'];
            }
            if(isset($data['top'])) {
                $query[] = "top = :cTop ";
                $params['cTop'] = $data['top'];
            }
            if(isset($data['level'])) {
                $query[] = "level = :cLevel ";
                $params['cLevel'] = $data['level'];
            }
            if(isset($data['status'])) {
                $query[] = "status = :cStatus ";
                $params['cStatus'] = $data['status'];
            }
            if(isset($data['date_updated'])) {
                $query[] = "date_updated = :cDateUpdated ";
                $params['cDateUpdated'] = $data['date_updated'];
            }


            $sql .= implode(" , ", $query);
            $sql .= " WHERE category_id = :cID ";
            $params['cID'] = $category_id;
            if(count($query) > 0) {
                $this->Database->query($sql, $params);
            }
            if(isset($data['category_names'])) {
                foreach ($data['category_names'] as $language_id => $category_name) {
                    $this->Database->query("UPDATE category_language SET name = :cName WHERE category_id = :cID AND language_id = :lID ", array(
                        'cID'   => $category_id,
                        'cName' => $category_name,
                        'lID'   => $language_id
                    ));
                }
            }
            if(isset($data['category_parent_id'])) {
                $this->Database->query("SELECT * FROM category_path WHERE path_id = :cPID ", array(
                    'cPID'  => $category_id
                ));
                $rows = $this->Database->getRows();
                if(count($rows) > 0) {
                    foreach ($rows as $row) {
                        $this->Database->query("DELETE FROM category_path WHERE category_id = :cID AND level < :cLevel ", array(
                            'cID' => $row['category_id'],
                            'cLevel'=> $row['level']
                        ));
                        $path = [];
                        $this->Database->query("SELECT * FROM category_path WHERE category_id = :cID ORDER BY level ASC", array(
                            'cID'   => $data['category_parent_id']
                        ));
                        $results = $this->Database->getRows();
                        foreach ($results as $result) {
                            $path[] = $result['path_id'];
                        }
                        $this->Database->query("SELECT * FROM category_path WHERE category_id = :cID ORDER BY level ASc", array(
                            'cID'   => $row['category_id']
                        ));
                        $results = $this->Database->getRows();
                        foreach ($results as $result) {
                            $path[] = $result['path_id'];
                        }
                        $level = 0;
                        foreach ($path as $path_id) {
                            $this->Database->query("REPLACE INTO category_path (category_id, path_id, level) VALUES (:cID, :cPID, :cLevel) ", array(
                                'cID'   => $row['category_id'],
                                'cPID'  => $path_id,
                                'cLevel'=> $level
                            ));
                            $level++;
                        }
                    }
                }else {
                    $this->Database->query("DELETE FROM category_path WHERE category_id = :cID ", array(
                        'cID'   => $category_id
                    ));
                    $this->Database->query("SELECT * FROM category_path WHERE category_id = :cID ORDER BY level ASC", array(
                        'cID'   => $data['category_parent_id']
                    ));
                    $rows = $this->Database->getRows();
                    $level = 0;
                    foreach ($rows as $row) {
                        $this->Database->query("INSERT INTO category_path (category_id, path_id, level) VALUES (:cID, :cPID, :cLevel)", array(
                            'cID'   => $category_id,
                            'cPID'  => $row['path_id'],
                            'cLevel'=> $level
                        ));
                        $level++;
                    }
                    $this->Database->query("REPLACE INTO category_path (category_id, path_id, level) VALUES (:cID, :cPID, :cLevel)", array(
                        'cID'   => $category_id,
                        'cPID'  => $category_id,
                        'cLevel'=> $level
                    ));
                }


            }


            if(isset($data['filters'])) {
                $this->Database->query("DELETE FROM category_filter WHERE category_id = :cID ", array(
                    'cID'   => $category_id
                ));
                foreach ($data['filters'] as $filte_id) {
                    $this->Database->query("INSERT INTO category_filter (category_id, filter_id) VALUES (:cID, :fID)", array(
                        'cID'   => $category_id,
                        'fID'  => $filte_id,
                    ));
                }
            }

            return $this->Database->numRows() > 0 ? true : false;
        }
        return false;

    }

    public function getCategoryInfoInPath($category_id, $lID = null) {
        $language_id = $this->Language->getLanguageID();
        if($lID) {
            $language_id = $lID;
        }
        $this->Database->query("SELECT * FROM category_path cp LEFT JOIN category c2 on cp.path_id = c2.category_id LEFT JOIN 
        category_language cl on c2.category_id = cl.category_id WHERE cp.category_id = :cID AND cl.language_id = :lID ORDER BY cp.level ASC", array(
            'cID'   => $category_id,
            'lID'   => $language_id
        ));
        $result = [];
        foreach ($this->Database->getRows() as $row) {
            $result[$row['category_id']] = $row;
        }
        return $result;
    }


}