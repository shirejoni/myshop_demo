<?php

namespace App\Admin\Controller;

use App\Lib\Config;
use App\Lib\Database;
use App\Lib\Request;
use App\Lib\Response;
use App\Lib\Validate;
use App\Model\Attribute;
use App\Model\Category;
use App\Model\Filter;
use App\Model\Language;
use App\Model\Length;
use App\Model\Manufacturer;
use App\Model\Option;
use App\model\Product;
use App\Model\Stock;
use App\Model\Weight;
use App\System\Controller;


/**
 * @property Response Response
 * @property Request Request
 * @property Database Database
 * @property Language Language
 * @property Config Config
 */
class ControllerProductProduct extends Controller {

    public function index()
    {
    }

    public function add()
    {
        $data = [];
        $messages = [];
        $error = false;
        /** @var Stock $Stock */
        $Stock = $this->load("Stock", $this->registry);
        /** @var Weight $Weight */
        $Weight = $this->load("Weight", $this->registry);
        /** @var Length $Length */
        $Length = $this->load("Length", $this->registry);

        if (isset($this->Request->post['product-post'])) {
            /** @var Product $Product */
            $Product = $this->load("Product", $this->registry);
            /** @var Manufacturer $Manufacturer */
            $Manufacturer = $this->load("Manufacturer", $this->registry);
            /** @var Category $Category */
            $Category = $this->load("Category", $this->registry);
            /** @var Filter $Filter */
            $Filter = $this->load("Filter", $this->registry);
            /** @var Attribute $Attribute */
            $Attribute = $this->load("Attribute", $this->registry);
            /** @var Option $Option */
            $Option = $this->load("Option", $this->registry);
            $languageDefaultID = $this->Language->getDefaultLanguageID();
            $data['product_descriptions']= [];
            foreach ($this->Language->getLanguages() as $language) {
                if(!empty($this->Request->post['product-name-' . $language['language_id']])) {
                    $data['product_descriptions'][$language['language_id']]['name'] = $this->Request->post['product-name-' . $language['language_id']];
                }

                if(!empty($this->Request->post['product-description-' . $language['language_id']])) {
                    $data['product_descriptions'][$language['language_id']]['description'] = $this->Request->post['product-description-' . $language['language_id']];
                }
            }
            if(empty($this->Request->post['product-name-' . $languageDefaultID])) {
                $error = true;
                $messages[] = $this->Language->get('error_product_name');
            }
            if(empty($this->Request->post['product-description-' . $languageDefaultID])) {
                $error = true;
                $messages[] = $this->Language->get('error_product_description');
            }

            if(!empty($this->Request->post['product-price'])) {
                $data['product_price'] = (int) $this->Request->post['product-price'];
            }else {
                $data['product_price'] = 0;
            }
            require_once LIB_PATH . DS . 'jdate/jdf.php';
            if(!empty($this->Request->post['product-date'])) {
                $parts = explode('/', $this->Request->post['product-date']);
                if(count($parts) == 3 && jcheckdate($parts[1], $parts[2], $parts[0])) {
                    $time = jmktime(0,0,0, $parts[1], $parts[2], $parts[0]);
                    $data['product_date'] = $time;
                }
            }
            if(!isset($data['product_date'])) {
                $data['product_date'] = time();
            }
            if(!empty($this->Request->post['product-quantity'])) {
                $data['quantity'] = (int) $this->Request->post['product-quantity'];
            }else {
                $data['quantity'] = 0;
            }

            if(!empty($this->Request->post['product-min-quantity-per-order'])) {
                $data['product_quantity_per_order'] = (int) $this->Request->post['product-min-quantity-per-order'];
            }else {
                $data['product_quantity_per_order'] = 1;
            }

            if(!empty($this->Request->post['product-stock-status']) && $Stock->getStock((int) $this->Request->post['product-stock-status'])) {
                $data['stock_status'] = (int) $this->Request->post['product-stock-status'];
            }else {
                $error = true;
                $messages[] = $this->Language->get('error_product_stock_status_not_selected');
            }

            if(!empty($this->Request->post['product-weight-unit']) && $Weight->getWeight((int) $this->Request->post['product-weight-unit'])) {
                $data['weight_unit_id'] = (int) $this->Request->post['product-weight-unit'];
            }else {
                $error = true;
                $messages[] = $this->Language->get('error_product_weight_unit_not_selected');
            }

            if(!empty($this->Request->post['product-length-unit']) && $Length->getLength((int) $this->Request->post['product-length-unit'])) {
                $data['length_unit_id'] = (int) $this->Request->post['product-length-unit'];
            }else {
                $error = true;
                $messages[] = $this->Language->get('error_product_length_unit_not_selected');
            }

            if(!empty($this->Request->post['product-weight'])) {
                $data['weight_value'] = (int) $this->Request->post['product-weight'];
            }else {
                $data['weight_value'] = 0;
            }

            if(!empty($this->Request->post['product-length'])) {
                $data['length_value'] = (int) $this->Request->post['product-length'];
            }else {
                $data['length_value'] = 0;
            }

            if(!empty($this->Request->post['product-width'])) {
                $data['width_value'] = (int) $this->Request->post['product-width'];
            }else {
                $data['width_value'] = 0;
            }

            if(!empty($this->Request->post['product-height'])) {
                $data['height_value'] = (int) $this->Request->post['product-height'];
            }else {
                $data['height_value'] = 0;
            }

            if(!empty($this->Request->post['product-sort-order']) && $this->Request->post['product-sort-order']) {
                $data['sort_order'] = (int) $this->Request->post['product-sort-order'];
            }else {
                $data['sort_order'] = 0;
            }
            if(!empty($this->Request->post['product-manufacturer-id']) && $Manufacturer->getManufacturerByID((int) $this->Request->post['product-manufacturer-id'])) {
                $data['manufacturer_id'] = (int) $this->Request->post['product-manufacturer-id'];
            }else {
                $error = true;
                $messages[] = $this->Language->getLanguages('error_product_manufacturer_empty');
            }
            if(!isset($this->Request->post['product-categories']) || count($this->Request->post['product-categories']) == 0) {
                $error = true;
                $messages[] = $this->Language->getLanguages('error_product_category_empty');
            }else {
                $data['categories_id'] = [];
                foreach ($this->Request->post['product-categories'] as $productCategory) {
                    if((int) $productCategory && $Category->getCategoryByID((int) $productCategory)) {
                        $data['categories_id'][] = (int) $productCategory;
                    }else {
                        $error = true;
                        $messages[] = $this->Language->get('error_product_category_id_invalid');
                    }
                }
            }
            $data['filters_id'] = [];
            if(isset($this->Request->post['product-filters']) && count($this->Request->post['product-filters']) > 0) {
                foreach ($this->Request->post['product-filters'] as $productFilter) {
                    if((int) $productFilter && $Filter->getFilterByID((int) $productFilter)) {
                        $data['filters_id'][] = (int) $productFilter;
                    }else {
                        $error = true;
                        $messages[] = $this->Language->get('error_product_filter_id_invalid');
                    }
                }
            }
            $data['attributes'] = [];
            if(isset($this->Request->post['product-attribute']) && count($this->Request->post['product-attribute']) > 0) {
                foreach ($this->Request->post['product-attribute'] as $productAttribute) {
                    if((int) $productAttribute['attribute-id'] && $Attribute->getAttributeByID((int) $productAttribute['attribute-id'])) {
                        $attribute_names = [];
                        foreach ($this->Language->getLanguages() as $language) {
                            if(!empty($productAttribute['attribute-value-' . $language['language_id']])) {
                                $attribute_names[$language['language_id']] = $productAttribute['attribute-value-'. $language['language_id']];
                            }
                        }
                        if(!empty($productAttribute['attribute-value-' . $languageDefaultID])) {
                            $data['attributes'][] = array(
                                'attribute_id'  => (int) $productAttribute['attribute-id'],
                                'attribute_values' => $attribute_names,
                            );
                        }else {
                            $error = true;
                            $messages[] = $this->Language->get('error_product_attribute_value_empty');
                        }
                    }else {
                        $error = true;
                        $messages[] = $this->Language->get('error_product_attribute_id_invalid');
                    }
                }
            }
            $data['options'] = [];
            if(isset($this->Request->post['product-option']) && count($this->Request->post['product-option']) > 0) {
                foreach ($this->Request->post['product-option'] as $productOption) {
                    if((int) $productOption['option-id'] && $optionGroupTotal = $Option->getOptionByID((int) $productOption['option-id'])) {
                        $optionDetail = [];
                        $optionDetail['option_id'] = (int) $productOption['option-id'];
                        if(isset($productOption['option-is-required']) && $productOption['option-is-required'] == 1) {
                            $optionDetail['is_required'] = 1;
                        }else {
                            $optionDetail['is_required'] = 0;
                        }
                        if(isset($productOption['option-items']) && count($productOption['option-items']) > 0) {
                            $optionItemsID = [];
                            foreach ($optionGroupTotal['options'] as $option) {
                                $optionItemsID[] = $option['option_value_id'];
                            }
                            $optionItemsDetail = [];
                            foreach ($productOption['option-items'] as $optionItem) {
                                if(isset($optionItem['option-item-id']) && in_array($optionItem['option-item-id'], $optionItemsID)) {
                                    $array = [];
                                    $array['option_item_id'] = $optionItem['option-item-id'];
                                    if(isset($optionItem['quantity']) && ((int) $optionItem['quantity']) > 0) {
                                        $array['quantity'] = (int) $optionItem['quantity'];
                                    }else {
                                        $array['quantity'] = 0;
                                    }
                                    if(isset($optionItem['effect-on-stock']) && $optionItem['effect-on-stock'] == 1) {
                                        $array['effect-on-stock'] = 1;
                                    }else {
                                        $array['effect-on-stock'] = 0;
                                    }
                                    if(isset($optionItem['price-sign']) && $optionItem['price-sign'] == '+') {
                                        $array['price-sign'] = '+';
                                    }else {
                                        $array['price-sign'] = '-';
                                    }
                                    if(isset($optionItem['price']) && ((int) $optionItem['price']) > 0) {
                                        $array['price'] = (int) $optionItem['price'];
                                    }else {
                                        $array['price'] = 0;
                                    }
                                    if(isset($optionItem['weight']) && ((int) $optionItem['weight']) > 0) {
                                        $array['weight'] = (int) $optionItem['weight'];
                                    }else {
                                        $array['weight'] = 0;
                                    }
                                    if(isset($optionItem['weight-sign']) && $optionItem['weight-sign'] == '+') {
                                        $array['weight-sign'] = '+';
                                    }else {
                                        $array['weight-sign'] = '-';
                                    }
                                    $optionItemsDetail[] = $array;
                                }else {
                                    $error = true;
                                    $messages[] = $this->Language->get('error_product_option_item_id_invalid');
                                    break;
                                }
                            }
                            $optionDetail['option_items'] = $optionItemsDetail;
                        }else {
                            $error = true;
                            $messages[] = $this->Language->get('error_product_option_items_empty');
                            break;
                        }
                        $data['options'][] = $optionDetail;
                    }else {
                        $error = true;
                        $messages[] = $this->Language->get('error_product_option_id_invalid');
                        break;
                    }
                }
            }
            $data['special_price'] = [];
            $priority = [];
            if(isset($this->Request->post['product-special']) && count($this->Request->post['product-special']) > 0) {
                foreach ($this->Request->post['product-special'] as $productSpecial) {
                    $specialDetail = [];
                    if(isset($productSpecial['priority']) && $productSpecial['priority'] > 0 && !in_array($productSpecial['priority'], $priority)) {
                        $priority[] = $productSpecial['priority'];
                        $specialDetail['priority'] = +$productSpecial['priority'];
                    }else {
                        $error = true;
                        $messages[] = $this->Language->get('error_product_special_priority_invalid');
                    }

                    if(isset($productSpecial['price']) && $productSpecial['price'] > 0) {
                        if(isset($data['product_price']) && $data['product_price'] > $productSpecial['price']) {
                            $specialDetail['price'] = +$productSpecial['price'];
                        }else {
                            $error = true;
                            $messages[] = $this->Language->get('error_product_special_price_invalid');
                        }
                    }else {
                        $error = true;
                        $messages[] = $this->Language->get('error_product_special_price_invalid');
                    }
                    if(!empty($productSpecial['start_date'])) {
                        $parts = explode('/', $productSpecial['start_date']);
                        if(count($parts) == 3 && jcheckdate($parts[1], $parts[2], $parts[0])) {
                            $time = jmktime(0,0,0, $parts[1], $parts[2], $parts[0]);
                            $specialDetail['start_date'] = $time;
                        }else {
                            $error = true;
                            $messages[] = $this->Language->get('error_product_special_start_date_invalid');
                        }
                    }else {
                        $error = true;
                        $messages[] = $this->Language->get('error_product_special_start_date_invalid');
                    }
                    if(!empty($productSpecial['end_date'])) {
                        $parts = explode('/', $productSpecial['end_date']);
                        if(count($parts) == 3 && jcheckdate($parts[1], $parts[2], $parts[0])) {
                            $time = jmktime(0,0,0, $parts[1], $parts[2], $parts[0]);
                            $specialDetail['end_date'] = $time;
                        }else {
                            $error = true;
                            $messages[] = $this->Language->get('error_product_special_end_date_invalid');
                        }
                    }else {
                        $error = true;
                        $messages[] = $this->Language->get('error_product_special_end_date_invalid');
                    }
                    $data['special_price'][] = $specialDetail;
                }
            }
            $data['images'] = [];
            if(isset($this->Request->post['product-image']) && count($this->Request->post['product-image']) > 0) {
                $imageSortOrder = [];
                foreach ($this->Request->post['product-image'] as $productImage) {
                    if(isset($productImage['src']) && Validate::urlValid($productImage['src'])) {
                        $i = 0;
                        do {
                            $i++;
                            $sort_order = $i;
                        }while(in_array($i, $imageSortOrder));
                        $imageSortOrder[] = $sort_order;
                        $data['images'][] = array(
                            'src'   => $productImage['src'],
                            'sort_order'    => $sort_order,
                        );
                        if(isset($productImage['default']) && $productImage['default'] == true) {
                            $data['image'] = $productImage['src'];
                        }

                    }
                }
            }
            if(!isset($data['image'])) {
                $error = true;
                $messages[] = $this->Language->get('error_product_default_image_not_selected');
            }
            $json = [];
            if(!$error) {
                $data['time_added'] = time();
                $data['time_updated'] = time();
                if($data['sort_order'] == 0) {
                    $rows = $Product->getProducts(array(
                        'sort'  => 'sort_order',
                        'order' => 'DESC',
                        'language_id'   => $languageDefaultID
                    ));
                    $oldSortOrder = count($rows) > 0 ? $rows[0]['sort_order'] : 0;
                    $data['sort_order'] = $oldSortOrder + 1;
                }

                $Product->insertProduct($data);
                $json['status'] = 1;
                $this->Response->endResponse();
                $json['messages'] = [$this->Language->get('message_success_done')];
                $json['redirect'] = ADMIN_URL . "product/product/index?token=" . $_SESSION['token'];
            }else {
                $json['status'] = 0;
                $json['messages'] = $messages;
            }


            $this->Response->setOutPut(json_encode($json));


        } else {

            $Language = $this->Language;

            $data['Languages'] = $Language->getLanguages();
            $data['StocksStatus'] = $Stock->getStocks();
            $data['Weights'] = $Weight->getWeights();
            $data['Lengths'] = $Length->getLengths();
            $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
            $this->Response->setOutPut($this->render('product/product/add', $data));
        }
    }
}