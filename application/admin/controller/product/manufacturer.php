<?php

namespace App\Admin\Controller;

use App\Lib\Action;
use App\Lib\Config;
use App\Lib\Database;
use App\Lib\Request;
use App\Lib\Response;
use App\Lib\Validate;
use App\Model\Language;
use App\Model\Manufacturer;
use App\System\Controller;

/**
 * @property Response Response
 * @property Language Language
 * @property Request Request
 * @property Validate Validate
 * @property Config Config
 * @property Database Database
 */
class ControllerProductManufacturer extends Controller {

    public function index() {
        $data = [];
        $language_id = $this->Language->getLanguageID();
        /** @var Manufacturer $Manufacturer */
        $Manufacturer = $this->load("Manufacturer", $this->registry);
        $data['Manufacturers'] = $Manufacturer->getManufacturers(array(
            'language_id'   => $language_id
        ));
        $Language = $this->Language;
        $data['Languages'] = $Language->getLanguages();
        $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
        $this->Response->setOutPut($this->render("product/manufacturer/index", $data));
    }

    public function add() {
        $data = array();
        $error = false;
        $messages = [];
        if(!empty($this->Request->post['manufacturer-url'])) {
            if(!$this->registry->has("Validate")) {
                $this->registry->Validate = new Validate();
            }
            /** @var Validate $Validate */
            $Validate = $this->Validate;
            $Language = $this->Language;
            $languages = $Language->getLanguages();
            $languageDefaultID = $Language->getDefaultLanguageID();
            foreach ($languages as $language) {
                if(!empty($this->Request->post['manufacturer-name-' . $language['language_id']])) {
                    $data['manufacturer_names'][$language['language_id']] = $this->Request->post['manufacturer-name-' . $language['language_id']];
                }
            }
            if(empty($data['manufacturer_names'][$languageDefaultID])) {
                $error = true;
                $messages[] = $this->Language->get('error_manufacturer_name');
            }
            if(empty($this->Request->post['manufacturer-url']) || $Validate::urlValid($this->Request->post['manufacturer-url'])) {
                $error = true;
                $messages[] = $this->Language->get('error_invalid_url');
            }else {
                $data['url'] = $this->Request->post['manufacturer-url'];
            }
            if(!empty($this->Request->post['manufacturer-sort-order'])) {
                $data['sort_order'] = (int) $this->Request->post['manufacturer-sort-order'];
            }else {
                $data['sort_order'] = 0;
            }
            if(!empty($this->Request->post['manufacturer-image']) && $Validate::urlValid($this->Request->post['manufacturer-image'])) {
                $data['image'] = $this->Request->post['manufacturer-image'];
            }else {
                $data['image'] = $this->Config->get('manufacturer_image_default');
            }
            $json = [];
            if(!$error) {
                /** @var Manufacturer $Manufacturer */
                $Manufacturer = $this->load("Manufacturer", $this->registry);
                if($data['sort_order'] == 0) {
                    $rows = $Manufacturer->getManufacturers(array(
                        'sort'  => 'sort_order',
                        'order' => 'DESC',
                        'language_id'   => $languageDefaultID
                    ));
                    $oldSortOrder = count($rows) > 0 ? $rows[0]['sort_order'] : 0;
                    $data['sort_order'] = $oldSortOrder + 1;
                }
                $Manufacturer->insertManufacturer($data);
                $json['status'] = 1;
                $this->Response->endResponse();
                $json['process_time'] = $this->Response->getProcessTime();
                $json['messages'] = [$this->Language->get('message_success_manufacturer_added')];
                $json['redirect'] = ADMIN_URL . "product/manufacturer/index?token=" . $_SESSION['token'];
            }else {
                $json['status'] = 0;
                $json['messages'] = $messages;
            }
            $this->Response->setOutPut(json_encode($json));
        }else {
            $Language = $this->Language;
            $data['Languages'] = $Language->getLanguages();
            $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
            $this->Response->setOutPut($this->render('product/manufacturer/add', $data));
        }
    }

    public function status() {
        if(isset($this->Request->post['manufacturer_id']) && isset($this->Request->post['manufacturer_status'])) {
            $manufacturer_id = (int) $this->Request->post['manufacturer_id'];
            $manufacturer_status = (int) $this->Request->post['manufacturer_status'];
            /** @var Manufacturer $Manufacturer */
            $Manufacturer = $this->load('Manufacturer', $this->registry);
            $manufacturer = $Manufacturer->getManufacturerByID($manufacturer_id);
            $json = [];
            if($manufacturer_id && $manufacturer) {
                $Manufacturer->editManufacturer($manufacturer_id, array(
                    'status'    => $manufacturer_status
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
            $manufacturer_id = (int) $this->Request->get[0];
            /** @var Manufacturer $Manufacturer */
            $Manufacturer = $this->load("Manufacturer", $this->registry);
            $manufacturerTotal = $Manufacturer->getManufacturerByID($manufacturer_id, "all");
            $manufacturerInfo = [];
            $Language = $this->Language;
            foreach ($manufacturerTotal as $manufacturer) {
                $manufacturerInfo['manufacturer_names'][$manufacturer['language_id']] = $manufacturer['name'];
            }
            $manufacturerInfo['image'] = $manufacturerTotal[0]['image'];
            $manufacturerInfo['url'] = $manufacturerTotal[0]['url'];
            $manufacturerInfo['sort_order'] = $manufacturerTotal[0]['sort_order'];
            $manufacturerInfo['status'] = $manufacturerTotal[0]['status'];
            $manufacturerInfo['manufacturer_id'] = $manufacturerTotal[0]['manufacturer_id'];

            if($manufacturer_id && $manufacturerInfo) {
                if(!empty($this->Request->post['manufacturer-url'])) {
                    if(!$this->registry->has("Validate")) {
                        $this->registry->Validate = new Validate();
                    }
                    /** @var Validate $Validate */
                    $Validate = $this->Validate;
                    $Language = $this->Language;
                    $languages = $Language->getLanguages();
                    $languageDefaultID = $Language->getDefaultLanguageID();
                    foreach ($languages as $language) {
                        if(!empty($this->Request->post['manufacturer-name-' . $language['language_id']])) {
                            $data['manufacturer_names'][$language['language_id']] = $this->Request->post['manufacturer-name-' . $language['language_id']];
                        }
                    }
                    if(empty($data['manufacturer_names'][$languageDefaultID])) {
                        $error = true;
                        $messages[] = $this->Language->get('error_manufacturer_name');
                    }
                    if(empty($this->Request->post['manufacturer-url']) || $Validate::urlValid($this->Request->post['manufacturer-url'])) {
                        $error = true;
                        $messages[] = $this->Language->get('error_invalid_url');
                    }else {
                        if($Manufacturer->getManufacturerByUrl($this->Request->post['manufacturer-url']) && $this->Request->post['manufacturer-url'] != $manufacturerInfo['url']) {
                            $error = true;
                            $messages[] = $this->Language->get('error_exist_url');
                        }else {
                            $data['url'] = $this->Request->post['manufacturer-url'];
                        }
                    }
                    if(!empty($this->Request->post['manufacturer-sort-order'])) {
                        $data['sort_order'] = (int) $this->Request->post['manufacturer-sort-order'];
                    }else {
                        $data['sort_order'] = 0;
                    }
                    if(!empty($this->Request->post['manufacturer-image']) && $Validate::urlValid($this->Request->post['manufacturer-image'])) {
                        $data['image'] = $this->Request->post['manufacturer-image'];
                    }else {
                        $data['image'] = $this->Config->get('manufacturer_image_default');
                    }
                    $json = [];
                    if(!$error) {
                        $delete = [];
                        $add = [];
                        /** @var Manufacturer $Manufacturer */
                        $Manufacturer = $this->load("Manufacturer", $this->registry);
                        if($data['sort_order'] == 0) {
                            $rows = $Manufacturer->getManufacturers(array(
                                'sort'  => 'sort_order',
                                'order' => 'DESC',
                                'language_id'   => $languageDefaultID
                            ));
                            $oldSortOrder = count($rows) > 0 ? $rows[0]['sort_order'] : 0;
                            $data['sort_order'] = $oldSortOrder + 1;
                        }
                        if($manufacturerInfo['url'] == $data['url']) {
                            unset($data['url']);
                        }
                        if($manufacturerInfo['image'] == $data['image']) {
                            unset($data['image']);
                        }
                        if($manufacturerInfo['sort_order'] == $data['sort_order']) {
                            unset($data['sort_order']);
                        }
                        foreach ($languages as $language) {
                            if(isset($data['manufacturer_names'][$language['language_id']])
                                && isset($manufacturerInfo['manufacturer_names'][$language['language_id']])
                                && $data['manufacturer_names'][$language['language_id']] == $manufacturerInfo['manufacturer_names'][$language['language_id']] ) {
                                unset($data['manufacturer_names'][$language['language_id']]);
                            }else if(isset($manufacturerInfo['manufacturer_names'][$language['language_id']])
                                     && !isset($data['manufacturer_names'][$language['language_id']])) {
                                $delete['manufacturer_names'][$language['language_id']] = $manufacturerInfo['manufacturer_id'];
                            }else if (!isset($manufacturerInfo['manufacturer_names'][$language['language_id']])
                                && isset($data['manufacturer_names'][$language['language_id']])) {
                                $add['manufacturer_names'][$language['language_id']] =  $data['manufacturer_names'][$language['language_id']];
                                unset($data['manufacturer_names'][$language['language_id']]);
                            }
                        }
                        if(count($data['manufacturer_names']) == 0) {
                            unset($data['manufacturer_names']);
                        }
                        if(count($data) > 0) {
                            $Manufacturer->editManufacturer($manufacturerInfo['manufacturer_id'], $data);
                        }
                        if(count($add) > 0) {
                            $Manufacturer->insertManufacturer($add, $manufacturerInfo['manufacturer_id']);
                        }
                        if(count($delete) > 0) {
                            $Manufacturer->deleteManufacturer($manufacturerInfo['manufacturer_id'], $delete);
                        }

                        $json['status'] = 1;
                        $json['messages'] = [$this->Language->get('message_success_manufacturer_added')];
                        $json['redirect'] = ADMIN_URL . "product/manufacturer/index?token=" . $_SESSION['token'];
                    }else {
                        $json['status'] = 0;
                        $json['messages'] = $messages;
                    }
                    $this->Response->setOutPut(json_encode($json));
                    return;
                }else {

                    $data['Languages'] = $Language->getLanguages();
                    $data['ManufacturerLanguages'] = [];
                    foreach ($Language->getLanguages() as $language) {
                        $data['ManufacturerLanguages'][] = array(
                            'language_id' => $language['language_id'],
                            'language_name' => $language['name'],
                            'language_code' => $language['code'],
                            'manufacturer_image' => $manufacturerInfo['image'],
                            'manufacturer_sort_order' => $manufacturerInfo['sort_order'],
                            'manufacturer_status' => $manufacturerInfo['status'],
                            'manufacturer_id' => $manufacturerInfo['manufacturer_id'],
                            'manufacturer_name' => isset($manufacturerInfo['manufacturer_names'][$language['language_id']]) ? $manufacturerInfo['manufacturer_names'][$language['language_id']] : '',
                        );
                    }
                    $data['Manufacturer'] = $manufacturerInfo;
                    $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
                    $this->Response->setOutPut($this->render('product/manufacturer/edit', $data));
                    return;
                }
            }
        }
        return new Action('error/notFound', 'web');

    }

    public function delete() {
        if(!empty($this->Request->post['manufacturers_id'])) {
            $json = [];
            /** @var Manufacturer $Manufacturer */
            $Manufacturer = $this->load("Manufacturer", $this->registry);
            $error = false;
            $this->Database->db->beginTransaction();
            foreach ($this->Request->post['manufacturers_id'] as $manufacturer_id) {
                $manufacturer = $Manufacturer->getManufacturerByID((int) $manufacturer_id);
                if((int) $manufacturer_id && $manufacturer) {
                    $Manufacturer->deleteManufacturer($manufacturer_id);
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
                $data['Manufacturers'] = $Manufacturer->getManufacturers(array(
                    'language_id'   => $language_id
                ));
                $Language = $this->Language;
                $data['Languages'] = $Language->getLanguages();
                $data['LanguageDefaultID'] = $Language->getDefaultLanguageID();
                $json['data'] = $this->render("product/manufacturer/manufacturers_table", $data);
            }

            $this->Response->setOutPut(json_encode($json));
            return;
        }
        return new Action("error/notFound", 'web');
    }


}