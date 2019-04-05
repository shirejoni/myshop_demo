<?php


namespace App\model;


use App\Lib\Database;
use App\System\Model;

/**
 * @property Database Database
 */
class Product extends Model
{

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


}