<?php
/**
 * Bootstrapping application 
 */

// Set request microtime for page time generation
$_SERVER['REQUEST_MICROTIME'] = microtime(true);

ini_set('memory_limit', '64M');

// Set display all errors & messages
ini_set('display_errors', 0);
error_reporting(E_ALL | E_STRICT);

// Define pathes
defined('BASE_PATH')
    || define('BASE_PATH', realpath(dirname(__FILE__) . '/../'));

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', BASE_PATH . '/app');

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? 
                                        getenv('APPLICATION_ENV') : 'production'));
                                        
if (APPLICATION_ENV == 'development') {
    ini_set('display_errors', 1);
}
                                        
// Ensure library & models is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    BASE_PATH . '/lib',
    APPLICATION_PATH . '/code',
    get_include_path(),
)));

// Loader
require_once('Zend/Loader/Autoloader.php');
$loader = Zend_Loader_Autoloader::getInstance();
$loader->setFallbackAutoloader(true);
$loader->suppressNotFoundWarnings(false);
//Lightweight autoloader
//$loader->setDefaultAutoloader(create_function('$class', "include str_replace('_', '/', \$class) . '.php';"));

// Enable output cache 
ob_start();

/**
 * Run application
 */
try {
    // Get or set bootstrap class
    if (!(isset($bootstrapClass) && !empty($bootstrapClass))) {
        $bootstrapClass = 'Bootstrap';
    } 
    
    // Load config
    $config = require_once(APPLICATION_PATH . '/configs/application.php');

    // Create bootstrap and runs it
    $bootstrap = new $bootstrapClass($config, APPLICATION_ENV);
    $bootstrap->bootstrap();
    $bootstrap->run();
} catch (Exception $exception) {
    echo '<html><title>Critical Error</title><body>'
       . '<h1>An exception occured while bootstrapping the application.</h1>';

    if (APPLICATION_ENV == 'development') {
        echo '<b>' . nl2br($exception->getMessage()) . '</b><br /><br />'
           . '<div align="left">Stack Trace:'
           . '<pre>' . $exception->getTraceAsString() . '</pre></div>';
    }
    echo '</body></html>';
}

if (APPLICATION_ENV == 'development') {
    require_once('FirePHPCore/FirePHP.class.php');
    $firephp = FirePHP::getInstance(true);
        
    $dbQueries = 0;
    $dbTime = 0;
    
    try {
        $profiler = Zend_Registry::get('db')->getProfiler();
        $dbQueries = $profiler->getTotalNumQueries();
        $dbTime = $profiler->getTotalElapsedSecs();
    } catch (Exception $ex) {}

    $memoryUsage = memory_get_peak_usage();
    $memoryUsage = $memoryUsage / 1024 / 1024;
    
    $pageGenTotalTime = microtime(true) - $_SERVER['REQUEST_MICROTIME'];
    
    $debugInfo = sprintf('Page generated in %.3f (%.3f) sec.; DB Queries: %d in %.3f sec.; Mem: %.3f Mb.', 
                        $pageGenTotalTime, $pageGenTotalTime - $dbTime, $dbQueries, $dbTime, $memoryUsage);
    $firephp->log($debugInfo, 'Page generation');
}

// Flush output cache
ob_end_flush();