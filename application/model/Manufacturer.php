<?php

namespace App\Model;

use App\Lib\Database;
use App\System\Model;
use Doctrine\Common\Cache\FilesystemCache;

/**
 * @property Database Database
 * @property FilesystemCache Cache
 * @property Language Language
 */
class Manufacturer extends Model {

    private $manufacturer_id;
    private $language_id;
    private $manufacturer_name;
    private $rows = [];
    private $manufacturer_image;
    private $manufacturer_status;
    private $manufacturer_sort_order;
    private $manufacturer_url;

    public function insertManufacturer($data, $manufacturer_id = null) {
        if(!$manufacturer_id) {
            $this->Database->query("INSERT INTO `manufacturer` (image, sort_order, url) VALUES (:mImage, :mSortOrder, :mURL)", array(
                'mImage'    => $data['image'],
                'mURL'      => $data['url'],
                'mSortOrder'=> $data['sort_order']
            ));
            $manufacturer_id = $this->Database->insertId();
        }
        foreach ($data['manufacturer_names'] as $language_id => $manufacturerName) {
            $this->Database->query("INSERT INTO manufacturer_language (manufacturer_id, language_id, name) VALUES (:mID, :lID, :mName) ", array(
                'mID'   => $manufacturer_id,
                'lID'   => $language_id,
                'mName' => $manufacturerName
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
                if($this->Cache->contains(self::ManufacturersCacheName . "-" . $language['language_id'] . '-' . $sort . '-ASC')) {
                    $this->Cache->delete(self::ManufacturersCacheName . "-" . $language['language_id'] . '-' . $sort . '-ASC');
                }
                if($this->Cache->contains(self::ManufacturersCacheName . "-" . $language['language_id'] . '-' . $sort . '-DESC')) {
                    $this->Cache->delete(self::ManufacturersCacheName . "-" . $language['language_id'] . '-' . $sort . '-DESC');
                }
            }
        }
        return $manufacturer_id;
    }

    public function getManufacturers($data = array()) {
        $data['sort'] = isset($data['sort']) ? $data['sort'] : '';
        $data['order'] = isset($data['order']) ? strtoupper($data['order']) : 'ASC';
        $data['language_id'] = isset($data['language_id']) ? $data['language_id'] : $this->Language->getLanguageID();

            $sql = "SELECT * FROM `manufacturer` m LEFT JOIN manufacturer_language ml ON m.manufacturer_id = ml.manufacturer_id WHERE
            ml.language_id = :lID ";
            $sort_data = array(
                'name',
                'sort_order'
            );

            if(!empty($data['filter_name'])) {
                $sql .= " AND ml.name LIKE :fName ";
            }

            if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
                $sql .= " ORDER BY " . $data['sort'];
            }else {
                $data['sort'] = '';
                $sql .= " ORDER BY m.manufacturer_id";
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

    public function getManufacturerByID($manufacturer_id, $lID = null) {
        $language_id = $this->Language->getLanguageID();
        if($lID != null && $lID != "all") {
            $language_id = $lID;
        }
        if($lID != "all") {
            $this->Database->query("SELECT * FROM manufacturer m JOIN manufacturer_language ml on m.manufacturer_id = ml.manufacturer_id
            WHERE ml.language_id = :lID AND m.manufacturer_id = :mID ", array(
                'lID'   => $language_id,
                'mID'   => $manufacturer_id
            ));
            $row = $this->Database->getRow();
            $this->manufacturer_id = $row['manufacturer_id'];
            $this->manufacturer_image = $row['image'];
            $this->manufacturer_url = $row['url'];
            $this->manufacturer_status = $row['status'];
            $this->manufacturer_sort_order = $row['sort_order'];
            $this->language_id = $row['language_id'];
            $this->manufacturer_name = $row['name'];
            $this->rows = [];
            $this->rows[0] = $row;
            return $row;
        }else {
            $this->Database->query("SELECT * FROM manufacturer m JOIN manufacturer_language ml on m.manufacturer_id = ml.manufacturer_id
            WHERE m.manufacturer_id = :mID ", array(
                'mID'   => $manufacturer_id
            ));
            $rows = $this->Database->getRows();
            $this->manufacturer_id = $rows[0]['manufacturer_id'];
            $this->manufacturer_image = $rows[0]['image'];
            $this->manufacturer_url = $rows[0]['url'];
            $this->manufacturer_status = $rows[0]['status'];
            $this->manufacturer_sort_order = $rows[0]['sort_order'];
            $this->rows = $rows;
            return $rows;
        }
    }

    public function getManufacturerByUrl($url, $lID = null) {
        $language_id = $this->Language->getLanguageID();
        if($lID != null && $lID != "all") {
            $language_id = $lID;
        }
        if($lID != "all") {
            $this->Database->query("SELECT * FROM manufacturer m JOIN manufacturer_language ml on m.manufacturer_id = ml.manufacturer_id
            WHERE ml.language_id = :lID AND m.url = :mURL ", array(
                'lID'   => $language_id,
                'mURL'   => $url
            ));
            $row = $this->Database->getRow();
            $this->manufacturer_id = $row['manufacturer_id'];
            $this->manufacturer_image = $row['image'];
            $this->manufacturer_url = $row['url'];
            $this->manufacturer_status = $row['status'];
            $this->manufacturer_sort_order = $row['sort_order'];
            $this->language_id = $row['language_id'];
            $this->manufacturer_name = $row['name'];
            $this->rows = [];
            $this->rows[0] = $row;
            return $row;
        }else {
            $this->Database->query("SELECT * FROM manufacturer m JOIN manufacturer_language ml on m.manufacturer_id = ml.manufacturer_id
            WHERE m.url = :mURL ", array(
                'mURL'   => $url
            ));
            $rows = $this->Database->getRows();
            $this->manufacturer_id = $rows[0]['manufacturer_id'];
            $this->manufacturer_image = $rows[0]['image'];
            $this->manufacturer_url = $rows[0]['url'];
            $this->manufacturer_status = $rows[0]['status'];
            $this->manufacturer_sort_order = $rows[0]['sort_order'];
            $this->rows = $rows;
            return $rows;
        }
    }

    public function editManufacturer($manufacturer_id, $data) {
        if(count($data) > 0) {
            $sql = "UPDATE manufacturer SET ";
            $params = [];
            $query = [];
            if(isset($data['image'])) {
                $query[] = "image = :mImage ";
                $params['mImage'] = $data['image'];
            }
            if(isset($data['sort_order'])) {
                $query[] = "sort_order = :mSortOrder ";
                $params['mSortOrder'] = $data['sort_order'];
            }
            if(isset($data['status'])) {
                $query[] = "status = :mStatus ";
                $params['mStatus'] = $data['status'];
            }
            if(isset($data['url'])) {
                $query[] = "url = :mUrl ";
                $params['mUrl'] = $data['url'];
            }
            $sql .= implode(" , ", $query);
            $sql .= " WHERE manufacturer_id = :mID ";
            $params['mID'] = $manufacturer_id;
            if(count($query) > 0) {
                $this->Database->query($sql, $params);
            }
            if(isset($data['manufacturer_names'])) {
                foreach ($data['manufacturer_names'] as $language_id => $manufacturer_name) {
                    $this->Database->query("UPDATE manufacturer_language SET name = :mName WHERE manufacturer_id = :mID AND language_id = :lID ", array(
                        'mID'   => $manufacturer_id,
                        'mName' => $manufacturer_name,
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
                        if($this->Cache->contains(self::ManufacturersCacheName . "-" . $language['language_id'] . '-' . $sort . '-ASC')) {
                            $this->Cache->delete(self::ManufacturersCacheName . "-" . $language['language_id'] . '-' . $sort . '-ASC');
                        }
                        if($this->Cache->contains(self::ManufacturersCacheName . "-" . $language['language_id'] . '-' . $sort . '-DESC')) {
                            $this->Cache->delete(self::ManufacturersCacheName . "-" . $language['language_id'] . '-' . $sort . '-DESC');
                        }
                    }
                }
            }
            return $this->Database->numRows() > 0 ? true : false;
        }
        return false;
    }

    public function deleteManufacturer($manufacturer_id, $data = array()) {
        if(isset($data['manufacturer_names']) && count($data['manufacturer_names']) > 0) {
            foreach ($data['manufacturer_names'] as $language_id => $manufacturer_name) {
                $this->Database->query("DELETE FROM manufacturer_language WHERE language_id = :lID AND manufacturer_id = :mID ", array(
                    'lID'   => $language_id,
                    'mID'   => $manufacturer_id
                ));
            }
        }else {
            $this->Database->query("DELETE FROM manufacturer WHERE manufacturer_id = :mID ", array(
                'mID'   => $manufacturer_id
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
                if($this->Cache->contains(self::ManufacturersCacheName . "-" . $language['language_id'] . '-' . $sort . '-ASC')) {
                    $this->Cache->delete(self::ManufacturersCacheName . "-" . $language['language_id'] . '-' . $sort . '-ASC');
                }
                if($this->Cache->contains(self::ManufacturersCacheName . "-" . $language['language_id'] . '-' . $sort . '-DESC')) {
                    $this->Cache->delete(self::ManufacturersCacheName . "-" . $language['language_id'] . '-' . $sort . '-DESC');
                }
            }
        }
        return $this->Database->numRows();
    }
}