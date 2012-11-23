<?php
require_once 'common.inc.php';

/**
 * Config
 */
$config = array();

/**
 * Base
 */
$config['base'] = array (
    'site' => array (
        'name'      => 'mms',
        'title'     => 'mms',
        'url'       => 'http://mms/',
    ),
    'path' => array (
        'root'          => BASE_PATH . '/',
        'public'        => BASE_PATH . '/public/',
        'library'       => BASE_PATH . '/library/',
        'application'   => BASE_PATH . '/app/',
        'modules'       => BASE_PATH . '/app/modules/',
        'locales'       => BASE_PATH . '/app/locale/',
        'design'        => BASE_PATH . '/app/design/',
        'data'          => BASE_PATH . '/data/',
        'data_backup'    => BASE_PATH . '/data/backup/',
        'data_cache'     => BASE_PATH . '/data/cache/',
        'data_index'     => BASE_PATH . '/data/index/',
        'data_log'       => BASE_PATH . '/data/log/',
        'data_session'   => BASE_PATH . '/data/session/',
        'mvc_modules'    => '/modules/',
        'mvc_views'      => '/views/',
        'mvc_layouts'    => '/layouts/',
        'mvc_controllers'=> '/controllers/',
    ),
    'url' => array (
         'image'        => '/img/',
         'css'          => '/css/',
         'js'           => '/js/',
    ),
    'env' => array (
        'locale'            => 'ru',
        'charset'           => 'utf-8',
        'timezone'          => 'Europe/Kiev',
        'debug_mode'        => false,
        'includePaths'      => array(),
        'namespaces'        => array(),
        'phpSettings'       => array(),
    ),
    'bootstrap' => array(
        'path' => APPLICATION_PATH . "/Bootstrap.php",
        'class' => "Bootstrap",
    ),
    'autoloadernamespaces' => array(
        'Core',
        'Ik',
    ),
    'resources' => array(
        'frontController' => array(
            'controllerDirectory' => APPLICATION_PATH . "/controllers",
            'moduleDirectory' => APPLICATION_PATH . "/modules",
            'defaultModule' => "default",
            'env' => APPLICATION_ENV,
        ),
        'modules' => array(),
    ),
    'db' => array (
        'adapter'   => 'pdo_mysql',
        'params'    => array (
            'host'          => 'localhost',
            'username'      => 'root',
            'password'      => '',
            'dbname'        => 'mms',
            'charset'       => 'utf8',
        ),
    ),
    'mongo' => array(
        'params' => array(
            array(
                'server' => 'mongodb://localhost:27017',
            )
        ),
        'db'     => 'mms',
    )
);

/**
 * Production
 */
$config['production'] = $config['base'];

/**
 * Testing
 */
$config['testing'] = array_merge_recursive_distinct($config['production'], array (
    'env' => array (
        'debug_mode'        => true,
    ),
));

/**
 * Development
 */
$config['development'] = $config['testing'];

return $config;