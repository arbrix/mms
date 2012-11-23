<?php
/**
 * INTERKASSA
 * http://www.interkassa.com/
 * 
 * $Id: Abstract.php 158 2010-09-04 16:49:32Z denis $
 * $HeadURL: http://www.interkassa.com/svn/interkassa1/trunk/lib/Ik/Application/Bootstrap/Abstract.php $
 * $LastChangedBy: denis $
 * $LastChangedDate: 2010-09-04 19:49:32 +0300 (Сб, 04 сен 2010) $
 * $LastChangedRevision: 158 $
 */

abstract class Ik_Application_Bootstrap_Abstract extends Zend_Controller_Plugin_Abstract
{
    protected $_classResources;
    
    protected $_resources = array();
    
    /**
     * @var array
     */
    protected $_options = array();
        
    public function __construct($config, $env)
    {
        if (!is_array($config) 
            && $config instanceof Zend_Config) {
            $config = $config->toArray();
        } elseif (!is_array($config)) {
            throw new Exception('Invalid config format');
        }
        
        if (null === $config[$env]) {
            throw new Exception('Config with defined application environment not exist');
        } else {
            $this->_options = $config[$env];
        }
        
        $this->_setupClassResources();
        
        if (isset($this->_options['env']['debug_mode'])
            && $this->_options['env']['debug_mode'] === true) {
            Ik_Debug::setEnabled(true);
            Ik_Debug::getGenerateTime('BS - ' . __FUNCTION__);
        }        
        
        Zend_Registry::set('bootstrap', $this);
    }

    protected function _setupClassResources()
    {
        if (version_compare(PHP_VERSION, '5.2.6') === -1) {
            $class        = new ReflectionObject($this);
            $classMethods = $class->getMethods();
            $methodNames  = array();
            
            foreach ($classMethods as $method) {
                $methodNames[] = $method->getName();
            }
        } else {
            $methodNames = get_class_methods($this);
        }
        
        $this->_classResources = array();
        foreach ($methodNames as $method) {
            if (5 < strlen($method) && '_init' === substr($method, 0, 5)) {
                $this->_classResources[strtolower(substr($method, 5))] = $method;
            }
        }
    }
    
    public function getClassResources()
    {
        if (null === $this->_classResources) {
            $this->_setupClassResources();
        }
        
        return $this->_classResources;
    }
        
    public function getClassResourceNames()
    {
        $resources = $this->getClassResources();
        return array_keys($resources);
    }    
    
    public function getResource($resourceName)
    {
        /*
        $resourceName = strtolower($resourceName);
        
        if (null === $this->_resources[$resourceName]) {
            return $this->_bootstrap(strtolower($resourceName));
        } else {
            return $this->_resources[$resourceName];
        }
        */
        return $this->_bootstrap(strtolower($resourceName));
    }
    
    public function __get($name)
    {
        return $this->getResource($name);
    }
    
    public function getResources()
    {
        return $this->_resources;
    }
    
    protected function _bootstrap($resourceName)
    {
        if (!array_key_exists($resourceName, $this->_resources)) {
            if (!isset($this->_classResources[$resourceName])) {
                throw new Ik_Application_Bootstrap_Exception('Could not find bootstrap resource - ' . $resourceName);
            }

            $this->_resources[$resourceName] = null;
            
            $this->_resources[$resourceName] = $this->{$this->_classResources[$resourceName]}();
            
            if (isset($this->_options['env']['debug_mode'])
                && $this->_options['env']['debug_mode'] === true) {
                Ik_Debug::getGenerateTime('BS - ' . $resourceName);
            }
        }
        
        return $this->_resources[$resourceName];
    }
    
    public function bootstrap($resourceName = 'bootstrap', $reboot = false)
    {
        if ($reboot) {
            if (array_key_exists($resourceName, $this->_resources)) {
                unset($this->_resources[$resourceName]);
            }
        }
        
        return $this->_bootstrap(strtolower($resourceName));
    }

    public function bootstrapResourses($resources, $reboot = false)
    {
        $resources = (array)$resources;
        
        if (empty($resources)) {
            throw new Ik_Application_Bootstrap_Exception('$resources is empty');
        }
        
        foreach ($resources as $resourceName) {
            $this->bootstrap($resourceName, $reboot);
        }
        
        return $this;
    }

    protected function _initBootstrap()
    {
        $this->initAll();
    }
    
    public function initAll()
    {
        foreach ($this->_classResources as $name => $method) {
            $this->bootstrap($name);
        }        
    }
        
    abstract public function run();    
}