<?php


namespace App\model;


use App\Lib\Database;
use App\System\Model;

/**
 * @property Database Database
 * @property Language Language
 */
class Product extends Model
{

    private $product_id;
    private $sort_order;
    private $language_id;
    private $name;
    /**
     * @var array|bool
     */
    private $rows = [];
    private $manufacturer_id;

    public function getProducts($data = []) {
        $data['sort'] = isset($data['sort']) ? $data['sort'] : '';
        $data['order'] = isset($data['order']) ? strtoupper($data['order']) : 'ASC';
        $data['language_id'] = isset($data['language_id']) ? $data['language_id'] : $this->Language->getLanguageID();

            $sql = "SELECT * FROM product p LEFT JOIN product_language pl ON p.product_id = pl.product_id WHERE 
            pl.language_id = :lID ";
            if(isset($data['filter_name'])) {
                $sql .= " AND pl.name LIKE :fName ";
            }
            $sql .= " GROUP BY p.product_id ";

            $sort_data = array(
                'pl.name',
                'p.sort_order',
                'p.price',
                'p.quantity',
                'p.status',
            );

            if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
                $sql .= " ORDER BY " . $data['sort'];
            }else {
                $data['sort'] = '';
                $sql .= " ORDER BY p.product_id";
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
                $params['fName'] = $data['filter_name'] . "%";
            }
            $this->Database->query($sql, $params);
            $rows = $this->Database->getRows();
        return $rows;
    }

    public function insertProduct($data) {
        $this->Database->query("INSERT INTO product (quantity, stock_status_id, image, manufacturer_id, price, 
        date_available, date_added, date_updated, weight, weight_id, height, width, length, length_id, minimum, 
        status, viewed, sort_order) VALUES (:pQuantity, :pStockStatusID, :pImage, :mID, :pPrice, :pAvailable, :pAdded, :pUpdated,
        :pWeight, :pWeightID, :pHeight, :pWidth, :pLength, :pLengthID, :pMinimum, :pStatus, :pViewed, :pSortOrder)", array(
            'pQuantity'         => $data['quantity'],
            'pStockStatusID'    => $data['stock_status'],
            'pImage'            => $data['image'],
            'pPrice'            => $data['product_price'],
            'mID'               => $data['manufacturer_id'],
            'pAvailable'        => $data['product_date'],
            'pAdded'            => $data['time_added'],
            'pUpdated'          => $data['time_updated'],
            'pWeight'           => $data['weight_value'],
            'pWeightID'           => $data['weight_unit_id'],
            'pHeight'           => $data['height_value'],
            'pWidth'           => $data['width_value'],
            'pLength'           => $data['length_value'],
            'pLengthID'           => $data['length_unit_id'],
            'pMinimum'          => $data['product_quantity_per_order'],
            'pStatus'           => 0,
            'pViewed'           => 0,
            'pSortOrder'        => $data['sort_order'],
        ));
        $product_id = $this->Database->insertId();
        foreach ($data['product_descriptions'] as $language_id => $product_description) {
            $this->Database->query("INSERT INTO product_language (product_id, language_id, name, meta_title, meta_description, meta_keyword, description) VALUES 
            (:pID, :lID, :pName, NULL, NULL, NULL, :pDescription)", array(
                'pID'   => $product_id,
                'lID'   => $language_id,
                'pName' => $product_description['name'],
                'pDescription'  => $product_description['description'],
            ));
        }

        if(isset($data['attributes'])) {
            foreach ($data['attributes'] as $attribute) {
                if(isset($attribute['attribute_values'])) {
                    foreach ($attribute['attribute_values'] as $language_id => $attribute_value) {
                        $this->Database->query("INSERT INTO product_attribute (product_id, attribute_id, language_id, text) 
                    VALUES (:pID, :aID, :lID, :pAText)", array(
                            'pID'   => $product_id,
                            'aID'   => $attribute['attribute_id'],
                            'lID'   => $language_id,
                            'pAText'   => $attribute_value,
                        ));
                    }
                }
            }
        }

        if(isset($data['options'])) {
            foreach ($data['options'] as $option) {
                $this->Database->query("INSERT INTO product_option (product_id, option_id, required) VALUES (
                :pID, :pOptionID, :oRequired)",array(
                    "pID"   => $product_id,
                    'pOptionID' => $option['option_id'],
                    'oRequired' => $option['is_required']
                ));
                $product_option_id = $this->Database->insertId();
                foreach ($option['option_items'] as $option_item) {
                    $this->Database->query("INSERT INTO product_option_value (product_id, product_option_id, option_id,
                    option_value_id, quantity, subtract, price_prefix, price, weight_prefix, weight) VALUES 
                    (:pID, :pOID, :oID, :oVID, :pOQuantity, :pOSubtract, :pOPPrefix, :pOPrice, :pOWP, :pOW)", array(
                        'pID'   => $product_id,
                        'pOID'  => $product_option_id,
                        'oID'   => $option['option_id'],
                        'oVID'  => $option_item['option_item_id'],
                        'pOQuantity'    => $option_item['quantity'],
                        'pOSubtract'    => $option_item['effect-on-stock'],
                        'pOPPrefix'     => $option_item['price-sign'],
                        'pOPrice'       => $option_item['price'],
                        'pOWP'          => $option_item['weight-sign'],
                        'pOW'           => $option_item['weight']
                    ));
                }
            }
        }
        if(isset($data['special_price'])) {
            foreach ($data['special_price'] as $special_price) {
                $this->Database->query("INSERT INTO product_special (product_id, price, priority, date_start, date_end) VALUES
                (:pID, :sPrice, :sPriority, :sDateStarted, :sDateEnd)", array(
                    'pID'   => $product_id,
                    'sPrice'    => $special_price['price'],
                    'sPriority' => $special_price['priority'],
                    'sDateStarted'     => $special_price['start_date'],
                    'sDateEnd'      => $special_price['end_date']
                ));
            }
        }

        if(isset($data['images'])) {

            foreach ($data['images'] as $image) {
                $this->Database->query("INSERT INTO product_image (product_id, image, sort_order) VALUES 
                (:pID, :Image, :SortOrder)", array(
                    'pID' => $product_id,
                    'Image' => $image['src'],
                    'SortOrder' => $image['sort_order']
                ));
            }
        }
        if(isset($data['categories_id'])) {
            foreach ($data['categories_id'] as $category_id) {
                $this->Database->query("INSERT INTO product_category (product_id, category_id) VALUES 
                (:pID, :cID)", array(
                   'pID'    => $product_id,
                   'cID'    => $category_id
                ));
            }
        }
        if(isset($data['filters_id'])) {
            foreach ($data['filters_id'] as $filter_id) {
                $this->Database->query("INSERT INTO product_filter (product_id, filter_id) VALUES 
                (:pID, :fID)", array(
                    'pID'   => $product_id,
                    'fID'   => $filter_id
                ));
            }
        }

        if(isset($data['related_id'])) {
            foreach ($data['related_id'] as $related_id) {
                $this->Database->query("INSERT INTO product_related (product_id, related_id) VALUES 
                (:pID, :rID)", array(
                    'pID'   => $product_id,
                    'rID'   => $related_id
                ));
            }
        }
        return $product_id;
    }

    public function getProductSpecials($product_id) {
        $this->Database->query("SELECT * FROM product_special WHERE product_id = :pID ORDER BY priority, price", array(
            'pID'   => $product_id
        ));
        return $this->Database->getRows();
    }

    public function getProduct($product_id, $lID = null) {
        $language_id = $this->Language->getLanguageID();
        if($lID != null && $lID != "all") {
            $language_id = $lID;
        }
        if($lID != "all") {
            $this->Database->query("SELECT * FROM product p LEFT JOIN product_language pl ON p.product_id = pl.product_id WHERE 
            p.product_id = :pID AND pl.language_id = :lID", array(
                'lID'   => $language_id,
                'pID'   => $product_id
            ));
            $row = $this->Database->getRow();
            $this->product_id = $row['product_id'];
            $this->manufacturer_id = $row['manufacturer_id'];
            $this->sort_order = $row['sort_order'];
            $this->language_id = $row['language_id'];
            $this->name = $row['name'];
            $this->rows = [];
            $this->rows[0] = $row;
            return $row;
        }else {
            $this->Database->query("SELECT * FROM product p LEFT JOIN product_language pl ON p.product_id = pl.product_id WHERE 
            p.product_id = :pID ", array(
                'pID'   => $product_id
            ));
            $rows = $this->Database->getRows();
            $this->product_id = $rows[0]['$this->product_id'];
            $this->manufacturer_id = $rows[0]['$this->manufacturer_id'];
            $this->sort_order = $rows[0]['sort_order'];
            $this->rows = $rows;
            return $rows;
        }
    }

    public function deleteProduct($product_id, $data = []) {
        $this->Database->query("DELETE FROM product WHERE product_id = :pID ", array(
            'pID'   => $product_id
        ));
        return $this->Database->numRows();
    }

    public function editProduct($product_id, $data) {
        if(count($data) > 0) {
            $sql = "UPDATE product SET ";
            $params = [];
            $query = [];
            if(isset($data['sort_order'])) {
                $query[] = "sort_order = :pSortOrder ";
                $params['pSortOrder'] = $data['sort_order'];
            }
            if(isset($data['quantity'])) {
                $query[] = "quantity = :pQuantity ";
                $params['pQuantity'] = $data['quantity'];
            }
            if(isset($data['stock_status'])) {
                $query[] = "stock_status_id = :StockStatusID ";
                $params['StockStatusID'] = $data['stock_status'];
            }
            if(isset($data['image'])) {
                $query[] = "image = :pImage ";
                $params['pImage'] = $data['image'];
            }
            if(isset($data['manufacturer_id'])) {
                $query[] = "manufacturer_id = :mID ";
                $params['mID'] = $data['manufacturer_id'];
            }
            if(isset($data['product_price'])) {
                $query[] = "price = :pPrice ";
                $params['pPrice'] = $data['product_price'];
            }
            if(isset($data['sort_order'])) {
                $query[] = "sort_order = :pSortOrder ";
                $params['pSortOrder'] = $data['sort_order'];
            }
            if(isset($data['time_updated'])) {
                $query[] = "date_updated = :pDateUpdated ";
                $params['pDateUpdated'] = $data['time_updated'];
            }
            if(isset($data['product_date'])) {
                $query[] = "date_available = :pDateAvailable ";
                $params['pDateAvailable'] = $data['product_date'];
            }
            if(isset($data['weight_value'])) {
                $query[] = "weight = :pWeight ";
                $params['pWeight'] = $data['weight_value'];
            }
            if(isset($data['weight_unit_id'])) {
                $query[] = "weight_id = :pWeightUnitID ";
                $params['pWeightUnitID'] = $data['weight_unit_id'];
            }
            if(isset($data['height_value'])) {
                $query[] = "height = :pHeight ";
                $params['pHeight'] = $data['height_value'];
            }
            if(isset($data['length_value'])) {
                $query[] = "length = :pLength ";
                $params['pLength'] = $data['length_value'];
            }
            if(isset($data['width_value'])) {
                $query[] = "width = :pWidth ";
                $params['pWidth'] = $data['width_value'];
            }
            if(isset($data['length_unit_id'])) {
                $query[] = "length_id = :pLengthUnitID ";
                $params['pLengthUnitID'] = $data['length_unit_id'];
            }
            if(isset($data['product_quantity_per_order'])) {
                $query[] = "minimum = :pMinimum ";
                $params['pMinimum'] = $data['product_quantity_per_order'];
            }
            if(isset($data['status'])) {
                $query[] = "status = :pStatus ";
                $params['pStatus'] = $data['status'];
            }
            if(isset($data['viewed'])) {
                $query[] = "viewed = :pViewed ";
                $params['pViewed'] = $data['viewed'];
            }
            $sql .= implode(" , ", $query);
            $sql .= " WHERE product_id = :pID ";
            $params['pID'] = $product_id;
            if(count($query) > 0) {
                $this->Database->query($sql, $params);
            }
            if(isset($data['product_descriptions'])) {
                foreach ($data['product_descriptions'] as $language_id => $product_description) {
                    $this->Database->query("UPDATE product_language SET name = :pName , description = :pDescription WHERE 
                    product_id = :pID AND language_id = :lID ", array(
                        'pID'   => $product_id,
                        'lID'   => $language_id,
                        'pName' => $product_description['name'],
                        'pDescription'  => $product_description['description'],
                    ));
                }
            }

            if(isset($data['attributes'])) {
                $this->Database->query("DELETE FROM product_attribute WHERE product_id = :pID", array(
                    'pID'   => $product_id,
                ));
                foreach ($data['attributes'] as $attribute) {
                    if(isset($attribute['attribute_values'])) {
                        foreach ($attribute['attribute_values'] as $language_id => $attribute_value) {
                            $this->Database->query("INSERT INTO product_attribute (product_id, attribute_id, language_id, text) 
                    VALUES (:pID, :aID, :lID, :pAText)", array(
                                'pID'   => $product_id,
                                'aID'   => $attribute['attribute_id'],
                                'lID'   => $language_id,
                                'pAText'   => $attribute_value,
                            ));
                        }
                    }
                }
            }

            if(isset($data['options'])) {
                $this->Database->query("DELETE FROM product_option WHERE product_id = :pID", array(
                    'pID'   => $product_id
                ));
                $this->Database->query("DELETE FROM product_option_value WHERE product_id = :pID", array(
                    'pID'   => $product_id
                ));
                foreach ($data['options'] as $option) {
                    $this->Database->query("INSERT INTO product_option (product_id, option_id, required) VALUES (
                :pID, :pOptionID, :oRequired)",array(
                        "pID"   => $product_id,
                        'pOptionID' => $option['option_id'],
                        'oRequired' => $option['is_required']
                    ));
                    $product_option_id = $this->Database->insertId();
                    foreach ($option['option_items'] as $option_item) {
                        $this->Database->query("INSERT INTO product_option_value (product_id, product_option_id, option_id,
                    option_value_id, quantity, subtract, price_prefix, price, weight_prefix, weight) VALUES 
                    (:pID, :pOID, :oID, :oVID, :pOQuantity, :pOSubtract, :pOPPrefix, :pOPrice, :pOWP, :pOW)", array(
                            'pID'   => $product_id,
                            'pOID'  => $product_option_id,
                            'oID'   => $option['option_id'],
                            'oVID'  => $option_item['option_item_id'],
                            'pOQuantity'    => $option_item['quantity'],
                            'pOSubtract'    => $option_item['effect-on-stock'],
                            'pOPPrefix'     => $option_item['price-sign'],
                            'pOPrice'       => $option_item['price'],
                            'pOWP'          => $option_item['weight-sign'],
                            'pOW'           => $option_item['weight']
                        ));
                    }
                }
            }
            if(isset($data['special_price'])) {
                $this->Database->query("DELETE FROM product_special WHERE product_id = :pID", array(
                    'pID'   => $product_id
                ));
                foreach ($data['special_price'] as $special_price) {
                    $this->Database->query("INSERT INTO product_special (product_id, price, priority, date_start, date_end) VALUES
                (:pID, :sPrice, :sPriority, :sDateStarted, :sDateEnd)", array(
                        'pID'   => $product_id,
                        'sPrice'    => $special_price['price'],
                        'sPriority' => $special_price['priority'],
                        'sDateStarted'     => $special_price['start_date'],
                        'sDateEnd'      => $special_price['end_date']
                    ));
                }
            }

            if(isset($data['images'])) {

                foreach ($data['images'] as $image) {
                    $this->Database->query("DELETE FROM product_image WHERE product_id = :pID", array(
                        'pID'   => $product_id
                    ));
                    $this->Database->query("INSERT INTO product_image (product_id, image, sort_order) VALUES 
                (:pID, :Image, :SortOrder)", array(
                        'pID' => $product_id,
                        'Image' => $image['src'],
                        'SortOrder' => $image['sort_order']
                    ));
                }
            }
            if(isset($data['categories_id'])) {
                $this->Database->query("DELETE FROM product_category WHERE product_id = :pID", array(
                    'pID'   => $product_id
                ));
                foreach ($data['categories_id'] as $category_id) {
                    $this->Database->query("INSERT INTO product_category (product_id, category_id) VALUES 
                (:pID, :cID)", array(
                        'pID'    => $product_id,
                        'cID'    => $category_id
                    ));
                }
            }
            if(isset($data['filters_id'])) {
                $this->Database->query("DELETE FROM product_filter WHERE product_id = :pID", array(
                    'pID'   => $product_id
                ));
                foreach ($data['filters_id'] as $filter_id) {
                    $this->Database->query("INSERT INTO product_filter (product_id, filter_id) VALUES 
                (:pID, :fID)", array(
                        'pID'   => $product_id,
                        'fID'   => $filter_id
                    ));
                }
            }

            if(isset($data['related_id'])) {
                $this->Database->query("DELETE FROM product_related WHERE product_id = :pID", array(
                    'pID'   => $product_id
                ));
                foreach ($data['related_id'] as $related_id) {
                    $this->Database->query("INSERT INTO product_related (product_id, related_id) VALUES 
                (:pID, :rID)", array(
                        'pID'   => $product_id,
                        'rID'   => $related_id
                    ));
                }
            }
            return $this->Database->numRows() > 0 ? true : false;
        }
        return false;
    }


}