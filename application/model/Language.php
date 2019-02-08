<?php

namespace App\Model;

use App\Lib\Database;
use App\Lib\Registry;
use App\System\Model;
use Doctrine\Common\Cache\FilesystemCache;

/**
 * @property Database Database
 * @property FilesystemCache Cache
 */
class Language extends Model {
    private $db;
    private $defaultLanguageDir = DEFAULT_LANGUAGE_DIR ;
    private $defaultLanguageCode = DEFAULT_LANGUAGE_CODE;
    private $languages = [];
    private $languageDir;
    private $languageID;
    private $languageCode;
    private $data = [];
    private $registry;
    private $defaultLanguageID;

    /**
     * @return mixed
     */
    public function getDefaultLanguageID()
    {
        return $this->defaultLanguageID;
    }

    /**
     * Language constructor.
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        parent::__construct($registry);
        /** @var Database $db */
        $db = $this->Database;
        /** @var FilesystemCache $cache */
        $cache = $this->Cache;
        $this->db = $db;
        if(!$cache->contains("languages")) {
            $languagesResults = $this->db->getRows("SELECT * FROM langauge");
            foreach ($languagesResults as $languagesResult) {
                $this->languages[$languagesResult['code']] = $languagesResult;
            }
            $cache->save("languages", $this->languages, LANGUAGE_CACHE_TIME);
        }else {
            $this->languages = $cache->fetch("languages");
        }

        $this->languageID = $this->languages[$this->defaultLanguageCode]['language_id'];
        $this->defaultLanguageID = $this->languages[$this->defaultLanguageCode]['language_id'];
        $this->languageDir = $this->languages[$this->defaultLanguageCode]['code'];
        $this->languageCode = $this->languages[$this->defaultLanguageCode]['code'];
    }

    public function setLanguageByID($languageID) {
        foreach ($this->languages as $language) {
            if($language['language_id'] == $languageID) {
                $this->languageDir = $language['code'];
                $this->languageCode = $language['code'];
                $this->languageID = $language['language_id'];
                break;
            }
        }
    }


    /**
     *
     *
     * @param	string	$key
     *
     * @return	string
     */
    public function get($key) {
        return (isset($this->data[$key]) ? $this->data[$key] : $key);
    }

    public function set($key, $value) {
        $this->data[$key] = $value;
    }

    /**
     *
     *
     * @return	array
     */
    public function all() {
        return $this->data;
    }

    /**
     *
     *
     * @param	string	$filename
     * @param	string	$key
     *
     * @return	array
     */
    public function load($filename, $key = '') {
        if (!$key) {
            $_ = array();

            $file = LANGUAGE_PATH . DS . $this->defaultLanguageDir . DS . $filename . '.php';

            if (is_file($file)) {
                require($file);
            }

            $file = LANGUAGE_PATH . DS . $this->languageDir . DS . $filename . '.php';

            if (is_file($file)) {
                require($file);
            }

            $this->data = array_merge($this->data, $_);
        } else {
            // Put the language into a sub key
            $this->data[$key] = new Language($this->registry);
            $this->data[$key]->load($filename);
        }

        return $this->data;
    }

    /**
     * @return mixed
     */
    public function getDefaultLanguageCode()
    {
        return $this->defaultLanguageCode;
    }

    /**
     * @return array
     */
    public function getLanguages(): array
    {
        return $this->languages;
    }

    /**
     * @return mixed
     */
    public function getLanguageID()
    {
        return $this->languageID;
    }

    /**
     * @return mixed
     */
    public function getLanguageCode()
    {
        return $this->languageCode;
    }

    public function getLanguageByCode($code) {
        if(!empty($this->languages[$code])) {
            return $this->languages[$code];
        }else {
            return false;
        }
    }

}
