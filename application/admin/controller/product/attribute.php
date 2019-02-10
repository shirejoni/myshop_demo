<?php

namespace App\Admin\Controller;

use App\Lib\Database;
use App\Lib\Request;
use App\Lib\Response;
use App\Model\Attribute;
use App\Model\AttributeGroup;
use App\Model\Language;
use App\System\Controller;

/**
 * @property Response Response
 * @property Language Language
 * @property Request Request
 * @property Database Database
 */
class ControllerProductAttribute extends Controller {
    public function index() {
        $data = [];
        $language_id = $this->Language->getLanguageID();
        /** @var Attribute $Attribute */
        $Attribute = $this->load("Attribute", $this->registry);
        $data['Attributes'] = $Attribute->getAttributes(array(
            'language_id'   => $language_id
        ));
        $Language = $this->Language;
        $data['Languages'] = $Language->getLanguages();
        $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
        $this->Response->setOutPut($this->render('product/attribute/index', $data));
    }

    public function add() {
        $data = array();
        $error = false;
        $messages = [];
        if(!empty($this->Request->post['attributegroup-id'])) {
            $Language = $this->Language;
            $languages = $Language->getLanguages();
            $languageDefaultID = $Language->getDefaultLanguageID();
            /** @var AttributeGroup $AttributeGroup */
            $AttributeGroup = $this->load("AttributeGroup", $this->registry);
            foreach ($languages as $language) {
                if(!empty($this->Request->post['attribute-name-' . $language['language_id']])) {
                    $data['attribute_names'][$language['language_id']] = $this->Request->post['attribute-name-' . $language['language_id']];
                }
            }
            if(empty($data['attribute_names'][$languageDefaultID])) {
                $error = true;
                $messages[] = $this->Language->get('error_attribute_name');
            }

            if(!empty($this->Request->post['attribute-sort-order'])) {
                $data['sort_order'] = (int) $this->Request->post['attribute-sort-order'];
            }else {
                $data['sort_order'] = 0;
            }

            if(!empty($this->Request->post['attributegroup-id']) && $AttributeGroup->getAttributeGroupByID($this->Request->post['attributegroup-id'])) {
                $data['attributegroup_id'] = (int) $this->Request->post['attributegroup-id'];
            }else {
                $error = true;
                $messages[] = $this->Language->get("error_attributegroup_select");
            }

            $json = [];
            if(!$error) {
                /** @var Attribute $Attribute */
                $Attribute = $this->load("Attribute", $this->registry);
                if($data['sort_order'] == 0) {
                    $rows = $Attribute->getAttributes(array(
                        'sort'  => 'a.sort_order',
                        'order' => 'DESC',
                        'language_id'   => $languageDefaultID
                    ));
                    $oldSortOrder = count($rows) > 0 ? $rows[0]['sort_order'] : 0;
                    $data['sort_order'] = $oldSortOrder + 1;
                }
                $Attribute->insertAttribute($data);
                $json['status'] = 1;
                $this->Response->endResponse();
                $json['process_time'] = $this->Response->getProcessTime();
                $json['messages'] = [$this->Language->get('message_success_done')];
                $json['redirect'] = ADMIN_URL . "product/attribute/index?token=" . $_SESSION['token'];
            }else {
                $json['status'] = 0;
                $json['messages'] = $messages;
            }
            $this->Response->setOutPut(json_encode($json));
        }else {
            $Language = $this->Language;
            /** @var AttributeGroup $AttributeGroup */
            $AttributeGroup = $this->load('AttributeGroup', $this->registry);
            $data['AttributeGroups'] = $AttributeGroup->getAttributeGroups();
            $data['Languages'] = $Language->getLanguages();
            $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
            $this->Response->setOutPut($this->render('product/attribute/add', $data));
        }

    }

    public function delete() {
        if(!empty($this->Request->post['attributes_id'])) {
            $json = [];
            /** @var Attribute $Attribute */
            $Attribute = $this->load("Attribute", $this->registry);
            $error = false;
            $this->Database->db->beginTransaction();
            foreach ($this->Request->post['attributes_id'] as $attribute_id) {
                $attribute = $Attribute->getAttributeByID((int) $attribute_id);
                if((int) $attribute_id && $attribute) {
                    $Attribute->deleteAttribute($attribute_id);
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
                $data['Attributes'] = $Attribute->getAttributes(array(
                    'language_id'   => $language_id
                ));
                $Language = $this->Language;
                $data['Languages'] = $Language->getLanguages();
                $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
                $json['data'] = $this->render("product/attribute/attributes_table", $data);
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
            $attribute_id = (int) $this->Request->get[0];
            /** @var Attribute $Attribute */
            $Attribute = $this->load("Attribute", $this->registry);
            $attributeTotal = $Attribute->getAttributeByID($attribute_id, "all");
            $attributeInfo = [];
            $Language = $this->Language;
            $languageDefaultID = $Language->getDefaultLanguageID();
            foreach ($attributeTotal as $attribute) {
                if(!isset($attributeInfo['attribute_names'][$attribute['language_id']])) {
                    $attributeInfo['attribute_names'][$attribute['language_id']] = $attribute['name'];
                }
                if($attribute['language_id'] == $languageDefaultID) {
                    $attributeInfo['attributegroup_name'] = $attribute['attributegroup_name'];
                }
            }
            $attributeInfo['sort_order'] = $attributeTotal[0]['sort_order'];
            $attributeInfo['attribute_group_id'] = $attributeTotal[0]['attribute_group_id'];
            $attributeInfo['attribute_id'] = $attributeTotal[0]['attribute_id'];

            if($attribute_id && $attributeInfo) {
                if(!empty($this->Request->post['attributegroup-id'])) {
                    $Language = $this->Language;
                    $languages = $Language->getLanguages();
                    $languageDefaultID = $Language->getDefaultLanguageID();
                    /** @var AttributeGroup $AttributeGroup */
                    $AttributeGroup = $this->load("AttributeGroup", $this->registry);
                    foreach ($languages as $language) {
                        if(!empty($this->Request->post['attribute-name-' . $language['language_id']])) {
                            $data['attribute_names'][$language['language_id']] = $this->Request->post['attribute-name-' . $language['language_id']];
                        }
                    }
                    if(empty($data['attribute_names'][$languageDefaultID])) {
                        $error = true;
                        $messages[] = $this->Language->get('error_attribute_name');
                    }

                    if(!empty($this->Request->post['attribute-sort-order'])) {
                        $data['sort_order'] = (int) $this->Request->post['attribute-sort-order'];
                    }else {
                        $data['sort_order'] = 0;
                    }

                    if(!empty($this->Request->post['attributegroup-id']) && $AttributeGroup->getAttributeGroupByID($this->Request->post['attributegroup-id'])) {
                        $data['attributegroup_id'] = (int) $this->Request->post['attributegroup-id'];
                    }else {
                        $error = true;
                        $messages[] = $this->Language->get("error_attributegroup_select");
                    }

                    $json = [];
                    if(!$error) {
                        $delete = [];
                        $add = [];
                        if($data['sort_order'] == 0) {
                            $rows = $Attribute->getAttributes(array(
                                'sort'  => 'a.sort_order',
                                'order' => 'DESC',
                                'language_id'   => $languageDefaultID
                            ));
                            $oldSortOrder = count($rows) > 0 ? $rows[0]['sort_order'] : 0;
                            $data['sort_order'] = $oldSortOrder + 1;
                        }
                        if($attributeInfo['sort_order'] == $data['sort_order']) {
                            unset($data['sort_order']);
                        }
                        if($attributeInfo['attribute_group_id'] == $data['attributegroup_id']) {
                            unset($data['attributegroup_id']);
                        }
                        foreach ($languages as $language) {
                            if(isset($data['attribute_names'][$language['language_id']])
                                && isset($attributeInfo['attribute_names'][$language['language_id']])
                                && $data['attribute_names'][$language['language_id']] == $attributeInfo['attribute_names'][$language['language_id']] ) {
                                unset($data['attribute_names'][$language['language_id']]);
                            }else if(isset($attributeInfo['attribute_names'][$language['language_id']])
                                && !isset($data['attribute_names'][$language['language_id']])) {
                                $delete['attribute_names'][$language['language_id']] = $attributeInfo['attribute_names'];
                            }else if (!isset($attributeInfo['attribute_names'][$language['language_id']])
                                && isset($data['attribute_names'][$language['language_id']])) {
                                $add['attribute_names'][$language['language_id']] =  $data['attribute_names'][$language['language_id']];
                                unset($data['attribute_names'][$language['language_id']]);
                            }
                        }
                        if(count($data['attribute_names']) == 0) {
                            unset($data['attribute_names']);
                        }
                        if(count($data) > 0) {
                            $Attribute->editAttribute($attributeInfo['attribute_id'], $data);
                        }
                        if(count($add) > 0) {
                            $Attribute->insertAttribute($add, $attributeInfo['attribute_id']);
                        }
                        if(count($delete) > 0) {
                            $Attribute->deleteAttribute($attributeInfo['attribute_id'], $delete);
                        }

                        $json['status'] = 1;
                        $json['messages'] = [$this->Language->get('message_success_done')];
                        $json['redirect'] = ADMIN_URL . "product/attribute/index?token=" . $_SESSION['token'];
                    }else {
                        $json['status'] = 0;
                        $json['messages'] = $messages;
                    }
                    $this->Response->setOutPut(json_encode($json));
                    return;
                }else {
                    $Language = $this->Language;
                    /** @var AttributeGroup $AttributeGroup */
                    $AttributeGroup = $this->load('AttributeGroup', $this->registry);
                    $data['AttributeGroups'] = $AttributeGroup->getAttributeGroups();
                    $data['Languages'] = $Language->getLanguages();
                    $data['AttributeLanguages'] = [];
                    foreach ($Language->getLanguages() as $language) {
                        $data['AttributeLanguages'][] = array(
                            'language_id' => $language['language_id'],
                            'language_name' => $language['name'],
                            'language_code' => $language['code'],
                            'attributegroup_name' => $attributeInfo['attributegroup_name'],
                            'attributegroup_id' => $attributeInfo['attribute_group_id'],
                            'attribute_sort_order' => $attributeInfo['sort_order'],
                            'attribute_id' => $attributeInfo['attribute_id'],
                            'attribute_name' => isset($attributeInfo['attribute_names'][$language['language_id']]) ? $attributeInfo['attribute_names'][$language['language_id']] : '',
                        );
                    }
                    $data['Attribute'] = $attributeInfo;
                    $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
                    $this->Response->setOutPut($this->render('product/attribute/edit', $data));
                    return;
                }
            }
        }
        return new Action('error/notFound', 'web');

    }


}