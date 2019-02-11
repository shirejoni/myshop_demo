<?php

namespace App\Admin\Controller;

use App\Lib\Database;
use App\Lib\Request;
use App\Lib\Response;
use App\Model\Filter;
use App\Model\Language;
use App\System\Controller;

/**
 * @property Language Language
 * @property Response Response
 * @property Request Request
 * @property Filter Filter
 * @property Database Database
 */
class ControllerProductFilter extends Controller {

    public function index() {
        $data = [];
        $language_id = $this->Language->getLanguageID();
        /** @var Filter $Filter */
        $Filter = $this->load("Filter", $this->registry);
        $data['FilterGroups'] = $Filter->getFilterGroups(array(
            'language_id'   => $language_id
        ));
        $Language = $this->Language;
        $data['Languages'] = $Language->getLanguages();
        $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
        $this->Response->setOutPut($this->render("product/filter/index", $data));
    }

    public function add() {
        $data = array();
        $error = false;
        $messages = [];
        if(!empty($this->Request->post['filter-post'])) {

            $Language = $this->Language;
            $languages = $Language->getLanguages();
            $languageDefaultID = $Language->getDefaultLanguageID();
            $filters = [];
            foreach ($languages as $language) {
                if(!empty($this->Request->post['filter-group-name-' . $language['language_id']])) {
                    $data['filter_group_names'][$language['language_id']] = $this->Request->post['filter-group-name-' . $language['language_id']];
                }
                if(isset($this->Request->post['filter-name-' . $language['language_id']])) {
                    foreach ($this->Request->post['filter-name-' . $language['language_id']] as $sort_order => $filter_name) {
                        $filters[$sort_order][$language['language_id']] = $filter_name;
                    }
                }
            }
            unset($filters[0]);
            foreach ($filters as $filter) {
                if(!isset($filter[$languageDefaultID])) {
                    $error = true;
                    $messages[] = $this->Language->get('error_filter_not_enough');
                }
            }
            $data['filters'] = $filters;
            if(empty($data['filter_group_names'][$languageDefaultID])) {
                $error = true;
                $messages[] = $this->Language->get('error_filter_group');
            }

            if(!empty($this->Request->post['filter-sort-order'])) {
                $data['sort_order'] = (int) $this->Request->post['filter-sort-order'];
            }else {
                $data['sort_order'] = 0;
            }

            $json = [];
            if(!$error) {
                /** @var Filter $Filter */
                $Filter = $this->load("Filter", $this->registry);
                if($data['sort_order'] == 0) {
                    $rows = $Filter->getFilterGroups(array(
                        'sort'  => 'sort_order',
                        'order' => 'DESC',
                        'language_id'   => $languageDefaultID
                    ));
                    $oldSortOrder = count($rows) > 0 ? $rows[0]['sort_order'] : 0;
                    $data['sort_order'] = $oldSortOrder + 1;
                }
                $filter_group_id = $Filter->insertFilterGroup($data);
                $Filter->insertFilters($filter_group_id, $data['filters']);
                $json['status'] = 1;
                $this->Response->endResponse();
                $json['process_time'] = $this->Response->getProcessTime();
                $json['messages'] = [$this->Language->get('message_success_done')];
                $json['redirect'] = ADMIN_URL . "product/filter/index?token=" . $_SESSION['token'];
                $json['data'] = $data;
            }else {
                $json['status'] = 0;
                $json['messages'] = $messages;
            }
            $this->Response->setOutPut(json_encode($json));
        }else {
            $Language = $this->Language;
            $data['Languages'] = $Language->getLanguages();
            $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
            $this->Response->setOutPut($this->render('product/filter/add', $data));
        }
    }

    public function edit() {
        $data = array();
        $error = false;
        $messages = [];
        if(isset($this->Request->get[0])) {
            $filterGroup = (int) $this->Request->get[0];
            /** @var Filter $Filter */
            $Filter = $this->load("Filter", $this->registry);
            $filterGroupTotal = $Filter->getFilterGroupByID($filterGroup, "all");
            $filterGroupInfo = [];
            $Language = $this->Language;
            foreach ($filterGroupTotal as $filterGroup) {
                $filterGroupInfo['filter_group_names'][$filterGroup['language_id']] = $filterGroup['name'];
            }
            $filterGroupInfo['sort_order'] = $filterGroupTotal[0]['sort_order'];
            $filterGroupInfo['filter_group_id'] = $filterGroupTotal[0]['filter_group_id'];

            foreach ($Filter->getFilters() as $filter) {
                $filterGroupInfo['filters'][$filter['language_id']][$filter['filter_id']] = $filter;
            }

            if($filterGroup && $filterGroupInfo) {
                if(!empty($this->Request->post['filter-post'])) {

                    $Language = $this->Language;
                    $languages = $Language->getLanguages();
                    $languageDefaultID = $Language->getDefaultLanguageID();
                    $filters = [];
                    foreach ($languages as $language) {
                        if(!empty($this->Request->post['filter-group-name-' . $language['language_id']])) {
                            $data['filter_group_names'][$language['language_id']] = $this->Request->post['filter-group-name-' . $language['language_id']];
                        }
                        if(isset($this->Request->post['filter-name-' . $language['language_id']])) {
                            foreach ($this->Request->post['filter-name-' . $language['language_id']] as $sort_order => $filter_name) {
                                $filters[$sort_order][$language['language_id']] = $filter_name;
                            }
                        }
                    }
                    unset($filters[0]);
                    foreach ($filters as $filter) {
                        if(!isset($filter[$languageDefaultID])) {
                            $error = true;
                            $messages[] = $this->Language->get('error_filter_not_enough');
                        }
                    }
                    $data['filters'] = $filters;
                    if(empty($data['filter_group_names'][$languageDefaultID])) {
                        $error = true;
                        $messages[] = $this->Language->get('error_filter_group');
                    }

                    if(!empty($this->Request->post['filter-sort-order'])) {
                        $data['sort_order'] = (int) $this->Request->post['filter-sort-order'];
                    }else {
                        $data['sort_order'] = 0;
                    }

                    $json = [];
                    if(!$error) {
                        $delete = [];
                        $add = [];
                        if($data['sort_order'] == 0) {
                            $rows = $Filter->getFilterGroups(array(
                                'sort'  => 'sort_order',
                                'order' => 'DESC',
                                'language_id'   => $languageDefaultID
                            ));
                            $oldSortOrder = count($rows) > 0 ? $rows[0]['sort_order'] : 0;
                            $data['sort_order'] = $oldSortOrder + 1;
                        }

                        if($filterGroupInfo['sort_order'] == $data['sort_order']) {
                            unset($data['sort_order']);
                        }
                        foreach ($languages as $language) {
                            if(isset($data['filter_group_names'][$language['language_id']])
                                && isset($filterGroupInfo['filter_group_names'][$language['language_id']])
                                && $data['filter_group_names'][$language['language_id']] == $filterGroupInfo['filter_group_names'][$language['language_id']] ) {
                                unset($data['filter_group_names'][$language['language_id']]);
                            }else if(isset($filterGroupInfo['filter_group_names'][$language['language_id']])
                                && !isset($data['filter_group_names'][$language['language_id']])) {
                                $delete['filter_group_names'][$language['language_id']] = $filterGroupInfo['manufacturer_id'];
                            }else if (!isset($filterGroupInfo['filter_group_names'][$language['language_id']])
                                && isset($data['filter_group_names'][$language['language_id']])) {
                                $add['filter_group_names'][$language['language_id']] =  $data['filter_group_names'][$language['language_id']];
                                unset($data['filter_group_names'][$language['language_id']]);
                            }
                        }
                        if(count($data['filter_group_names']) == 0) {
                            unset($data['filter_group_names']);
                        }
                        if(count($data) > 0) {
                            $Filter->editFilterGroup($filterGroupInfo['filter_group_id'], $data);
                        }
                        if(count($add) > 0) {
                            $Filter->insertFilterGroup($add, $filterGroupInfo['filter_group_id']);
                        }
                        if(count($delete) > 0) {
                            $Filter->deleteFilterGroup($filterGroupInfo['filter_group_id'], $delete);
                        }

                        $json['status'] = 1;
                        $json['messages'] = [$this->Language->get('message_success_done')];
                        $json['redirect'] = ADMIN_URL . "product/filter/index?token=" . $_SESSION['token'];
                    }else {
                        $json['status'] = 0;
                        $json['messages'] = $messages;
                    }
                    $this->Response->setOutPut(json_encode($json));
                    return;
                }else {

                    $data['Languages'] = $Language->getLanguages();
                    $data['FilterGroupLanguages'] = [];
                    foreach ($Language->getLanguages() as $language) {
                        $data['FilterGroupLanguages'][] = array(
                            'language_id' => $language['language_id'],
                            'language_name' => $language['name'],
                            'language_code' => $language['code'],
                            'filter_group_sort_order' => $filterGroupInfo['sort_order'],
                            'filter_group_id' => $filterGroupInfo['filter_group_id'],
                            'filters' => isset($filterGroupInfo['filters'][$language['language_id']]) ? $filterGroupInfo['filters'][$language['language_id']] : '',
                            'filter_group_name' => isset($filterGroupInfo['filter_group_names'][$language['language_id']]) ? $filterGroupInfo['filter_group_names'][$language['language_id']] : '',
                        );
                    }
                    $data['FilterGroup'] = $filterGroupInfo;
                    $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
                    $this->Response->setOutPut($this->render('product/filter/edit', $data));
                    return;
                }
            }
        }
        return new Action('error/notFound', 'web');

    }

    public function delete() {
        if(!empty($this->Request->post['filtergroups_id'])) {
            $json = [];
            /** @var Filter $Filter */
            $Filter = $this->load("Filter", $this->registry);
            $error = false;
            $this->Database->db->beginTransaction();
            foreach ($this->Request->post['filtergroups_id'] as $filter_group_id) {
                $manufacturer = $Filter->getFilterGroupByID((int) $filter_group_id);
                if((int) $filter_group_id && $manufacturer) {
                    $Filter->deleteFilterGroup($filter_group_id);
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
                $data['FilterGroups'] = $Filter->getFilterGroups(array(
                    'language_id'   => $language_id
                ));
                $Language = $this->Language;
                $data['Languages'] = $Language->getLanguages();
                $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
                $json['data'] = $this->render("product/filter/filtergroups_table", $data);
            }

            $this->Response->setOutPut(json_encode($json));
            return;
        }
        return new Action("error/notFound", 'web');
    }

}