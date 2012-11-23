<?php
/*
 * Interkassa Default Bootstrap
 *
 * $Id: Bootstrap.php 164 2011-01-06 19:32:00Z denis $
 * $HeadURL: http://www.interkassa.com/svn/interkassa1/trunk/app/code/Bootstrap.php $
 * $LastChangedBy: denis $
 * $LastChangedDate: 2011-01-06 21:32:00 +0200 (Чт, 06 янв 2011) $
 * $LastChangedRevision: 164 $
 */

class Bootstrap extends Ik_Application_Bootstrap_Abstract
{
    protected function _initEnvironment()
    {
        // Set request microtime
        if (null === $_SERVER['REQUEST_MICROTIME']) {
            $_SERVER['REQUEST_MICROTIME'] = microtime();
        }

        // Setup Debug Mode
        if ($this->_options['env']['debug_mode'] === true) {
            error_reporting(E_ALL | E_STRICT);
            ini_set('display_errors', 1);
            Ik_Debug::setEnabled(true);
        } else {
            error_reporting((E_ALL | E_STRICT) ^ E_NOTICE ^ E_WARNING);
            ini_set('display_errors', 0);
        }

        // Set default time zone
        date_default_timezone_set($this->_options['env']['timezone']);

        Zend_Validate::addDefaultNamespaces('Ik_Validate');
        Zend_Filter::addDefaultNamespaces('Ik_Filter');
    }

    protected function _initConfig()
    {
        $config = new Zend_Config($this->_options);
        Zend_Registry::set('config', $config);
        return $config;
    }

    /**
     * Database
     */
    protected function _initDb()
    {
        // Initialize Database
        $db = Zend_Db::factory(
            $this->_options['db']['adapter'],
            $this->_options['db']['params']
        );

        // Setup debug mode
        if ($this->_options['env']['debug_mode'] === true) {
            $profiler = new Zend_Db_Profiler_Firebug('DB Profiler');
            $profiler->setEnabled(true);
            $db->setProfiler($profiler);
        }

        // Set as default adapter
        Zend_Db_Table::setDefaultAdapter($db);
        Zend_Registry::set('db', $db);
    }

    protected function _initMongo()
    {
        $params = $this->_options['mongo']['params'];
        $serversConfig = array();
        foreach ($params as $serverParams) {
            $serversConfig[] = $serverParams['server'];
        }
        Ik_Mongo_Connection::setupDefaultConn(implode(',', $serversConfig));
    }

    protected function _initFrontController()
    {
        $front = Zend_Controller_Front::getInstance();

        $front->addModuleDirectory($this->_options['path']['modules']);

        // Setup debug mode
        $front->setParam('disableOutputBuffering', true);

        // Register bootstrap as front controller plugin
        $front->registerPlugin($this);

        return $front;
    }

    protected function _initErrorHandlerRouteShutdown()
    {
        $frontController = Zend_Controller_Front::getInstance();
        $errorPlugin = $frontController->getPlugin('Zend_Controller_Plugin_ErrorHandler');
        if ($errorPlugin !== false) {
            $errorPlugin->setErrorHandlerModule($this->getRequest()->getModuleName());
        }
    }

    /**
     * Zend_Layout & Zend_View
     */
    protected function _initView()
    {
        // Set Layout
        $layout = Zend_Layout::startMvc(array(
            'viewScriptPath' => $this->_options['path']['mvc_layouts'],
            'layout'     => 'default',
        ));

        // Set View
        $view = $layout->getView();

        $view->headTitle($this->_options['site']['title']);
        $view->headTitle()->setSeparator(' - ');

        // Set Paginator
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('paginatorControl.phtml');
    }

    protected function _initLayoutRouteShutdown()
    {
        $layout = Zend_Layout::getMvcInstance();
        if (null !== $layout) {
            $layoutPath = $this->_options['path']['modules']
                        . $this->getRequest()->getModuleName()
                        . $layout->getLayoutPath();

            $layout->setLayoutPath($layoutPath);
        }
    }

    protected function _initSession()
    {
        $session = new Zend_Session_Namespace('Main', true);
        Zend_Registry::set('session', $session);
    }

    protected function _initLocale()
    {
        $region = 'UA';
        $lang = 'auto';

        if (!empty($_REQUEST['ik_loc'])) {
            list($lang, $region) = explode('_', $_REQUEST['ik_loc']);
            Zend_Registry::set('clientLangs', array($lang));
        }

        $locale = new Zend_Locale($lang);
        Zend_Registry::set('Zend_Locale', $locale);

        $localeCurrentLang = array($locale->getLanguage() => true);
        $localeDefaultLang = Zend_Locale::getDefault();
        $clientLangs = $localeCurrentLang + $localeDefaultLang;
        Zend_Registry::set('clientLangs', array_keys($clientLangs));

        Zend_Registry::set('clientRegion', $region);

        return $locale;
    }

    protected function _initBootstrap()
    {
        $this->bootstrapResourses(array(
            'environment',
            'config',
            'db',
            'mongo',
            'view',
            'frontcontroller',
            'locale',
            'session',
        ));
        return $this;
    }

    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        $this->bootstrapResourses(array(
            'layoutRouteShutdown',
            'errorHandlerRouteShutdown',
        ));
    }

    public function run()
    {
        $front = Zend_Controller_Front::getInstance();
        $front->returnResponse(true);

        $response = $front->dispatch();

        if ($response->isException() && !$front->hasPlugin('Zend_Controller_Plugin_ErrorHandler')) {
            $exceptions = $response->getException();
            throw reset($exceptions);
        } else {
            $response->sendResponse();
        }
    }
}