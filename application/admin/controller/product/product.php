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
use App\model\Image;
use App\Model\Language;
use App\Model\Length;
use App\Model\Manufacturer;
use App\Model\Option;
use App\model\Product;
use App\Model\Stock;
use App\Model\Weight;
use App\System\Controller;
use Aws\DAX\DAXClient;


/**
 * @property Response Response
 * @property Request Request
 * @property Database Database
 * @property Language Language
 * @property Config Config
 */
class ControllerProductProduct extends Controller
{

    public function index()
    {
        $data = [];
        $language_id = $this->Language->getLanguageID();
        /** @var Product $Product */
        $Product = $this->load("Product", $this->registry);
        if (isset($this->Request->get['page'])) {
            $page = (int)$this->Request->get['page'] > 0 ? (int)$this->Request->get['page'] : 1;
        } else {
            $page = 1;
        }
        $products = $Product->getProducts(array(
            'start' => ($page - 1) * $this->Config->get('config_limit_admin'),
            'limit' => $this->Config->get('config_limit_admin'),
            'order' => 'DESC'
        ));
        $data['Products'] = [];
        /** @var Image $Image */
        $Image = $this->load("Image", $this->registry);
        foreach ($products as $product) {
            if (is_file(ASSETS_PATH . DS . substr($product['image'], strlen(ASSETS_URL)))) {
                $image = ASSETS_URL . $Image->resize(substr($product['image'], strlen(ASSETS_URL)), 200, 200);
            } else {
                $image = ASSETS_URL . $Image->resize('img/no-image.jpeg', 200, 200);
            }
            $product_specials = $Product->getProductSpecials($product['product_id']);
            $special = '';
            foreach ($product_specials as $product_special) {
                if ($product_special['date_start'] < time() && $product_special['date_end'] > time()) {
                    $special = $product_special['price'];
                }
            }
            $data['Products'][] = array(
                'product_id' => $product['product_id'],
                'image' => $image,
                'name' => $product['name'],
                'price' => $product['price'],
                'special' => $special,
                'status' => $product['status'],
                'quantity' => $product['quantity'],
                'sort_order' => $product['sort_order'],
            );
        }
        $Language = $this->Language;
        $data['Languages'] = $Language->getLanguages();
        $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
        $this->Response->setOutPut($this->render('product/product/index', $data));

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
            $data['product_descriptions'] = [];
            foreach ($this->Language->getLanguages() as $language) {
                if (!empty($this->Request->post['product-name-' . $language['language_id']])) {
                    $data['product_descriptions'][$language['language_id']]['name'] = $this->Request->post['product-name-' . $language['language_id']];
                }

                if (!empty($this->Request->post['product-description-' . $language['language_id']])) {
                    $data['product_descriptions'][$language['language_id']]['description'] = $this->Request->post['product-description-' . $language['language_id']];
                }
            }
            if (empty($this->Request->post['product-name-' . $languageDefaultID])) {
                $error = true;
                $messages[] = $this->Language->get('error_product_name');
            }
            if (empty($this->Request->post['product-description-' . $languageDefaultID])) {
                $error = true;
                $messages[] = $this->Language->get('error_product_description');
            }

            if (!empty($this->Request->post['product-price'])) {
                $data['product_price'] = (int)$this->Request->post['product-price'];
            } else {
                $data['product_price'] = 0;
            }
            require_once LIB_PATH . DS . 'jdate/jdf.php';
            if (!empty($this->Request->post['product-date'])) {
                $parts = explode('/', $this->Request->post['product-date']);
                if (count($parts) == 3 && jcheckdate($parts[1], $parts[2], $parts[0])) {
                    $time = jmktime(0, 0, 0, $parts[1], $parts[2], $parts[0]);
                    $data['product_date'] = $time;
                }
            }
            if (!isset($data['product_date'])) {
                $data['product_date'] = time();
            }
            if (!empty($this->Request->post['product-quantity'])) {
                $data['quantity'] = (int)$this->Request->post['product-quantity'];
            } else {
                $data['quantity'] = 0;
            }

            if (!empty($this->Request->post['product-min-quantity-per-order'])) {
                $data['product_quantity_per_order'] = (int)$this->Request->post['product-min-quantity-per-order'];
            } else {
                $data['product_quantity_per_order'] = 1;
            }

            if (!empty($this->Request->post['product-stock-status']) && $Stock->getStock((int)$this->Request->post['product-stock-status'])) {
                $data['stock_status'] = (int)$this->Request->post['product-stock-status'];
            } else {
                $error = true;
                $messages[] = $this->Language->get('error_product_stock_status_not_selected');
            }

            if (!empty($this->Request->post['product-weight-unit']) && $Weight->getWeight((int)$this->Request->post['product-weight-unit'])) {
                $data['weight_unit_id'] = (int)$this->Request->post['product-weight-unit'];
            } else {
                $error = true;
                $messages[] = $this->Language->get('error_product_weight_unit_not_selected');
            }

            if (!empty($this->Request->post['product-length-unit']) && $Length->getLength((int)$this->Request->post['product-length-unit'])) {
                $data['length_unit_id'] = (int)$this->Request->post['product-length-unit'];
            } else {
                $error = true;
                $messages[] = $this->Language->get('error_product_length_unit_not_selected');
            }

            if (!empty($this->Request->post['product-weight'])) {
                $data['weight_value'] = (int)$this->Request->post['product-weight'];
            } else {
                $data['weight_value'] = 0;
            }

            if (!empty($this->Request->post['product-length'])) {
                $data['length_value'] = (int)$this->Request->post['product-length'];
            } else {
                $data['length_value'] = 0;
            }

            if (!empty($this->Request->post['product-width'])) {
                $data['width_value'] = (int)$this->Request->post['product-width'];
            } else {
                $data['width_value'] = 0;
            }

            if (!empty($this->Request->post['product-height'])) {
                $data['height_value'] = (int)$this->Request->post['product-height'];
            } else {
                $data['height_value'] = 0;
            }

            if (!empty($this->Request->post['product-sort-order']) && $this->Request->post['product-sort-order']) {
                $data['sort_order'] = (int)$this->Request->post['product-sort-order'];
            } else {
                $data['sort_order'] = 0;
            }
            if (!empty($this->Request->post['product-manufacturer-id']) && $Manufacturer->getManufacturerByID((int)$this->Request->post['product-manufacturer-id'])) {
                $data['manufacturer_id'] = (int)$this->Request->post['product-manufacturer-id'];
            } else {
                $error = true;
                $messages[] = $this->Language->getLanguages('error_product_manufacturer_empty');
            }
            if (!isset($this->Request->post['product-categories']) || count($this->Request->post['product-categories']) == 0) {
                $error = true;
                $messages[] = $this->Language->getLanguages('error_product_category_empty');
            } else {
                $data['categories_id'] = [];
                foreach ($this->Request->post['product-categories'] as $productCategory) {
                    if ((int)$productCategory && $Category->getCategoryByID((int)$productCategory)) {
                        $data['categories_id'][] = (int)$productCategory;
                    } else {
                        $error = true;
                        $messages[] = $this->Language->get('error_product_category_id_invalid');
                    }
                }
            }
            $data['filters_id'] = [];
            if (isset($this->Request->post['product-filters']) && count($this->Request->post['product-filters']) > 0) {
                foreach ($this->Request->post['product-filters'] as $productFilter) {
                    if ((int)$productFilter && $Filter->getFilterByID((int)$productFilter)) {
                        $data['filters_id'][] = (int)$productFilter;
                    } else {
                        $error = true;
                        $messages[] = $this->Language->get('error_product_filter_id_invalid');
                    }
                }
            }
            $data['attributes'] = [];
            if (isset($this->Request->post['product-attribute']) && count($this->Request->post['product-attribute']) > 0) {
                foreach ($this->Request->post['product-attribute'] as $productAttribute) {
                    if ((int)$productAttribute['attribute-id'] && $Attribute->getAttributeByID((int)$productAttribute['attribute-id'])) {
                        $attribute_names = [];
                        foreach ($this->Language->getLanguages() as $language) {
                            if (!empty($productAttribute['attribute-value-' . $language['language_id']])) {
                                $attribute_names[$language['language_id']] = $productAttribute['attribute-value-' . $language['language_id']];
                            }
                        }
                        if (!empty($productAttribute['attribute-value-' . $languageDefaultID])) {
                            $data['attributes'][] = array(
                                'attribute_id' => (int)$productAttribute['attribute-id'],
                                'attribute_values' => $attribute_names,
                            );
                        } else {
                            $error = true;
                            $messages[] = $this->Language->get('error_product_attribute_value_empty');
                        }
                    } else {
                        $error = true;
                        $messages[] = $this->Language->get('error_product_attribute_id_invalid');
                    }
                }
            }
            $data['options'] = [];
            if (isset($this->Request->post['product-option']) && count($this->Request->post['product-option']) > 0) {
                foreach ($this->Request->post['product-option'] as $productOption) {
                    if ((int)$productOption['option-id'] && $optionGroupTotal = $Option->getOptionByID((int)$productOption['option-id'])) {
                        $optionDetail = [];
                        $optionDetail['option_id'] = (int)$productOption['option-id'];
                        if (isset($productOption['option-is-required']) && $productOption['option-is-required'] == 1) {
                            $optionDetail['is_required'] = 1;
                        } else {
                            $optionDetail['is_required'] = 0;
                        }
                        if (isset($productOption['option-items']) && count($productOption['option-items']) > 0) {
                            $optionItemsID = [];
                            foreach ($optionGroupTotal['options'] as $option) {
                                $optionItemsID[] = $option['option_value_id'];
                            }
                            $optionItemsDetail = [];
                            foreach ($productOption['option-items'] as $optionItem) {
                                if (isset($optionItem['option-item-id']) && in_array($optionItem['option-item-id'], $optionItemsID)) {
                                    $array = [];
                                    $array['option_item_id'] = $optionItem['option-item-id'];
                                    if (isset($optionItem['quantity']) && ((int)$optionItem['quantity']) > 0) {
                                        $array['quantity'] = (int)$optionItem['quantity'];
                                    } else {
                                        $array['quantity'] = 0;
                                    }
                                    if (isset($optionItem['effect-on-stock']) && $optionItem['effect-on-stock'] == 1) {
                                        $array['effect-on-stock'] = 1;
                                    } else {
                                        $array['effect-on-stock'] = 0;
                                    }
                                    if (isset($optionItem['price-sign']) && $optionItem['price-sign'] == '+') {
                                        $array['price-sign'] = '+';
                                    } else {
                                        $array['price-sign'] = '-';
                                    }
                                    if (isset($optionItem['price']) && ((int)$optionItem['price']) > 0) {
                                        $array['price'] = (int)$optionItem['price'];
                                    } else {
                                        $array['price'] = 0;
                                    }
                                    if (isset($optionItem['weight']) && ((int)$optionItem['weight']) > 0) {
                                        $array['weight'] = (int)$optionItem['weight'];
                                    } else {
                                        $array['weight'] = 0;
                                    }
                                    if (isset($optionItem['weight-sign']) && $optionItem['weight-sign'] == '+') {
                                        $array['weight-sign'] = '+';
                                    } else {
                                        $array['weight-sign'] = '-';
                                    }
                                    $optionItemsDetail[] = $array;
                                } else {
                                    $error = true;
                                    $messages[] = $this->Language->get('error_product_option_item_id_invalid');
                                    break;
                                }
                            }
                            $optionDetail['option_items'] = $optionItemsDetail;
                        } else {
                            $error = true;
                            $messages[] = $this->Language->get('error_product_option_items_empty');
                            break;
                        }
                        $data['options'][] = $optionDetail;
                    } else {
                        $error = true;
                        $messages[] = $this->Language->get('error_product_option_id_invalid');
                        break;
                    }
                }
            }
            $data['special_price'] = [];
            $priority = [];
            if (isset($this->Request->post['product-special']) && count($this->Request->post['product-special']) > 0) {
                foreach ($this->Request->post['product-special'] as $productSpecial) {
                    $specialDetail = [];
                    if (isset($productSpecial['priority']) && $productSpecial['priority'] > 0 && !in_array($productSpecial['priority'], $priority)) {
                        $priority[] = $productSpecial['priority'];
                        $specialDetail['priority'] = +$productSpecial['priority'];
                    } else {
                        $error = true;
                        $messages[] = $this->Language->get('error_product_special_priority_invalid');
                    }

                    if (isset($productSpecial['price']) && $productSpecial['price'] > 0) {
                        if (isset($data['product_price']) && $data['product_price'] > $productSpecial['price']) {
                            $specialDetail['price'] = +$productSpecial['price'];
                        } else {
                            $error = true;
                            $messages[] = $this->Language->get('error_product_special_price_invalid');
                        }
                    } else {
                        $error = true;
                        $messages[] = $this->Language->get('error_product_special_price_invalid');
                    }
                    if (!empty($productSpecial['start_date'])) {
                        $parts = explode('/', $productSpecial['start_date']);
                        if (count($parts) == 3 && jcheckdate($parts[1], $parts[2], $parts[0])) {
                            $time = jmktime(0, 0, 0, $parts[1], $parts[2], $parts[0]);
                            $specialDetail['start_date'] = $time;
                        } else {
                            $error = true;
                            $messages[] = $this->Language->get('error_product_special_start_date_invalid');
                        }
                    } else {
                        $error = true;
                        $messages[] = $this->Language->get('error_product_special_start_date_invalid');
                    }
                    if (!empty($productSpecial['end_date'])) {
                        $parts = explode('/', $productSpecial['end_date']);
                        if (count($parts) == 3 && jcheckdate($parts[1], $parts[2], $parts[0])) {
                            $time = jmktime(0, 0, 0, $parts[1], $parts[2], $parts[0]);
                            $specialDetail['end_date'] = $time;
                        } else {
                            $error = true;
                            $messages[] = $this->Language->get('error_product_special_end_date_invalid');
                        }
                    } else {
                        $error = true;
                        $messages[] = $this->Language->get('error_product_special_end_date_invalid');
                    }
                    $data['special_price'][] = $specialDetail;
                }
            }
            $data['images'] = [];
            if (isset($this->Request->post['product-image']) && count($this->Request->post['product-image']) > 0) {
                $imageSortOrder = [];
                foreach ($this->Request->post['product-image'] as $productImage) {
                    if (isset($productImage['src']) && Validate::urlValid($productImage['src'])) {
                        $i = 0;
                        do {
                            $i++;
                            $sort_order = $i;
                        } while (in_array($i, $imageSortOrder));
                        $imageSortOrder[] = $sort_order;
                        $data['images'][] = array(
                            'src' => $productImage['src'],
                            'sort_order' => $sort_order,
                        );
                        if (isset($productImage['default']) && $productImage['default'] == "true") {
                            $data['image'] = $productImage['src'];
                        }

                    }
                }
            }
            if (!isset($data['image'])) {
                $error = true;
                $messages[] = $this->Language->get('error_product_default_image_not_selected');
            }
            $json = [];
            if (!$error) {
                $data['time_added'] = time();
                $data['time_updated'] = time();
                if ($data['sort_order'] == 0) {
                    $rows = $Product->getProducts(array(
                        'sort' => 'sort_order',
                        'order' => 'DESC',
                        'language_id' => $languageDefaultID
                    ));
                    $oldSortOrder = count($rows) > 0 ? $rows[0]['sort_order'] : 0;
                    $data['sort_order'] = $oldSortOrder + 1;
                }

                $Product->insertProduct($data);
                $json['status'] = 1;
                $json['data'] = $data;
                $this->Response->endResponse();
                $json['messages'] = [$this->Language->get('message_success_done')];
                $json['redirect'] = ADMIN_URL . "product/product/index?token=" . $_SESSION['token'];
            } else {
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

    public function edit()
    {

        $data = array();
        $error = false;
        $messages = [];
        if (isset($this->Request->get[0])) {
            $product_id = (int)$this->Request->get[0];
            /** @var Stock $Stock */
            $Stock = $this->load("Stock", $this->registry);
            /** @var Weight $Weight */
            $Weight = $this->load("Weight", $this->registry);
            /** @var Length $Length */
            $Length = $this->load("Length", $this->registry);
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
            /** @var Product $Product */
            $Product = $this->load("Product", $this->registry);
            $productTotal = $Product->getProduct($product_id, "all");
            $productInfo = [];
            $Language = $this->Language;
            foreach ($productTotal as $pRow) {
                $productInfo['product_descriptions'][$pRow['language_id']]['name'] = $pRow['name'];
                $productInfo['product_descriptions'][$pRow['language_id']]['description'] = $pRow['description'];
            }
            require_once LIB_PATH . DS . 'jdate/jdf.php';

            $productInfo['product_id'] = $productTotal[0]['product_id'];
            $productInfo['quantity'] = $productTotal[0]['quantity'];
            $productInfo['stock_status_id'] = $productTotal[0]['stock_status_id'];
            $productInfo['image'] = $productTotal[0]['image'];
            $productInfo['manufacturer_id'] = $productTotal[0]['manufacturer_id'];
            $productInfo['price'] = $productTotal[0]['price'];
            $productInfo['date_available'] = jdate('Y-m-d', $productTotal[0]['date_available'], '', '', 'en');
            $productInfo['date_available_time'] = $productTotal[0]['date_available'];
            $productInfo['weight'] = $productTotal[0]['weight'];
            $productInfo['weight_id'] = $productTotal[0]['weight_id'];
            $productInfo['height'] = $productTotal[0]['height'];
            $productInfo['width'] = $productTotal[0]['width'];
            $productInfo['length'] = $productTotal[0]['length'];
            $productInfo['length_id'] = $productTotal[0]['length_id'];
            $productInfo['minimum'] = $productTotal[0]['minimum'];
            $productInfo['status'] = $productTotal[0]['status'];
            $productInfo['viewed'] = $productTotal[0]['viewed'];
            $productInfo['sort_order'] = $productTotal[0]['sort_order'];
            $manufacturerInfo = $Manufacturer->getManufacturerByID($productInfo['manufacturer_id']);
            if (!empty($manufacturerInfo)) {
                $productInfo['manufacturer_name'] = $manufacturerInfo['name'];
            }

            $product_categories_id = $Product->getCategories($productInfo['product_id']);
            $productInfo['categories'] = [];
            foreach ($product_categories_id as $category_id) {
                $category = $Category->getCategoryByID($category_id);
                $productInfo['categories'][$category_id] = array(
                    'category_id' => $category_id,
                    'name' => $category['name']
                );
            }

            $product_filters_id = $Product->getFilters($productInfo['product_id']);
            $productInfo['product_filters'] = [];
            foreach ($product_filters_id as $filter_id) {
                $filter = $Filter->getFilterByID($filter_id);
                $productInfo['product_filters'][$filter_id] = array(
                    'filter_id' => $filter_id,
                    'filter_group_name' => $filter['group_name'],
                    'name' => $filter['name']
                );
            }

            $product_attributes = $Product->getAttributes($productInfo['product_id']);
            $productInfo['attributes'] = [];
            foreach ($product_attributes as $attribute) {
                $a = $Attribute->getAttributeByID($attribute['attribute_id']);
                $productInfo['attributes'][$attribute['attribute_id']]['attribute_id'] = $a['attribute_id'];
                $productInfo['attributes'][$attribute['attribute_id']]['attribute_group_name'] = $a['attributegroup_name'];
                $productInfo['attributes'][$attribute['attribute_id']]['name'] = $a['name'];
                $productInfo['attributes'][$attribute['attribute_id']]['value'][$attribute['language_id']] = $attribute['value'];

            }

            $productInfo['product_options'] = $Product->getOptions($productInfo['product_id']);
            $data['options'] = [];
            foreach ($productInfo['product_options'] as $product_option) {
                $data['Options'][$product_option['option_id']] = $Option->getOptionByID($product_option['option_id']);
            }

            $product_specials = $Product->getProductSpecials($productInfo['product_id']);
            $productInfo['product_specials'] = [];
            foreach ($product_specials as $product_special) {
                $productInfo['product_specials'][] = array(
                    'product_special_id' => $product_special['product_special_id'],
                    'product_id' => $product_special['product_id'],
                    'price' => $product_special['price'],
                    'product_priority' => $product_special['priority'],
                    'date_start' => jdate('Y/m/d', $product_special['date_start']),
                    'date_end' => jdate('Y/m/d', $product_special['date_end'])
                );
            }
            $product_images = $Product->getImages($productInfo['product_id']);
            $productInfo['product_images'] = [];
            /** @var Image $Image */
            $Image = $this->load("Image", $this->registry);
            foreach ($product_images as $product_image) {
                if (is_file(ASSETS_PATH . DS . substr($product_image['image'], strlen(ASSETS_URL)))) {
                    $image = ASSETS_URL . $Image->resize(substr($product_image['image'], strlen(ASSETS_URL)), 200, 200);
                    $default = $product_image['image'] == $productInfo['image'] ? 1 : 0;
                    $productInfo['product_images'][] = array(
                        'default' => $default,
                        'product_id' => $product_image['product_id'],
                        'src' => $image,
                        'image' => $product_image['image'],
                        'sort_order' => $product_image['sort_order']
                    );
                }
            }




            if ($product_id && $productInfo) {
                if (isset($this->Request->post['product-post'])) {

                    $languageDefaultID = $this->Language->getDefaultLanguageID();
                    $data['product_descriptions'] = [];
                    foreach ($this->Language->getLanguages() as $language) {
                        if (!empty($this->Request->post['product-name-' . $language['language_id']])) {
                            $data['product_descriptions'][$language['language_id']]['name'] = $this->Request->post['product-name-' . $language['language_id']];
                        }

                        if (!empty($this->Request->post['product-description-' . $language['language_id']])) {
                            $data['product_descriptions'][$language['language_id']]['description'] = $this->Request->post['product-description-' . $language['language_id']];
                        }
                    }
                    if (empty($this->Request->post['product-name-' . $languageDefaultID])) {
                        $error = true;
                        $messages[] = $this->Language->get('error_product_name');
                    }
                    if (empty($this->Request->post['product-description-' . $languageDefaultID])) {
                        $error = true;
                        $messages[] = $this->Language->get('error_product_description');
                    }

                    if (!empty($this->Request->post['product-price'])) {
                        $data['product_price'] = (int)$this->Request->post['product-price'];
                    } else {
                        $data['product_price'] = 0;
                    }
                    require_once LIB_PATH . DS . 'jdate/jdf.php';
                    if (!empty($this->Request->post['product-date'])) {
                        $parts = explode('/', $this->Request->post['product-date']);
                        if (count($parts) == 3 && jcheckdate($parts[1], $parts[2], $parts[0])) {
                            $time = jmktime(0, 0, 0, $parts[1], $parts[2], $parts[0]);
                            $data['product_date'] = $time;
                        }
                    }
                    if (!isset($data['product_date'])) {
                        $data['product_date'] = time();
                    }
                    if (!empty($this->Request->post['product-quantity'])) {
                        $data['quantity'] = (int)$this->Request->post['product-quantity'];
                    } else {
                        $data['quantity'] = 0;
                    }

                    if (!empty($this->Request->post['product-min-quantity-per-order'])) {
                        $data['product_quantity_per_order'] = (int)$this->Request->post['product-min-quantity-per-order'];
                    } else {
                        $data['product_quantity_per_order'] = 1;
                    }

                    if (!empty($this->Request->post['product-stock-status']) && $Stock->getStock((int)$this->Request->post['product-stock-status'])) {
                        $data['stock_status'] = (int)$this->Request->post['product-stock-status'];
                    } else {
                        $error = true;
                        $messages[] = $this->Language->get('error_product_stock_status_not_selected');
                    }

                    if (!empty($this->Request->post['product-weight-unit']) && $Weight->getWeight((int)$this->Request->post['product-weight-unit'])) {
                        $data['weight_unit_id'] = (int)$this->Request->post['product-weight-unit'];
                    } else {
                        $error = true;
                        $messages[] = $this->Language->get('error_product_weight_unit_not_selected');
                    }

                    if (!empty($this->Request->post['product-length-unit']) && $Length->getLength((int)$this->Request->post['product-length-unit'])) {
                        $data['length_unit_id'] = (int)$this->Request->post['product-length-unit'];
                    } else {
                        $error = true;
                        $messages[] = $this->Language->get('error_product_length_unit_not_selected');
                    }

                    if (!empty($this->Request->post['product-weight'])) {
                        $data['weight_value'] = (int)$this->Request->post['product-weight'];
                    } else {
                        $data['weight_value'] = 0;
                    }

                    if (!empty($this->Request->post['product-length'])) {
                        $data['length_value'] = (int)$this->Request->post['product-length'];
                    } else {
                        $data['length_value'] = 0;
                    }

                    if (!empty($this->Request->post['product-width'])) {
                        $data['width_value'] = (int)$this->Request->post['product-width'];
                    } else {
                        $data['width_value'] = 0;
                    }

                    if (!empty($this->Request->post['product-height'])) {
                        $data['height_value'] = (int)$this->Request->post['product-height'];
                    } else {
                        $data['height_value'] = 0;
                    }

                    if (!empty($this->Request->post['product-sort-order']) && $this->Request->post['product-sort-order']) {
                        $data['sort_order'] = (int)$this->Request->post['product-sort-order'];
                    } else {
                        $data['sort_order'] = 0;
                    }
                    if (!empty($this->Request->post['product-manufacturer-id']) && $Manufacturer->getManufacturerByID((int)$this->Request->post['product-manufacturer-id'])) {
                        $data['manufacturer_id'] = (int)$this->Request->post['product-manufacturer-id'];
                    } else {
                        $error = true;
                        $messages[] = $this->Language->getLanguages('error_product_manufacturer_empty');
                    }
                    if (!isset($this->Request->post['product-categories']) || count($this->Request->post['product-categories']) == 0) {
                        $error = true;
                        $messages[] = $this->Language->getLanguages('error_product_category_empty');
                    } else {
                        $data['categories_id'] = [];
                        foreach ($this->Request->post['product-categories'] as $productCategory) {
                            if ((int)$productCategory && $Category->getCategoryByID((int)$productCategory)) {
                                $data['categories_id'][] = (int)$productCategory;
                            } else {
                                $error = true;
                                $messages[] = $this->Language->get('error_product_category_id_invalid');
                            }
                        }
                    }
                    $data['filters_id'] = [];
                    if (isset($this->Request->post['product-filters']) && count($this->Request->post['product-filters']) > 0) {
                        foreach ($this->Request->post['product-filters'] as $productFilter) {
                            if ((int)$productFilter && $Filter->getFilterByID((int)$productFilter)) {
                                $data['filters_id'][] = (int)$productFilter;
                            } else {
                                $error = true;
                                $messages[] = $this->Language->get('error_product_filter_id_invalid');
                            }
                        }
                    }
                    $data['attributes'] = [];
                    if (isset($this->Request->post['product-attribute']) && count($this->Request->post['product-attribute']) > 0) {
                        foreach ($this->Request->post['product-attribute'] as $productAttribute) {
                            if ((int)$productAttribute['attribute-id'] && $Attribute->getAttributeByID((int)$productAttribute['attribute-id'])) {
                                $attribute_names = [];
                                foreach ($this->Language->getLanguages() as $language) {
                                    if (!empty($productAttribute['attribute-value-' . $language['language_id']])) {
                                        $attribute_names[$language['language_id']] = $productAttribute['attribute-value-' . $language['language_id']];
                                    }
                                }
                                if (!empty($productAttribute['attribute-value-' . $languageDefaultID])) {
                                    $data['attributes'][] = array(
                                        'attribute_id' => (int)$productAttribute['attribute-id'],
                                        'attribute_values' => $attribute_names,
                                    );
                                } else {
                                    $error = true;
                                    $messages[] = $this->Language->get('error_product_attribute_value_empty');
                                }
                            } else {
                                $error = true;
                                $messages[] = $this->Language->get('error_product_attribute_id_invalid');
                            }
                        }
                    }
                    $data['options'] = [];
                    if (isset($this->Request->post['product-option']) && count($this->Request->post['product-option']) > 0) {
                        foreach ($this->Request->post['product-option'] as $productOption) {
                            if ((int)$productOption['option-id'] && $optionGroupTotal = $Option->getOptionByID((int)$productOption['option-id'])) {
                                $optionDetail = [];
                                $optionDetail['option_id'] = (int)$productOption['option-id'];
                                if (isset($productOption['option-is-required']) && $productOption['option-is-required'] == 1) {
                                    $optionDetail['is_required'] = 1;
                                } else {
                                    $optionDetail['is_required'] = 0;
                                }
                                if (isset($productOption['option-items']) && count($productOption['option-items']) > 0) {
                                    $optionItemsID = [];
                                    foreach ($optionGroupTotal['options'] as $option) {
                                        $optionItemsID[] = $option['option_value_id'];
                                    }
                                    $optionItemsDetail = [];
                                    foreach ($productOption['option-items'] as $optionItem) {
                                        if (isset($optionItem['option-item-id']) && in_array($optionItem['option-item-id'], $optionItemsID)) {
                                            $array = [];
                                            $array['option_item_id'] = $optionItem['option-item-id'];
                                            if (isset($optionItem['quantity']) && ((int)$optionItem['quantity']) > 0) {
                                                $array['quantity'] = (int)$optionItem['quantity'];
                                            } else {
                                                $array['quantity'] = 0;
                                            }
                                            if (isset($optionItem['effect-on-stock']) && $optionItem['effect-on-stock'] == 1) {
                                                $array['effect-on-stock'] = 1;
                                            } else {
                                                $array['effect-on-stock'] = 0;
                                            }
                                            if (isset($optionItem['price-sign']) && $optionItem['price-sign'] == '+') {
                                                $array['price-sign'] = '+';
                                            } else {
                                                $array['price-sign'] = '-';
                                            }
                                            if (isset($optionItem['price']) && ((int)$optionItem['price']) > 0) {
                                                $array['price'] = (int)$optionItem['price'];
                                            } else {
                                                $array['price'] = 0;
                                            }
                                            if (isset($optionItem['weight']) && ((int)$optionItem['weight']) > 0) {
                                                $array['weight'] = (int)$optionItem['weight'];
                                            } else {
                                                $array['weight'] = 0;
                                            }
                                            if (isset($optionItem['weight-sign']) && $optionItem['weight-sign'] == '+') {
                                                $array['weight-sign'] = '+';
                                            } else {
                                                $array['weight-sign'] = '-';
                                            }
                                            $optionItemsDetail[] = $array;
                                        } else {
                                            $error = true;
                                            $messages[] = $this->Language->get('error_product_option_item_id_invalid');
                                            break;
                                        }
                                    }
                                    $optionDetail['option_items'] = $optionItemsDetail;
                                } else {
                                    $error = true;
                                    $messages[] = $this->Language->get('error_product_option_items_empty');
                                    break;
                                }
                                $data['options'][] = $optionDetail;
                            } else {
                                $error = true;
                                $messages[] = $this->Language->get('error_product_option_id_invalid');
                                break;
                            }
                        }
                    }
                    $data['special_price'] = [];
                    $priority = [];
                    if (isset($this->Request->post['product-special']) && count($this->Request->post['product-special']) > 0) {
                        foreach ($this->Request->post['product-special'] as $productSpecial) {
                            $specialDetail = [];
                            if (isset($productSpecial['priority']) && $productSpecial['priority'] > 0 && !in_array($productSpecial['priority'], $priority)) {
                                $priority[] = $productSpecial['priority'];
                                $specialDetail['priority'] = +$productSpecial['priority'];
                            } else {
                                $error = true;
                                $messages[] = $this->Language->get('error_product_special_priority_invalid');
                            }

                            if (isset($productSpecial['price']) && $productSpecial['price'] > 0) {
                                if (isset($data['product_price']) && $data['product_price'] > $productSpecial['price']) {
                                    $specialDetail['price'] = +$productSpecial['price'];
                                } else {
                                    $error = true;
                                    $messages[] = $this->Language->get('error_product_special_price_invalid');
                                }
                            } else {
                                $error = true;
                                $messages[] = $this->Language->get('error_product_special_price_invalid');
                            }
                            if (!empty($productSpecial['start_date'])) {
                                $parts = explode('/', $productSpecial['start_date']);
                                if (count($parts) == 3 && jcheckdate($parts[1], $parts[2], $parts[0])) {
                                    $time = jmktime(0, 0, 0, $parts[1], $parts[2], $parts[0]);
                                    $specialDetail['start_date'] = $time;
                                } else {
                                    $error = true;
                                    $messages[] = $this->Language->get('error_product_special_start_date_invalid');
                                }
                            } else {
                                $error = true;
                                $messages[] = $this->Language->get('error_product_special_start_date_invalid');
                            }
                            if (!empty($productSpecial['end_date'])) {
                                $parts = explode('/', $productSpecial['end_date']);
                                if (count($parts) == 3 && jcheckdate($parts[1], $parts[2], $parts[0])) {
                                    $time = jmktime(0, 0, 0, $parts[1], $parts[2], $parts[0]);
                                    $specialDetail['end_date'] = $time;
                                } else {
                                    $error = true;
                                    $messages[] = $this->Language->get('error_product_special_end_date_invalid');
                                }
                            } else {
                                $error = true;
                                $messages[] = $this->Language->get('error_product_special_end_date_invalid');
                            }
                            $data['special_price'][] = $specialDetail;
                        }
                    }
                    $data['images'] = [];
                    if (isset($this->Request->post['product-image']) && count($this->Request->post['product-image']) > 0) {
                        $imageSortOrder = [];
                        foreach ($this->Request->post['product-image'] as $productImage) {
                            if (isset($productImage['src']) && Validate::urlValid($productImage['src'])) {
                                $i = 0;
                                do {
                                    $i++;
                                    $sort_order = $i;
                                } while (in_array($i, $imageSortOrder));
                                $imageSortOrder[] = $sort_order;
                                $data['images'][] = array(
                                    'src' => $productImage['src'],
                                    'sort_order' => $sort_order,
                                );
                                if (isset($productImage['default']) && $productImage['default'] == "true") {
                                    $data['image'] = $productImage['src'];
                                }

                            }
                        }
                    }
                    if (!isset($data['image'])) {
                        $error = true;
                        $messages[] = $this->Language->get('error_product_default_image_not_selected');
                    }
                    $json = [];
                    if (!$error) {
                        $delete = [];
                        $add = [];
                        if ($data['sort_order'] == 0) {
                            $rows = $Product->getProducts(array(
                                'sort' => 'sort_order',
                                'order' => 'DESC',
                                'language_id' => $languageDefaultID
                            ));
                            $oldSortOrder = count($rows) > 0 ? $rows[0]['sort_order'] : 0;
                            $data['sort_order'] = $oldSortOrder + 1;
                        }
                        if ($productInfo['price'] == $data['product_price']) {
                            unset($data['product_price']);
                        }
                        if ($productInfo['sort_order'] == $data['sort_order']) {
                            unset($data['sort_order']);
                        }
                        if ($productInfo['date_available_time'] == $data['product_date']) {
                            unset($data['product_date']);
                        }
                        if ($productInfo['quantity'] == $data['quantity']) {
                            unset($data['quantity']);
                        }
                        if ($productInfo['minimum'] == $data['product_quantity_per_order']) {
                            unset($data['product_quantity_per_order']);
                        }
                        if ($productInfo['stock_status_id'] == $data['stock_status']) {
                            unset($data['stock_status']);
                        }
                        if ($productInfo['weight'] == $data['weight_value']) {
                            unset($data['weight_value']);
                        }
                        if ($productInfo['length_id'] == $data['length_unit_id']) {
                            unset($data['length_unit_id']);
                        }
                        if ($productInfo['weight_id'] == $data['weight_unit_id']) {
                            unset($data['weight_unit_id']);
                        }
                        if ($productInfo['width'] == $data['width_value']) {
                            unset($data['width_value']);
                        }
                        if ($productInfo['height'] == $data['height_value']) {
                            unset($data['height_value']);
                        }
                        if ($productInfo['length'] == $data['length_value']) {
                            unset($data['length_value']);
                        }
                        if ($productInfo['manufacturer_id'] == $data['manufacturer_id']) {
                            unset($data['manufacturer_id']);
                        }
                        if ($productInfo['image'] == $data['image']) {
                            unset($data['image']);
                        }
                        foreach ($this->Language->getLanguages() as $language) {
                            if(isset($data['product_descriptions'][$language['language_id']]['name'])
                                && isset($productInfo['product_descriptions'][$language['language_id']]['name'])
                                && $data['product_descriptions'][$language['language_id']]['name'] == $productInfo['product_descriptions'][$language['language_id']]['name'] ) {
                                unset($data['product_descriptions'][$language['language_id']]['name']);
                            }else if(isset($productInfo['product_descriptions'][$language['language_id']]['name'])
                                && !isset($data['product_descriptions'][$language['language_id']]['name'])) {
                                $delete['product_descriptions'][$language['language_id']]['name'] = $productInfo['product_descriptions'];
                            }else if (!isset($productInfo['product_descriptions'][$language['language_id']]['name'])
                                && isset($data['product_descriptions'][$language['language_id']]['name'])) {
                                $add['product_descriptions'][$language['language_id']]['name'] =  $data['product_descriptions'][$language['language_id']]['name'];
                                unset($data['product_descriptions'][$language['language_id']]['name']);
                            }
                            if(isset($data['product_descriptions'][$language['language_id']])
                                && isset($productInfo['product_descriptions'][$language['language_id']]['description'])
                                && $data['product_descriptions'][$language['language_id']]['description'] == $productInfo['product_descriptions'][$language['language_id']]['description'] ) {
                                unset($data['product_descriptions'][$language['language_id']]['description']);
                            }else if(isset($productInfo['product_descriptions'][$language['language_id']]['description'])
                                && !isset($data['product_descriptions'][$language['language_id']]['description'])) {
                                $delete['product_descriptions'][$language['language_id']]['description'] = $productInfo['product_descriptions'];
                            }else if (!isset($productInfo['product_descriptions'][$language['language_id']]['description'])
                                && isset($data['product_descriptions'][$language['language_id']]['description'])) {
                                $add['product_descriptions'][$language['language_id']]['description'] =  $data['product_descriptions'][$language['language_id']]['description'];
                                unset($data['product_descriptions'][$language['language_id']]['description']);
                            }

                            if(empty($data['product_descriptions'][$language['language_id']])) {
                                unset($data['product_descriptions'][$language['language_id']]);
                            }
                        }
                        $json['data'] = $data;
                        $json['add'] = $add;
                        $json['delete'] = $delete;
                        $json['productInfo'] = $productInfo;
                        if(count($data['product_descriptions']) == 0) {
                            unset($data['product_descriptions']);
                        }
                        if(count($data) > 0) {
                            $Product->editProduct($productInfo['product_id'], $data);
                        }
                        if(count($add) > 0) {
                            $Product->insertProduct($add, $productInfo['product_id']);
                        }
                        if(count($delete) > 0) {
                            $Product->deleteProduct($productInfo['product_id'], $delete);
                        }

                        $json['status'] = 1;
                        $json['messages'] = [$this->Language->get('message_success_done')];
                        $json['redirect'] = ADMIN_URL . "product/product/index?token=" . $_SESSION['token'];
                    } else {
                        $json['status'] = 0;
                        $json['messages'] = $messages;
                    }
                    $this->Response->setOutPut(json_encode($json));
                    return;
                } else {

                    $Language = $this->Language;

                    $data['Languages'] = $Language->getLanguages();
                    $data['StocksStatus'] = $Stock->getStocks();
                    $data['Weights'] = $Weight->getWeights();
                    $data['Lengths'] = $Length->getLengths();
                    $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
                    $data['ProductInfo'] = $productInfo;
//                    print_r($data['ProductInfo'])
                    $this->Response->setOutPut($this->render('product/product/edit', $data));
                    return;
                }
            }
        }
        return new Action('error/notFound', 'web');


    }

    public function delete()
    {

        if (!empty($this->Request->post['products_id'])) {
            $json = [];
            /** @var Product $Product */
            $Product = $this->load("Product", $this->registry);
            $error = false;
            $this->Database->db->beginTransaction();
            foreach ($this->Request->post['products_id'] as $product_id) {
                $product = $Product->getProduct((int)$product_id);
                if ((int)$product_id && $product) {
                    $Product->deleteProduct($product_id);
                } else {
                    $error = true;
                }
            }
            if ($error) {
                $this->Database->db->rollBack();
                $json['status'] = 0;
                $json['messages'] = [$this->Language->get('error_done')];
            } else {
                $this->Database->db->commit();
                $json['status'] = 1;
                $json['messages'] = [$this->Language->get('message_success_done')];
                $data = [];
                if (isset($this->Request->get['page'])) {
                    $page = (int)$this->Request->get['page'] > 0 ? (int)$this->Request->get['page'] : 1;
                } else {
                    $page = 1;
                }
                $products = $Product->getProducts(array(
                    'start' => ($page - 1) * $this->Config->get('config_limit_admin'),
                    'limit' => $this->Config->get('config_limit_admin'),
                    'order' => 'DESC'
                ));
                $data['Products'] = [];
                /** @var Image $Image */
                $Image = $this->load("Image", $this->registry);
                foreach ($products as $product) {
                    if (is_file(ASSETS_PATH . DS . substr($product['image'], strlen(ASSETS_URL)))) {
                        $image = ASSETS_URL . $Image->resize(substr($product['image'], strlen(ASSETS_URL)), 200, 200);
                    } else {
                        $image = ASSETS_URL . $Image->resize('img/no-image.jpeg', 200, 200);
                    }
                    $product_specials = $Product->getProductSpecials($product['product_id']);
                    $special = '';
                    foreach ($product_specials as $product_special) {
                        if ($product_special['date_start'] < time() && $product_special['date_end'] > time()) {
                            $special = $product_special['price'];
                        }
                    }
                    $data['Products'][] = array(
                        'product_id' => $product['product_id'],
                        'image' => $image,
                        'name' => $product['name'],
                        'price' => $product['price'],
                        'special' => $special,
                        'status' => $product['status'],
                        'quantity' => $product['quantity'],
                        'sort_order' => $product['sort_order'],
                    );
                }
                $Language = $this->Language;
                $data['Languages'] = $Language->getLanguages();
                $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
                $json['data'] = $this->render('product/product/product_table', $data);
            }

            $this->Response->setOutPut(json_encode($json));
            return;
        }
        return new Action("error/notFound", 'web');

    }

    public function status()
    {
        if (isset($this->Request->post['product_id']) && isset($this->Request->post['product_status'])) {
            $product_id = (int)$this->Request->post['product_id'];
            $product_status = (int)$this->Request->post['product_status'];
            /** @var Product $Product */
            $Product = $this->load('Product', $this->registry);
            $product = $Product->getProduct($product_id);
            $json = [];
            if ($product_id && $product) {
                $Product->editProduct($product_id, array(
                    'status' => $product_status
                ));
                $json['status'] = 1;
                $json['messages'] = [$this->Language->get('message_success_done')];
            } else {
                $json['status'] = 0;
                $json['messages'] = [$this->Language->get('error_done')];
            }
            $this->Response->setOutPut(json_encode($json));
            return;
        }
        return new Action('error/notFound', 'web');
    }
}