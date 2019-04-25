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

    public function insertProduct($data, $product_id = null) {
        if(!$product_id) {
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
        }

        foreach ($data['product_descriptions'] as $language_id => $product_description) {
            $this->Database->query("INSERT INTO product_language (product_id, language_id, name, meta_title, meta_description, meta_keyword, description) VALUES 
            (:pID, :lID, :pName, NULL, NULL, NULL, :pDescription)", array(
                'pID'   => $product_id,
                'lID'   => $language_id,
                'pName' => isset($product_description['name']) ? $product_description['name'] : '',
                'pDescription'  => isset($product_description['description']) ? $product_description['description'] : '',
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

    public function getImages($product_id) {
        $this->Database->query("SELECT * FROM product_image WHERE product_id = :pID ORDER BY sort_order ASC", array(
            'pID'   => $product_id
        ));
        return $this->Database->getRows();
    }

    public function getReviews($product_id) {
        $this->Database->query("SELECT * FROM review WHERE product_id = :pID AND status = 1 ORDER BY date_updated DESC", array(
            'pID'   => $product_id
        ));
        return $this->Database->getRows();
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
            $this->product_id = $rows[0]['product_id'];
            $this->manufacturer_id = $rows[0]['manufacturer_id'];
            $this->sort_order = $rows[0]['sort_order'];
            $this->rows = $rows;
            return $rows;
        }
    }

    public function deleteProduct($product_id, $data = []) {
        if(isset($data['product_descriptions']) && count($data['product_descriptions']) > 0) {
            foreach ($data['product_descriptions'] as $language_id => $product_description) {
                $this->Database->query("DELETE FROM product_language WHERE language_id = :lID AND product_id = :pID ", array(
                    'lID'   => $language_id,
                    'pID'   => $product_id
                ));
            }
        }else {
            $this->Database->query("DELETE FROM product WHERE product_id = :pID ", array(
                'pID' => $product_id
            ));
        }
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
                $this->Database->query("DELETE FROM product_image WHERE product_id = :pID", array(
                    'pID'   => $product_id
                ));
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

    public function getCategories($product_id) {
        $this->Database->query("SELECT category_id FROM product_category WHERE product_id = :pID", array(
            'pID'   => $product_id
        ));
        $result = [];
        foreach ($this->Database->getRows() as $row) {
            $result[] = $row['category_id'];
        }
        return $result;
    }

    public function getCategory($product_id) {
        $this->Database->query("SELECT * FROM product_category pc LEFT JOIN category c ON pc.category_id = c.category_id LEFT JOIN category_language cl ON cl.category_id = c.category_id WHERE  pc.product_id = :pID 
        AND cl.language_id = :lID ORDER BY c.level DESC", array(
            'pID'   => $product_id,
            'lID'   => $this->Language->getLanguageID(),
        ));
        return $this->Database->getRow();
    }

    public function getFilters($product_id) {
        $this->Database->query("SELECT filter_id FROM product_filter WHERE product_id = :pID", array(
            'pID'   => $product_id
        ));
        $result = [];
        foreach ($this->Database->getRows() as $row) {
            $result[] = $row['filter_id'];
        }
        return $result;
    }

    public function getAttributes($product_id, $lID = null) {
        $language_id = $this->Language->getLanguageID();
        if($lID && $lID != "all") {
            $language_id = $lID;
        }
        if($lID != "all") {
            $this->Database->query("SELECT * FROM product_attribute pa LEFT JOIN attribute a ON pa.attribute_id = a.attribute_id  LEFT JOIN attribute_language al 
            ON al.attribute_id = a.attribute_id LEFT JOIN attribute_group ag ON ag.attribute_group_id = a.attribute_group_id WHERE product_id = :aID AND al.language_id = :lID ORDER BY ag.sort_order, a.attribute_group_id, a.sort_order ASC", array(
                'aID'   => $product_id,
                'lID'   => $language_id
            ));
        }else {
            $this->Database->query("SELECT * FROM product_attribute pa LEFT JOIN attribute a ON pa.attribute_id = a.attribute_id  LEFT JOIN attribute_language al 
            ON al.attribute_id = a.attribute_id WHERE product_id = :aID ORDER BY a.attribute_group_id, a.sort_order ASC", array(
                'aID'   => $product_id
            ));
        }

        $result = [];
        foreach ($this->Database->getRows() as $row) {
            $result[] = array(
                'attribute_id'  => $row['attribute_id'],
                'attribute_group_id'  => $row['attribute_group_id'],
                'name'  => $row['name'],
                'sort_order'  => $row['sort_order'],
                'value'         => $row['text'],
                'language_id'   => $row['language_id']
            );
        }
        return $result;
    }

    public function getOptions($product_id, $lID = null) {
        $language_id = $this->Language->getLanguageID();
        if($lID != null) {
            $language_id = $lID;
        }
        $this->Database->query("SELECT * FROM product_option po LEFT JOIN `option` o ON o.option_id = po.option_id LEFT JOIN 
        option_language ol ON o.option_id = ol.option_id WHERE product_id = :pID AND language_id = :lID ORDER BY o.sort_order ASC", array(
            'pID'   => $product_id,
        'lID'       => $language_id
        ));
        $product_options = [];
        foreach ($this->Database->getRows() as $row) {
            $product_options[] = array(
                'product_option_id' => $row['product_option_id'],
                'option_id'         => $row['option_id'],
                'product_id'         => $row['product_id'],
                'required'         => $row['required'],
                'option_type'         => $row['option_type'],
                'sort_order'         => $row['sort_order'],
                'language_id'         => $row['language_id'],
                'name'         => $row['name'],
            );
        }
        foreach ($product_options as $index => $product_option) {
            $product_option_values_data = [];
            $this->Database->query("SELECT * FROM product_option_value pov LEFT JOIN 
            option_value ov on pov.option_value_id = ov.option_value_id WHERE pov.product_option_id = :pOID", array(
               'pOID'   => $product_option['product_option_id']
            ));
            foreach ($this->Database->getRows() as $row) {
                $product_option_values_data[] = array(
                    'product_option_value_id'   => $row['product_option_value_id'],
                    'product_id'                => $row['product_id'],
                    'product_option_id'         => $row['product_option_id'],
                    'option_id'                 => $row['option_id'],
                    'option_value_id'           => $row['option_value_id'],
                    'quantity'                  => $row['quantity'],
                    'subtract'                  => $row['subtract'],
                    'price_prefix'              => $row['price_prefix'],
                    'price'                     => $row['price'],
                    'weight_prefix'             => $row['weight_prefix'],
                    'weight'                    => $row['weight']
                );
            }
            $product_options[$index]['product_option_values'] = $product_option_values_data;

        }
        return $product_options;
    }

    public function getProductComplete($product_id, $lID = null)
    {
        $language_id = $this->Language->getLanguageID();
        if($lID) {
            $language_id = $lID;
        }
        $this->Database->query("SELECT *,p.image as `image`, pl.name AS name, ml.name AS manufacturer_name ,(SELECT ps.price FROM product_special ps WHERE ps.product_id = p.product_id 
        AND ps.date_start < UNIX_TIMESTAMP() AND ps.date_end > UNIX_TIMESTAMP() ORDER BY ps.priority DESC LIMIT 0,1) AS special, (SELECT ss.name FROM
         stock_status ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = pl.language_id) as `stock_status_name`,
         (SELECT wl.unit FROM weight_language wl WHERE wl.weight_id = p.weight_id AND wl.language_id = pl.language_id ) AS weight_unit,
         (SELECT ll.unit FROM length_language ll WHERE ll.length_id = p.length_id AND ll.language_id = pl.language_id ) AS `length_unit`,
         (SELECT AVG(r1.rate) FROM review r1 WHERE r1.product_id = p.product_id AND r1.status = 1)  AS rating, (SELECT COUNT(*) FROM 
         review r2 WHERE r2.product_id = p.product_id AND r2.status = 1) AS reviews
          FROM product p LEFT JOIN product_language pl ON p.product_id = pl.product_id
        LEFT JOIN manufacturer m ON m.manufacturer_id = p.manufacturer_id LEFT JOIN manufacturer_language ml ON ml.manufacturer_id = m.manufacturer_id 
        WHERE p.product_id = :pID  AND pl.language_id = :lID ", array(
            'pID'   => $product_id,
            'lID'   => $language_id
        ));
        $row = $this->Database->getRow();
        return array(
            'product_id'    => $row['product_id'],
            'special'    => $row['special'],
            'rate'      => $row['rating'],
            'reviews_count'   => $row['reviews'],
            'name'    => $row['name'],
            'description'    => $row['description'],
            'language_id'    => $row['language_id'],
            'quantity'      => $row['quantity'],
            'stock_status_id'   => $row['stock_status_id'],
            'stock_status'      => $row['stock_status_name'],
            'image'             => $row['image'],
            'manufacturer_id'   => $row['manufacturer_id'],
            'manufacturer_name'   => $row['manufacturer_name'],
            'price'             => $row['price'],
            'date_available'    => $row['date_available'],
            'date_added'        => $row['date_added'],
            'date_updated'      => $row['date_updated'],
            'weight'            => $row['weight'],
            'weight_id'            => $row['weight_id'],
            'weight_unit'       => $row['weight_unit'],
            'length'            => $row['length'],
            'length_id'            => $row['length_id'],
            'width'             => $row['width'],
            'height'            => $row['height'],
            'length_unit'       => $row['length_unit'],
            'minimum'           => $row['minimum'],
            'viewed'            => $row['viewed'],
            'sort_order'        => $row['sort_order']
        );
    }

}