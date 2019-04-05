<?php
define("PUB_PATH", ROOT_PATH . DS . 'public');
define("WEB_PATH", APP_PATH . DS . 'web');
define("ADMIN_PATH", APP_PATH . DS . 'admin');
define("MODEL_PATH", APP_PATH . DS . 'model');
define("LANGUAGE_PATH", APP_PATH . DS . 'language');
define("SYSTEM_PATH", APP_PATH . DS . 'system');
define("CACHE_PATH", APP_PATH . DS . 'cache');
define("LIB_PATH", APP_PATH . DS . 'lib');
define("IMAGE_PATH", PUB_PATH . DS . 'assets' . DS . 'img');
define("ASSETS_PATH", PUB_PATH . DS . 'assets');

define("DB_NAME", "demoshop");
define("DB_USER", "root");
define("DB_SERVER", "localhost");
define("DB_PASSWORD", "");

define("URL", "http://myshopdemo.test/");
define("ASSETS_URL", "http://myshopdemo.test/assets/");
define("ADMIN_URL", "http://myshopdemo.test/admin/");

define("DEFAULT_LANGUAGE_DIR", "fa");
define("DEFAULT_LANGUAGE_CODE", "fa");
define("MAIN_CONFIG_FILENAME", 'config');
define("DEBUG_MODE", true);
define("MODEL_NAMESPACE", "App\\Model");

define("LONG_TIME_CACHE", 3600 * 5);
define("MEDIUM_TIME_CACHE", 1800);
define("SHORT_TIME_CACHE", 60);

define("LANGUAGE_CACHE_TIME", LONG_TIME_CACHE);

define("CKFINDER_ROUT", "ckfinder/ckfinder");
define("LOGIN_STATUS_LOGIN_FORM", 1);
