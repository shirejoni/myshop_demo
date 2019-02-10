<?php

namespace App\Admin\Controller;

use App\Lib\Database;
use App\Lib\Request;
use App\Lib\Response;
use App\Model\AttributeGroup;
use App\Model\Language;
use App\System\Controller;

/**
 * @property Response Response
 * @property Language Language
 * @property Request Request
 * @property Database Database
 */
class ControllerProductAttributegroup extends Controller {
    public function index() {
        $data = [];
        $language_id = $this->Language->getLanguageID();
        /** @var AttributeGroup $AttributeGroup */
        $AttributeGroup = $this->load("AttributeGroup", $this->registry);
        $data['AttributeGroups'] = $AttributeGroup->getAttributeGroups(array(
            'language_id'   => $language_id
        ));
        $Language = $this->Language;
        $data['Languages'] = $Language->getLanguages();
        $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
        $this->Response->setOutPut($this->render('product/attributegroup/index', $data));
    }

    public function add() {
        $data = array();
        $error = false;
        $messages = [];
        if(isset($this->Request->post['attribute-post'])) {
            $Language = $this->Language;
            $languages = $Language->getLanguages();
            $languageDefaultID = $Language->getDefaultLanguageID();
            foreach ($languages as $language) {
                if(!empty($this->Request->post['attributegroup-name-' . $language['language_id']])) {
                    $data['attributegroup_names'][$language['language_id']] = $this->Request->post['attributegroup-name-' . $language['language_id']];
                }
            }
            if(empty($data['attributegroup_names'][$languageDefaultID])) {
                $error = true;
                $messages[] = $this->Language->get('error_attributegroup_name');
            }
            if(!empty($this->Request->post['attributegroup-sort-order'])) {
                $data['sort_order'] = (int) $this->Request->post['attributegroup-sort-order'];
            }else {
                $data['sort_order'] = 0;
            }
            $json = [];
            if(!$error) {
                /** @var AttributeGroup $AttributeGroup */
                $AttributeGroup = $this->load("AttributeGroup", $this->registry);
                if($data['sort_order'] == 0) {
                    $rows = $AttributeGroup->getAttributeGroups(array(
                        'sort'  => 'sort_order',
                        'order' => 'DESC',
                        'language_id'   => $languageDefaultID
                    ));
                    $oldSortOrder = count($rows) > 0 ? $rows[0]['sort_order'] : 0;
                    $data['sort_order'] = $oldSortOrder + 1;
                }
                $AttributeGroup->insertAttributeGroup($data);
                $json['status'] = 1;
                $this->Response->endResponse();
                $json['process_time'] = $this->Response->getProcessTime();
                $json['messages'] = [$this->Language->get('message_success_done')];
                $json['redirect'] = ADMIN_URL . "product/attributegroup/index?token=" . $_SESSION['token'];
            }else {
                $json['status'] = 0;
                $json['messages'] = $messages;
            }
            $this->Response->setOutPut(json_encode($json));
        }else {
            $Language = $this->Language;
            $data['Languages'] = $Language->getLanguages();
            $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
            $this->Response->setOutPut($this->render('product/attributegroup/add', $data));
        }
    }

    public function delete() {
        if(!empty($this->Request->post['attributegroups_id'])) {
            $json = [];
            /** @var AttributeGroup $AttributeGroup */
            $AttributeGroup = $this->load("AttributeGroup", $this->registry);
            $error = false;
            $this->Database->db->beginTransaction();
            foreach ($this->Request->post['attributegroups_id'] as $attribute_group_id) {
                $attributeGroup = $AttributeGroup->getAttributeGroupByID((int) $attribute_group_id);
                if((int) $attribute_group_id && $attributeGroup) {
                    $AttributeGroup->deleteAttributeGroup($attribute_group_id);
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
                $data['AttributeGroups'] = $AttributeGroup->getAttributeGroups(array(
                    'language_id'   => $language_id
                ));
                $Language = $this->Language;
                $data['Languages'] = $Language->getLanguages();
                $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
                $json['data'] = $this->render("product/attributegroup/attributegroups_table", $data);
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
            $attribute_group_id = (int) $this->Request->get[0];
            /** @var AttributeGroup $AttributeGroup */
            $AttributeGroup = $this->load("AttributeGroup", $this->registry);
            $attributeGroupTotal = $AttributeGroup->getAttributeGroupByID($attribute_group_id, "all");
            $attributeGroupInfo = [];
            $Language = $this->Language;
            foreach ($attributeGroupTotal as $attributeGroup) {
                $attributeGroupInfo['attributegroup_names'][$attributeGroup['language_id']] = $attributeGroup['name'];
            }
            $attributeGroupInfo['sort_order'] = $attributeGroupTotal[0]['sort_order'];
            $attributeGroupInfo['attribute_group_id'] = $attributeGroupTotal[0]['attribute_group_id'];

            if($attribute_group_id && $attributeGroupInfo) {
                if(isset($this->Request->post['attribute-post'])) {
                    $Language = $this->Language;
                    $languages = $Language->getLanguages();
                    $languageDefaultID = $Language->getDefaultLanguageID();
                    foreach ($languages as $language) {
                        if(!empty($this->Request->post['attributegroup-name-' . $language['language_id']])) {
                            $data['attributegroup_names'][$language['language_id']] = $this->Request->post['attributegroup-name-' . $language['language_id']];
                        }
                    }
                    if(empty($data['attributegroup_names'][$languageDefaultID])) {
                        $error = true;
                        $messages[] = $this->Language->get('error_attributegroup_name');
                    }
                    if(!empty($this->Request->post['attributegroup-sort-order'])) {
                        $data['sort_order'] = (int) $this->Request->post['attributegroup-sort-order'];
                    }else {
                        $data['sort_order'] = 0;
                    }
                    $json = [];
                    if(!$error) {
                        $delete = [];
                        $add = [];
                        if($data['sort_order'] == 0) {
                            $rows = $AttributeGroup->getAttributeGroups(array(
                                'sort'  => 'sort_order',
                                'order' => 'DESC',
                                'language_id'   => $languageDefaultID
                            ));
                            $oldSortOrder = count($rows) > 0 ? $rows[0]['sort_order'] : 0;
                            $data['sort_order'] = $oldSortOrder + 1;
                        }
                        if($attributeGroupInfo['sort_order'] == $data['sort_order']) {
                            unset($data['sort_order']);
                        }
                        foreach ($languages as $language) {
                            if(isset($data['attributegroup_names'][$language['language_id']])
                                && isset($attributeGroupInfo['attributegroup_names'][$language['language_id']])
                                && $data['attributegroup_names'][$language['language_id']] == $attributeGroupInfo['attributegroup_names'][$language['language_id']] ) {
                                unset($data['attributegroup_names'][$language['language_id']]);
                            }else if(isset($attributeGroupInfo['attributegroup_names'][$language['language_id']])
                                && !isset($data['attributegroup_names'][$language['language_id']])) {
                                $delete['attributegroup_names'][$language['language_id']] = $attributeGroupInfo['attributegroup_names'];
                            }else if (!isset($attributeGroupInfo['attributegroup_names'][$language['language_id']])
                                && isset($data['attributegroup_names'][$language['language_id']])) {
                                $add['attributegroup_names'][$language['language_id']] =  $data['attributegroup_names'][$language['language_id']];
                                unset($data['attributegroup_names'][$language['language_id']]);
                            }
                        }
                        if(count($data['attributegroup_names']) == 0) {
                            unset($data['attributegroup_names']);
                        }
                        if(count($data) > 0) {
                            $AttributeGroup->editAttributeGroup($attributeGroupInfo['attribute_group_id'], $data);
                        }
                        if(count($add) > 0) {
                            $AttributeGroup->insertAttributeGroup($add, $attributeGroupInfo['attribute_group_id']);
                        }
                        if(count($delete) > 0) {
                            $AttributeGroup->deleteAttributeGroup($attributeGroupInfo['attribute_group_id'], $delete);
                        }

                        $json['status'] = 1;
                        $json['messages'] = [$this->Language->get('message_success_done')];
                        $json['redirect'] = ADMIN_URL . "product/attributegroup/index?token=" . $_SESSION['token'];
                    }else {
                        $json['status'] = 0;
                        $json['messages'] = $messages;
                    }
                    $this->Response->setOutPut(json_encode($json));
                    return;
                }else {

                    $data['Languages'] = $Language->getLanguages();
                    $data['AttributeGroupLanguages'] = [];
                    foreach ($Language->getLanguages() as $language) {
                        $data['AttributeGroupLanguages'][] = array(
                            'language_id' => $language['language_id'],
                            'language_name' => $language['name'],
                            'language_code' => $language['code'],
                            'attribute_group_sort_order' => $attributeGroupInfo['sort_order'],
                            'attribute_group_id' => $attributeGroupInfo['attribute_group_id'],
                            'attribute_group_name' => isset($attributeGroupInfo['attributegroup_names'][$language['language_id']]) ? $attributeGroupInfo['attributegroup_names'][$language['language_id']] : '',
                        );
                    }
                    $data['AttributeGroup'] = $attributeGroupInfo;
                    $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
                    $this->Response->setOutPut($this->render('product/attributegroup/edit', $data));
                    return;
                }
            }
        }
        return new Action('error/notFound', 'web');

    }


}