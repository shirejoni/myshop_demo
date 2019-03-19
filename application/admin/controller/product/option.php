<?php

namespace App\Admin\Controller;

use App\Lib\Config;
use App\Lib\Database;
use App\Lib\Request;
use App\Lib\Response;
use App\Model\Language;
use App\Model\Option;
use App\System\Controller;

/**
 * @property Response Response
 * @property Request Request
 * @property Database Database
 * @property Option Option
 * @property Language Language
 * @property Config Config
 */
class ControllerProductOption extends Controller {

    public function index() {
        $data = [];
        $language_id = $this->Language->getLanguageID();
        /** @var Option $Option */
        $Option = $this->load("Option", $this->registry);
        $data['Options'] = $Option->getOptionGroups(array(
            'language_id'   => $language_id
        ));
        $Language = $this->Language;
        $data['Languages'] = $Language->getLanguages();
        $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
        $this->Response->setOutPut($this->render('product/option/index', $data));
    }

    public function add() {
        $data = array();
        $error = false;
        $messages = [];

        if(!empty($this->Request->post['option-post'])) {

            $Language = $this->Language;
            $languages = $Language->getLanguages();
            $languageDefaultID = $Language->getDefaultLanguageID();
            $options = [];
            foreach ($languages as $language) {
                if(!empty($this->Request->post['option-group-name-' . $language['language_id']])) {
                    $data['option_group_names'][$language['language_id']] = $this->Request->post['option-group-name-' . $language['language_id']];
                }
                if(isset($this->Request->post['option-name-' . $language['language_id']])) {
                    foreach ($this->Request->post['option-name-' . $language['language_id']] as $sort_order => $filter_name) {
                        $options[$sort_order][$language['language_id']] = $filter_name;
                    }
                }
            }
            unset($options[0]);
            foreach ($options as $option) {
                if(!isset($option[$languageDefaultID])) {
                    $error = true;
                    $messages[] = $this->Language->get('error_option_not_enough');
                }
            }
            foreach ($options as $sort_order => $option) {
                if(isset($this->Request->post['option-image'][$sort_order])) {
                    $data['option_image'][$sort_order] = $this->Request->post['option-image'][$sort_order];
                }else {
                    $data['option_image'][$sort_order] = '';
                }
            }
            $data['options'] = $options;
            if(empty($data['option_group_names'][$languageDefaultID])) {
                $error = true;
                $messages[] = $this->Language->get('error_option_name');
            }

            if(!empty($this->Request->post['option-sort-order'])) {
                $data['sort_order'] = (int) $this->Request->post['option-sort-order'];
            }else {
                $data['sort_order'] = 0;
            }
            if(!empty($this->Request->post['option-type']) && in_array($this->Request->post['option-type'], $this->Config->get('option_type'))) {
                $data['type'] = $this->Request->post['option-type'];
            }else {
                $error = true;
                $messages[] = $this->Language->get('error_option_type_select');
            }

            $json = [];
            if(!$error) {
                /** @var Option $Option */
                $Option = $this->load("Option", $this->registry);
                if($data['sort_order'] == 0) {
                    $rows = $Option->getOptionGroups(array(
                        'sort'  => 'sort_order',
                        'order' => 'DESC',
                        'language_id'   => $languageDefaultID
                    ));
                    $oldSortOrder = count($rows) > 0 ? $rows[0]['sort_order'] : 0;
                    $data['sort_order'] = $oldSortOrder + 1;
                }
                $option_id = $Option->insertOptionGroup($data);
                $Option->insertOptionValues($option_id, $data);
                $json['status'] = 1;
                $this->Response->endResponse();
                $json['process_time'] = $this->Response->getProcessTime();
                $json['messages'] = [$this->Language->get('message_success_done')];
                $json['redirect'] = ADMIN_URL . "product/option/index?token=" . $_SESSION['token'];
                $json['data'] = $data;
            }else {
                $json['status'] = 0;
                $json['messages'] = $messages;
            }
            $this->Response->setOutPut(json_encode($json));
        }else {
            $Language = $this->Language;
            $data['Languages'] = $Language->getLanguages();
            $data['OptionTypes'] = $this->Config->get('option_type');
            $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
            $this->Response->setOutPut($this->render('product/option/add', $data));
        }
    }

    public function delete() {
        if(!empty($this->Request->post['options_id'])) {
            $json = [];
            /** @var Option $Option */
            $Option = $this->load("Option", $this->registry);
            $error = false;
            $this->Database->db->beginTransaction();
            foreach ($this->Request->post['options_id'] as $option_id) {
                $option = $Option->getOptionByID((int) $option_id);
                if((int) $option_id && $option) {
                    $Option->deleteOption($option_id);
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
                $data['Options'] = $Option->getOptionGroups(array(
                    'language_id'   => $language_id
                ));
                $Language = $this->Language;
                $data['Languages'] = $Language->getLanguages();
                $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
                $json['data'] = $this->render("product/option/options_table", $data);
            }

            $this->Response->setOutPut(json_encode($json));
            return;
        }
        return new Action("error/notFound", 'web');
    }

    public function edit() {
        $data = array();
        $error = false;
        $messages = [];
        if(isset($this->Request->get[0])) {
            $option_id = (int) $this->Request->get[0];
            /** @var Option $Option */
            $Option = $this->load("Option", $this->registry);
            $optionTotal = $Option->getOptionByID($option_id, "all");
            $optionInfo = [];
            $Language = $this->Language;
            foreach ($optionTotal as $option) {
                $optionInfo['option_group_names'][$option['language_id']] = $option['name'];
            }
            $optionInfo['sort_order'] = $optionTotal[0]['sort_order'];
            $optionInfo['type'] = $optionTotal[0]['option_type'];
            $optionInfo['option_id'] = $optionTotal[0]['option_id'];

            foreach ($Option->getOptionValues() as $optionValue) {
                $optionInfo['options'][$optionValue['language_id']][$optionValue['option_value_id']] = $optionValue;
            }

            if($option_id && $optionInfo) {
                if(!empty($this->Request->post['option-post'])) {

                    $Language = $this->Language;
                    $languages = $Language->getLanguages();
                    $languageDefaultID = $Language->getDefaultLanguageID();
                    $options = [];
                    foreach ($languages as $language) {
                        if(!empty($this->Request->post['option-group-name-' . $language['language_id']])) {
                            $data['option_group_names'][$language['language_id']] = $this->Request->post['option-group-name-' . $language['language_id']];
                        }
                        if(isset($this->Request->post['option-name-' . $language['language_id']])) {
                            foreach ($this->Request->post['option-name-' . $language['language_id']] as $sort_order => $filter_name) {
                                $options[$sort_order][$language['language_id']] = $filter_name;
                            }
                        }
                    }
                    unset($options[0]);
                    foreach ($options as $option) {
                        if(!isset($option[$languageDefaultID])) {
                            $error = true;
                            $messages[] = $this->Language->get('error_option_not_enough');
                        }
                    }
                    foreach ($options as $sort_order => $option) {
                        if(isset($this->Request->post['option-image'][$sort_order])) {
                            $data['option_image'][$sort_order] = $this->Request->post['option-image'][$sort_order];
                        }else {
                            $data['option_image'][$sort_order] = '';
                        }
                    }
                    $data['options'] = $options;
                    if(empty($data['option_group_names'][$languageDefaultID])) {
                        $error = true;
                        $messages[] = $this->Language->get('error_option_name');
                    }

                    if(!empty($this->Request->post['option-sort-order'])) {
                        $data['sort_order'] = (int) $this->Request->post['option-sort-order'];
                    }else {
                        $data['sort_order'] = 0;
                    }
                    if(!empty($this->Request->post['option-type']) && in_array($this->Request->post['option-type'], $this->Config->get('option_type'))) {
                        $data['type'] = $this->Request->post['option-type'];
                    }else {
                        $error = true;
                        $messages[] = $this->Language->get('error_option_type_select');
                    }

                    $json = [];
                    if(!$error) {
                        $delete = [];
                        $add = [];
                        if($data['sort_order'] == 0) {
                            $rows = $Option->getOptionGroups(array(
                                'sort'  => 'sort_order',
                                'order' => 'DESC',
                                'language_id'   => $languageDefaultID
                            ));
                            $oldSortOrder = count($rows) > 0 ? $rows[0]['sort_order'] : 0;
                            $data['sort_order'] = $oldSortOrder + 1;
                        }

                        if($optionInfo['sort_order'] == $data['sort_order']) {
                            unset($data['sort_order']);
                        }
                        foreach ($languages as $language) {
                            if(isset($data['option_group_names'][$language['language_id']])
                                && isset($optionInfo['option_group_names'][$language['language_id']])
                                && $data['option_group_names'][$language['language_id']] == $optionInfo['option_group_names'][$language['language_id']] ) {
                                unset($data['option_group_names'][$language['language_id']]);
                            }else if(isset($optionInfo['option_group_names'][$language['language_id']])
                                && !isset($data['option_group_names'][$language['language_id']])) {
                                $delete['option_group_names'][$language['language_id']] = $optionInfo['manufacturer_id'];
                            }else if (!isset($optionInfo['option_group_names'][$language['language_id']])
                                && isset($data['option_group_names'][$language['language_id']])) {
                                $add['option_group_names'][$language['language_id']] =  $data['option_group_names'][$language['language_id']];
                                unset($data['option_group_names'][$language['language_id']]);
                            }
                        }
                        if(count($data['option_group_names']) == 0) {
                            unset($data['option_group_names']);
                        }
                        if(count($data) > 0) {
                            $Option->editOption($optionInfo['option_id'], $data);
                        }
                        if(count($add) > 0) {
                            $Option->insertOptionGroup($add, $optionInfo['option_id']);
                        }
                        if(count($delete) > 0) {
                            $Option->deleteOption($optionInfo['option_id'], $delete);
                        }

                        $json['status'] = 1;
                        $json['messages'] = [$this->Language->get('message_success_done')];
                        $json['redirect'] = ADMIN_URL . "product/option/index?token=" . $_SESSION['token'];
                    }else {
                        $json['status'] = 0;
                        $json['messages'] = $messages;
                    }
                    $this->Response->setOutPut(json_encode($json));
                    return;
                }else {

                    $data['Languages'] = $Language->getLanguages();
                    $data['OptionLanguages'] = [];
                    foreach ($Language->getLanguages() as $language) {
                        $data['OptionLanguages'][] = array(
                            'language_id' => $language['language_id'],
                            'language_name' => $language['name'],
                            'language_code' => $language['code'],
                            'option_sort_order' => $optionInfo['sort_order'],
                            'option_id' => $optionInfo['option_id'],
                            'options' => isset($optionInfo['options'][$language['language_id']]) ? $optionInfo['options'][$language['language_id']] : '',
                            'option_name' => isset($optionInfo['option_group_names'][$language['language_id']]) ? $optionInfo['option_group_names'][$language['language_id']] : '',
                        );
                    }
                    $data['Options'] = $optionInfo;
                    $data['OptionTypes'] = $this->Config->get('option_type');
                    $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
                    $this->Response->setOutPut($this->render('product/option/edit', $data));
                    return;
                }
            }
        }
        return new Action('error/notFound', 'web');
    }

    public function getoptions()
    {
        $data = [];
        $language_id = $this->Language->getLanguageID();
        /** @var Option $Option */
        $Option = $this->load("Option", $this->registry);
        $option = array(
            'language_id'   => $language_id
        );
        if(!empty(trim($this->Request->post['s']))) {
            $option['filter_name']   = trim($this->Request->post['s']);
        }

        $data['Options'] = $Option->getOptionGroups($option);
        $json = array(
            'status'    => 1,
            'options'   => $data['Options']
        );

        $this->Response->setOutPut(json_encode($json));
    }

    public function get() {
        if(isset($this->Request->post['option_id'])) {
            $option_id = (int) $this->Request->post['option_id'];
            /** @var Option $Option */
            $Option = $this->load("Option", $this->registry);
            $data['Option'] = $Option->getOptionByID($option_id);
            $json = array(
                'status'    => 1,
                'option'   => $data['Option']
            );
            $this->Response->setOutPut(json_encode($json));
        }
    }

}
