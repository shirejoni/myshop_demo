<?php

namespace App\Admin\Controller;

use App\Lib\Config;
use App\Lib\Database;
use App\Lib\Request;
use App\Lib\Response;
use App\Model\Category;
use App\Model\Filter;
use App\Model\Language;
use App\System\Controller;

/**
 * @property Response Response
 * @property Request Request
 * @property Database Database
 * @property Language Language
 * @property Config Config
 */
class ControllerProductCategory extends Controller {

    public function index() {
        $data = [];
        $language_id = $this->Language->getLanguageID();
        /** @var Category $Category */
        $Category = $this->load("Category", $this->registry);
        $data['CategoriesComp'] = $Category->getCategoriesComplete(array(
            'language_id'   => $language_id,
            'order'          => "DESC"
        ));

        foreach ($data['CategoriesComp'] as $index => $value) {

            $data['CategoriesComp'][$index]['full_name'] = implode(' > ', explode(',', $value['full_name']));
        }
        $Language = $this->Language;
        $data['Languages'] = $Language->getLanguages();
        $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
        $this->Response->setOutPut($this->render("product/category/index", $data));
    }

    public function add() {
        $data = array();
        $error = false;
        $messages = [];
        if(!empty($this->Request->post['category-post'])) {
            /** @var Category $Category */
            $Category = $this->load("Category", $this->registry);
            $Language = $this->Language;
            $languages = $Language->getLanguages();
            $languageDefaultID = $Language->getDefaultLanguageID();
            foreach ($languages as $language) {
                if(!empty($this->Request->post['category-name-' . $language['language_id']])) {
                    $data['category_names'][$language['language_id']] = $this->Request->post['category-name-' . $language['language_id']];
                }
            }
            if(empty($data['category_names'][$languageDefaultID])) {
                $error = true;
                $messages[] = $this->Language->get('error_category_name');
            }

            if(!empty($this->Request->post['category-sort-order'])) {
                $data['sort_order'] = (int) $this->Request->post['category-sort-order'];
            }else {
                $data['sort_order'] = 0;
            }
            /** @var Filter $Filter */
            $Filter = $this->load("Filter", $this->registry);
            if(!empty($this->Request->post['category-filters'])) {
                foreach ($this->Request->post['category-filters'] as $filter_id) {
                    $filter = $Filter->getFilterByID($filter_id);
                    if(!$filter) {
                        $error = true;
                        $messages = $this->Language->get('error_filter_id_invalid');
                    }else {
                        $data['filters'][] = $filter['filter_id'];
                    }
                }
            }else {
                $data['filters'] = [];
            }
            if(!empty($this->Request->post['category-parent'])
                && (int) $this->Request->post['category-parent'] != 0) {
                $categoryParent = $Category->getCategoryByID((int) $this->Request->post['category-parent']);

                $data['category_parent_id'] = (int) $this->Request->post['category-parent'];
                $data['level'] = $categoryParent['level'] + 1;

            }else {
                $data['category_parent_id'] = 0;
                $data['level'] = 0;
            }


            $json = [];
            if(!$error) {
                if($data['sort_order'] == 0) {
                    $rows = $Category->getCategories(array(
                        'sort'  => 'sort_order',
                        'order' => 'DESC',
                        'language_id'   => $languageDefaultID
                    ));
                    $oldSortOrder = count($rows) > 0 ? $rows[0]['sort_order'] : 0;
                    $data['sort_order'] = $oldSortOrder + 1;
                }
                $Category->insertCategory($data);
                $json['status'] = 1;
                $this->Response->endResponse();
                $json['process_time'] = $this->Response->getProcessTime();
                $json['messages'] = [$this->Language->get('message_success_done')];
                $json['redirect'] = ADMIN_URL . "product/category/index?token=" . $_SESSION['token'];
            }else {
                $json['status'] = 0;
                $json['messages'] = $messages;
            }
            $this->Response->setOutPut(json_encode($json));
        }else {
            $Language = $this->Language;
            $data['Languages'] = $Language->getLanguages();
            $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
            $this->Response->setOutPut($this->render('product/category/add', $data));
        }

    }

    public function getcategories() {
        $data = [];
        $language_id = $this->Language->getLanguageID();
        /** @var Category $Category */
        $Category = $this->load("Category", $this->registry);
        $option = array(
            'language_id'   => $language_id
        );
        if(!empty($this->Request->post['s'])) {
            $option['filter_name']   = trim($this->Request->post['s']);
        }
        $data['Categories'] = $Category->getCategories($option);
        $json = array(
            'status'    => 1,
            'categories'   => $data['Categories']
        );
        $this->Response->setOutPut(json_encode($json));
    }

    public function delete() {
        if(!empty($this->Request->post['categories_id'])) {
            $json = [];
            /** @var Category $Category */
            $Category = $this->load("Category", $this->registry);
            $error = false;
            $this->Database->db->beginTransaction();
            foreach ($this->Request->post['categories_id'] as $category_id) {
                $category = $Category->getCategoryByID((int) $category_id);
                if((int) $category_id && $category) {
                    $Category->deleteCategory($category_id);
                }else {
                    $error = true;
                }
            }
            if($error) {
                $this->Database->db->rollBack();
                $json['status'] = 0;
                $json['messages'] = [$this->Language->get('error_done')];
            }else {
                $this->Database->db->commit();
                $json['status'] = 1;
                $json['messages'] = [$this->Language->get('message_success_done')];
                $data = [];
                $language_id = $this->Language->getLanguageID();
                $data['CategoriesComp'] = $Category->getCategoriesComplete(array(
                    'language_id'   => $language_id,
                    'order'         => 'DESC'
                ));
                foreach ($data['CategoriesComp'] as $index => $value) {

                    $data['CategoriesComp'][$index]['full_name'] = implode(' > ', array_reverse(explode(',', $value['full_name'])));
                }
                $Language = $this->Language;
                $data['Languages'] = $Language->getLanguages();
                $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
                $json['data'] = $this->render("product/category/categories_table", $data);
            }

            $this->Response->setOutPut(json_encode($json));
            return;
        }
        return new Action("error/notFound", 'web');
    }

    public function status() {
        if(isset($this->Request->post['category_id']) && isset($this->Request->post['category_status'])) {
            $category_id = (int) $this->Request->post['category_id'];
            $category_status = (int) $this->Request->post['category_status'];
            /** @var Category $Category */
            $Category = $this->load('Category', $this->registry);
            $category = $Category->getCategoryByID($category_id);
            $json = [];
            if($category_id && $category) {
                $Category->editCategory($category_id, array(
                    'status'    => $category_status
                ));
                $json['status'] = 1;
                $json['messages'] = [$this->Language->get('message_success_done')];
            }else {
                $json['status'] = 0;
                $json['messages'] = [$this->Language->get('error_done')];
            }
            $this->Response->setOutPut(json_encode($json));
            return;
        }
        return new Action('error/notFound', 'web');
    }

    public function edit() {
        $data = array();
        $error = false;
        $messages = [];
        if(isset($this->Request->get[0])) {
            $category_id = (int) $this->Request->get[0];
            /** @var Category $Category */
            $Category = $this->load("Category", $this->registry);
            $categoryTotal = $Category->getCategoryByID($category_id, "all");
            $categoryInfo = [];
            $Language = $this->Language;

            foreach ($categoryTotal as $category) {
                if(isset($category['language_id'])) {
                    $categoryInfo['category_names'][$category['language_id']] = $category['name'];
                }
            }
            $categoryInfo['sort_order'] = $categoryTotal[0]['sort_order'];
            $categoryInfo['top'] = $categoryTotal[0]['top'];
            $categoryInfo['level'] = $categoryTotal[0]['level'];
            $categoryInfo['date_added'] = $categoryTotal[0]['date_added'];
            $categoryInfo['date_updated'] = $categoryTotal[0]['date_updated'];
            $categoryInfo['status'] = $categoryTotal[0]['status'];
            $categoryInfo['category_id'] = $categoryTotal[0]['category_id'];
            $categoryInfo['filters'] = $categoryTotal['filters'];
            if($categoryTotal[0]['parent_id'] != 0) {
                $categoryParent = $Category->getCategoryByID($categoryTotal[0]['parent_id'] );
                $categoryInfo['parent_id'] = $categoryTotal[0]['parent_id'];
                $categoryInfo['parent_name'] = $categoryParent['name'];
            }else {
                $categoryInfo['parent_id'] = 0;
                $categoryInfo['parent_name'] = '';
            }


            if($category_id && $categoryInfo) {
                if(!empty($this->Request->post['category-post'])) {
                    /** @var Category $Category */
                    $Category = $this->load("Category", $this->registry);
                    $Language = $this->Language;
                    $languages = $Language->getLanguages();
                    $languageDefaultID = $Language->getDefaultLanguageID();
                    foreach ($languages as $language) {
                        if(!empty($this->Request->post['category-name-' . $language['language_id']])) {
                            $data['category_names'][$language['language_id']] = $this->Request->post['category-name-' . $language['language_id']];
                        }
                    }
                    if(empty($data['category_names'][$languageDefaultID])) {
                        $error = true;
                        $messages[] = $this->Language->get('error_category_name');
                    }

                    if(!empty($this->Request->post['category-sort-order'])) {
                        $data['sort_order'] = (int) $this->Request->post['category-sort-order'];
                    }else {
                        $data['sort_order'] = 0;
                    }
                    /** @var Filter $Filter */
                    $Filter = $this->load("Filter", $this->registry);
                    if(!empty($this->Request->post['category-filters'])) {
                        foreach ($this->Request->post['category-filters'] as $filter_id) {
                            $filter = $Filter->getFilterByID($filter_id);
                            if(!$filter) {
                                $error = true;
                                $messages = $this->Language->get('error_filter_id_invalid');
                            }else {
                                $data['filters'][] = $filter['filter_id'];
                            }
                        }
                    }else {
                        $data['filters'] = [];
                    }
                    if(!empty($this->Request->post['category-parent'])
                        && (int) $this->Request->post['category-parent'] != 0) {
                        $categoryParent = $Category->getCategoryByID((int) $this->Request->post['category-parent']);

                        $data['category_parent_id'] = (int) $this->Request->post['category-parent'];
                        $data['level'] = $categoryParent['level'] + 1;

                    }else {
                        $data['category_parent_id'] = 0;
                        $data['level'] = 0;
                    }


                    $json = [];
                    if(!$error) {
                        $delete = [];
                        $add = [];
                        if($data['sort_order'] == 0) {
                            $rows = $Category->getCategories(array(
                                'sort'  => 'sort_order',
                                'order' => 'DESC',
                                'language_id'   => $languageDefaultID
                            ));
                            $oldSortOrder = count($rows) > 0 ? $rows[0]['sort_order'] : 0;
                            $data['sort_order'] = $oldSortOrder + 1;
                        }

                        if($categoryInfo['sort_order'] == $data['sort_order']) {
                            unset($data['sort_order']);
                        }
                        if($categoryInfo['parent_id'] == $data['category_parent_id']) {
                            unset($data['category_parent_id']);
                        }
                        foreach ($languages as $language) {
                            if(isset($data['category_names'][$language['language_id']])
                                && isset($categoryInfo['category_names'][$language['language_id']])
                                && $data['category_names'][$language['language_id']] == $categoryInfo['category_names'][$language['language_id']] ) {
                                unset($data['category_names'][$language['language_id']]);
                            }else if(isset($categoryInfo['category_names'][$language['language_id']])
                                && !isset($data['category_names'][$language['language_id']])) {
                                $delete['category_names'][$language['language_id']] = $categoryInfo['manufacturer_id'];
                            }else if (!isset($categoryInfo['category_names'][$language['language_id']])
                                && isset($data['category_names'][$language['language_id']])) {
                                $add['category_names'][$language['language_id']] =  $data['category_names'][$language['language_id']];
                                unset($data['category_names'][$language['language_id']]);
                            }
                        }
                        if(count($data['category_names']) == 0) {
                            unset($data['category_names']);
                        }
                        if(count($data) > 0) {
                            $Category->editCategory($categoryInfo['category_id'], $data);
                        }
                        if(count($add) > 0) {
                            $Category->insertCategory($add, $categoryInfo['category_id']);
                        }
                        if(count($delete) > 0) {
                            $Category->deleteCategory($categoryInfo['category_id'], $delete);
                        }

                        $json['status'] = 1;
                        $json['messages'] = [$this->Language->get('message_success_done')];
                        $json['redirect'] = ADMIN_URL . "product/category/index?token=" . $_SESSION['token'];
                    }else {
                        $json['status'] = 0;
                        $json['messages'] = $messages;
                    }
                    $this->Response->setOutPut(json_encode($json));
                    return;
                }else {

                    $data['Languages'] = $Language->getLanguages();
                    $data['CategoryLanguages'] = [];
                    foreach ($Language->getLanguages() as $language) {
                        $data['CategoryLanguages'][] = array(
                            'language_id' => $language['language_id'],
                            'language_name' => $language['name'],
                            'language_code' => $language['code'],
                            'category_sort_order' => $categoryInfo['sort_order'],
                            'category_id' => $categoryInfo['category_id'],
                            'category_name' => isset($categoryInfo['category_names'][$language['language_id']]) ? $categoryInfo['category_names'][$language['language_id']] : '',
                        );
                    }
                    if(count($categoryInfo['filters']) > 0) {
                        /** @var Filter $Filter */
                        $Filter = $this->load("Filter", $this->registry);
                        $data['CategoryFilters'] = [];
                        foreach ($categoryInfo['filters'] as $categoryFilter) {
                            $data['CategoryFilters'][] = $Filter->getFilterByID($categoryFilter['filter_id'], $this->Language->getLanguageID());
                        }
                    }else {
                        $data['CategoryFilters'] = [];
                    }
                    $data['Category'] = $categoryInfo;
                    $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
                    $this->Response->setOutPut($this->render('product/category/edit', $data));
                    return;
                }
            }
        }
        return new Action('error/notFound', 'web');

    }


}