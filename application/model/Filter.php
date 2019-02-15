<?php


namespace App\Model;


use App\Lib\Database;
use App\System\Model;

/**
 * @property Database Database
 * @property Language Language
 */
class Filter extends Model
{
    private $filter_group_id;
    private $filter_group_sort_order;
    private $language_id;
    private $filter_group_name;
    private $rows = [];
    private $filters = [];
    private $filter_id;
    private $filter_sort_order;
    private $filter_name;

    public function insertFilterGroup($data, $filter_group_id = null) {
        if(!$filter_group_id) {
            $this->Database->query("INSERT INTO filter_group (sort_order) VALUES (:fGSortOrder)", array(
                'fGSortOrder'=> $data['sort_order']
            ));
            $filter_group_id = $this->Database->insertId();
        }
        foreach ($data['filter_group_names'] as $language_id => $filter_group_name) {
            $this->Database->query("INSERT INTO filter_group_langauge (filter_group_id, language_id, name) VALUES (:fGID, :lID, :fGName) ", array(
                'fGID'   => $filter_group_id,
                'lID'   => $language_id,
                'fGName' => $filter_group_name
            ));
        }

        return $filter_group_id;
    }

    public function insertFilters($filter_group_id, $data) {
        foreach ($data as $sort_order => $item) {
            $this->Database->query("INSERT INTO filter (filter_group_id, sort_order) VALUES (:fGID, :fGSortOrder)", array(
                'fGID'  => $filter_group_id,
                'fGSortOrder'   => $sort_order
            ));
            $filter_id = $this->Database->insertId();
            foreach ($item as $language_id => $filter_name) {
                $this->Database->query("INSERT INTO filter_language (filter_id, language_id, name) VALUES (:fID, :lID, :fName)", array(
                    'fID'   => $filter_id,
                    'lID'   => $language_id,
                    'fName' => $filter_name
                ));

            }
        }
    }

    public function getFilterGroups($data = []) {
        $data['sort'] = isset($data['sort']) ? $data['sort'] : '';
        $data['order'] = isset($data['order']) ? strtoupper($data['order']) : 'ASC';
        $data['language_id'] = isset($data['language_id']) ? $data['language_id'] : $this->Language->getLanguageID();

        $sql = "SELECT * FROM filter_group fg JOIN filter_group_langauge fgl on fg.filter_group_id = fgl.filter_group_id WHERE
        fgl.language_id = :lID ";
        $sort_data = array(
            'name',
            'sort_order'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        }else {
            $data['sort'] = '';
            $sql .= " ORDER BY fg.filter_group_id";
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

    public function getFilterGroupByID($filter_group_id, $lID = null) {
        $language_id = $this->Language->getLanguageID();
        if($lID != null && $lID != "all") {
            $language_id = $lID;
        }
        if($lID != "all") {
            $this->Database->query("SELECT * FROM filter_group fg JOIN filter_group_langauge fgl on fg.filter_group_id = fgl.filter_group_id
            WHERE fgl.language_id = :lID AND fg.filter_group_id = :fGID", array(
                'lID'   => $language_id,
                'fGID'   => $filter_group_id
            ));
            $row = $this->Database->getRow();
            $this->filter_group_id = $row['filter_group_id'];
            $this->filter_group_sort_order = $row['sort_order'];
            $this->language_id = $row['language_id'];
            $this->filter_group_name = $row['name'];
            $this->rows = [];
            $this->rows[0] = $row;
            $this->Database->query("SELECT * FROM filter f JOIN filter_language fl on f.filter_id = fl.filter_id WHERE
            f.filter_group_id = :fGID AND fl.language_id = :lID", array(
                'fGID'  => $filter_group_id,
                'lID'   => $language_id
            ));
            $this->filters = $this->Database->getRows();
            $row['filters'] = $this->filters;
            return $row;
        }else {
            $this->Database->query("SELECT * FROM filter_group fg JOIN filter_group_langauge fgl on fg.filter_group_id = fgl.filter_group_id
            WHERE fg.filter_group_id = :fGID", array(
                'fGID'   => $filter_group_id
            ));
            $rows = $this->Database->getRows();
            $this->filter_group_id = $rows[0]['filter_group_id'];
            $this->filter_group_sort_order = $rows[0]['sort_order'];
            $this->rows = $rows;
            $this->Database->query("SELECT * FROM filter f JOIN filter_language fl on f.filter_id = fl.filter_id
            WHERE f.filter_group_id = :fGID", array(
                'fGID'  => $filter_group_id
            ));
            $this->filters = $this->Database->getRows();
            $this->rows['filters'] = $this->filters;
            return $rows;
        }
    }

    public function getFilterByID($filter_id, $lID = null) {
        $language_id = $this->Language->getLanguageID();
        if($lID != null && $lID != "all") {
            $language_id = $lID;
        }
        if($lID != "all") {
            $this->Database->query("SELECT * FROM filter f JOIN filter_language fl on f.filter_id = fl.filter_id
            WHERE fl.language_id = :lID AND f.filter_id = :fID", array(
                'lID'   => $language_id,
                'fID'   => $filter_id
            ));
            $row = $this->Database->getRow();
            $this->filter_id = $row['filter_id'];
            $this->filter_sort_order = $row['sort_order'];
            $this->language_id = $row['language_id'];
            $this->filter_name = $row['name'];
            $this->rows = [];
            $this->rows[0] = $row;
            return $row;
        }else {
            $this->Database->query("SELECT * FROM filter f JOIN filter_langauge fl on f.filter_id = fl.filter_id
            WHERE f.filter_id = :fID", array(
                'fID'   => $filter_id
            ));
            $rows = $this->Database->getRows();
            $this->filter_id = $rows[0]['filter_id'];
            $this->filter_sort_order = $rows[0]['sort_order'];
            $this->rows = $rows;
            return $rows;
        }
    }

    public function deleteFilterGroup($filter_group_id, $data = []) {
        if(isset($data['filter_group_names']) && count($data['filter_group_names']) > 0) {
            foreach ($data['filter_group_names'] as $language_id => $manufacturer_name) {
                $this->Database->query("DELETE FROM filter_group_langauge WHERE language_id = :lID AND filter_group_id = :fGID ", array(
                    'lID'   => $language_id,
                    'fGID'   => $filter_group_id
                ));
            }
        }else {
            $this->Database->query("DELETE FROM filter_group WHERE filter_group_id = :fGID ", array(
                'fGID'   => $filter_group_id
            ));
        }

        return $this->Database->numRows();
    }

    public function editFilterGroup($filter_group_id, $data) {
        if(count($data) > 0) {
            $sql = "UPDATE filter_group SET ";
            $params = [];
            $query = [];

            if(isset($data['sort_order'])) {
                $query[] = "sort_order = :fGSortOrder ";
                $params['fGSortOrder'] = $data['sort_order'];
            }

            $sql .= implode(" , ", $query);
            $sql .= " WHERE filter_group_id = :fGID ";
            $params['fGID'] = $filter_group_id;
            if(count($query) > 0) {
                $this->Database->query($sql, $params);
            }
            if(isset($data['filter_group_names'])) {
                foreach ($data['filter_group_names'] as $language_id => $filter_group_name) {
                    $this->Database->query("UPDATE filter_group_langauge SET name = :fGName WHERE filter_group_id = :fGID AND language_id = :lID ", array(
                        'fGID'   => $filter_group_id,
                        'fGName' => $filter_group_name,
                        'lID'   => $language_id
                    ));
                }
            }
            $this->Database->query("DELETE FROM filter WHERE filter_group_id = :fGID", array(
                'fGID'  => $filter_group_id
            ));
            foreach ($data['filters'] as $filter) {
                if(isset($filter['filter_id'])) {
                    $this->Database->query("INSERT INTO filter (filter_id, filter_group_id, sort_order) VALUES (:fID, :fGID, :fGSortOrder)", array(
                        'fID'  => $filter['filter_id'],
                        'fGID'  => $filter_group_id,
                        'fGSortOrder'   => $filter['sort_order']
                    ));
                }else {
                    $this->Database->query("INSERT INTO filter (filter_group_id, sort_order) VALUES (:fGID, :fGSortOrder)", array(
                        'fGID'  => $filter_group_id,
                        'fGSortOrder'   => $filter['sort_order']
                    ));
                }

                $filter_id = $this->Database->insertId();
                foreach ($filter['filter_language'] as $language_id => $filter_name) {
                    $this->Database->query("INSERT INTO filter_language (filter_id, language_id, name) VALUES (:fID, :lID, :fName)", array(
                        'fID'   => $filter_id,
                        'lID'   => $language_id,
                        'fName' => $filter_name
                    ));

                }
            }

            return $this->Database->numRows() > 0 ? true : false;
        }
        return false;
    }

    public function getFiltersSearch($data = []) {
        $data['sort'] = isset($data['sort']) ? $data['sort'] : '';
        $data['order'] = isset($data['order']) ? strtoupper($data['order']) : 'ASC';
        $data['language_id'] = isset($data['language_id']) ? $data['language_id'] : $this->Language->getLanguageID();

        $sql = "SELECT f.filter_id, f.filter_group_id, fl.name, fgl.name as `group`, fl.language_id, sort_order
        FROM filter f JOIN filter_language fl on f.filter_id = fl.filter_id JOIN filter_group_langauge fgl ON f.filter_group_id = fgl.filter_group_id
        WHERE fl.language_id = :lID AND fgl.language_id = :lID ";
        $sort_data = array(
            'name',
            'sort_order'
        );
        if(!empty($data['filter_name'])) {

            $sql .= " AND (fl.name LIKE :fName OR fgl.name LIKE :fName ) ";
        }

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        }else {
            $data['sort'] = '';
            $sql .= " ORDER BY f.filter_id";
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
            'fName' => $data['filter_name'] . '%'
        ));
        $rows = $this->Database->getRows();
        return $rows;
    }

    /**
     * @return mixed
     */
    public function getFilterGroupId()
    {
        return $this->filter_group_id;
    }

    /**
     * @return mixed
     */
    public function getFilterGroupSortOrder()
    {
        return $this->filter_group_sort_order;
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
    public function getFilterGroupName()
    {
        return $this->filter_group_name;
    }

    /**
     * @return array
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return $this->filters;
    }


}