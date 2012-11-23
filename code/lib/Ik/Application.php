<?php
/**
 * INTERKASSA
 * http://www.interkassa.com/
 * 
 * $Id: Application.php 158 2010-09-04 16:49:32Z denis $
 * $HeadURL: http://www.interkassa.com/svn/interkassa1/trunk/lib/Ik/Application.php $
 * $LastChangedBy: denis $
 * $LastChangedDate: 2009-08-06$
 * $LastChangedRevision: 158 $
 */

class Ik_Application
{
    /**
     * Autoloader to use
     * 
     * @var Zend_Loader_Autoloader
     */
    protected $_autoloader;

    /**
     * Bootstrap
     * 
     * @var Ik_Application_Bootstrap_Abstract
     */
    protected $_bootstrap;
        
    /**
     * Config
     * 
     * @var array
     */
    protected $_options;
    
    public function __construct($config)
    {
        $this->_autoloader = Zend_Loader_Autoloader::getInstance();
            
        if (!is_array($config)) {
            throw new Zend_Application_Exception('Invalid config');
        }
        
        $this->_options = $config;

        Zend_Registry::set('app', $this);
    }
    
	public function bootstrap()
	{
	    $this->_bootstrap->bootstrap();
	}
	 
	public function run()
	{
	    $this->_bootstrap->run();
	}	
	
	public function getBootstrap()
	{
	    return $this->_bootstrap;
	}
} 