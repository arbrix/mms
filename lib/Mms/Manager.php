<?php
/**
 * Interkassa Ltd.
 * Info: Mms/Manager.php 2012.11.19 14:02:00 Kutuzov Ivan
 */
class Mms_Manager
{
/******************************************************************************
 * SYSTEM
 ******************************************************************************/

    public function __construct($options = array())
    {
        if (empty($options)) {
            return;
        }
        if (is_array($options) || $options instanceof Zend_Config) {
            if ($options instanceof Zend_Config) {
                $options = $options->toArray();
            } elseif (!is_array($options)) {
                throw new Mms_Exception('Construct expects either an array or a Zend_Config object');
            }

            foreach ($options as $key => $value) {
                $method = 'set' . ucfirst($key);
                if (method_exists($this, $method)) {
                    $this->{$method}($value);
                }
            }
        }  else {
            throw new Mms_Exception('Invalid option provided to constructor');
        }
    }

/******************************************************************************
 * INTERFACE
 ******************************************************************************/

    public function generateInterface()
    {
        $output = '';
        $renderedControlSet = $this->getControlRenderResult();
        foreach ($renderedControlSet as $controlRender) {
            $output .= $controlRender;
        }
        return $output;
    }

    public function getControlRenderResult()
    {
        $renderedControlSet = array();
        $this->processOperation();
        $controlAliasSet = $this->_getControlAliasArray();
        foreach ($controlAliasSet as $controlAlias) {
            $control = $this->getControl($controlAlias);
            $renderedControlSet[$controlAlias] = $control->render();
        }
        return $renderedControlSet;
    }

    public function processOperation()
    {
        $request = $this->getRequest();
        $operation = $request->getParam('operation');
        if (!$request->isPost() || ($operation == null)) {
            return;
        }
        $storage = $this->getStorage();
        $storage->processOperation($request, $this->getParams());
    }

    public function getData()
    {

    }

/******************************************************************************
 * STORAGE
 ******************************************************************************/

    /**
     * @var Mms_Storage_Abstract
     */
    protected $_storage;
    protected $_storageAlias;
    protected $_storageClass;

    public function setStorageAlias($storageAlias)
    {
        $this->_storageAlias = $storageAlias;
    }

    public function getStorageClass()
    {
        return $this->_storageClass;
    }

    public function getStorage()
    {
        if ($this->_storage === null) {
            if (empty($this->_storageAlias)) {
                throw new Mms_Exception('Storage alias can\'t be empty');
            }
            $storageAlias = $this->_storageAlias;
            $storageAlias = Zend_Filter::filterStatic($storageAlias, 'Word_CamelCaseToUnderscore');
            $storageAlias = Zend_Filter::filterStatic($storageAlias, 'Word_SeparatorToCamelCase', array('-'));

            $class = 'Mms_Model_' . ucfirst($storageAlias) . '_Storage';
            if (!@class_exists($class)) {
                throw new Mms_Exception('Storage does not exist - ' . $class);
            }
            $options = array();
            $options['select']['querySet'] = $this->_gatherQuerySet();
            $options['request'] = $this->getRequest();
            $this->_storage = new $class($options);
            $this->_storageClass = $class;
        }
        return $this->_storage;
    }

/******************************************************************************
* CONTROLS
******************************************************************************/

    const C_FILTER    = 'filter';
    const C_DATAGRID  = 'datagrid';
    const C_PAGINATOR = 'paginator';
    const C_FORM      = 'form';

    /**
     * @var Mms_Control_Abstract[]
     */
    protected $_controlSet;

    protected $_controlAliasArray = array(
        self::C_FILTER,
        self::C_DATAGRID,
        self::C_PAGINATOR,
        self::C_FORM,
    );

    protected $_controlsParamSet = array();

    public function setControlSet($controlSet)
    {
        foreach ($controlSet as $alias => $control) {
            $this->_controlSet[$alias] = $control;
        }
    }

    public function getControl($controlAlias)
    {
        if (!($this->_controlSet[$controlAlias] instanceof Mms_Control_Abstract)) {
            $control = Mms_Control::factory($controlAlias);
            $this->_determineControlParamAlias($control);
            $this->_setControlsParamSet();
            $control->setRequest($this->getRequest());
            $this->_controlSet[$controlAlias] = $control;
            $this->_prepareControl($control);
        }

        return $this->_controlSet[$controlAlias];
    }

    protected function _getControlAliasArray()
    {
        if ($this->_controlSet === null) {
            $formType = $this->getParams(self::C_FORM, Mms_Control_Form::TYPE_CREATE);
            if ($formType == Mms_Control_Form::TYPE_UPDATE) {
                $this->_controlSet = array(self::C_FORM => self::C_FORM);
                return array(self::C_FORM);
            }
            $this->_controlSet = array(
                self::C_FILTER => self::C_FILTER,
                self::C_DATAGRID => self::C_DATAGRID,
                self::C_PAGINATOR => self::C_PAGINATOR,
                self::C_FORM => self::C_FORM,
            );
        }

        return array_keys($this->_controlSet);
    }

    protected function _determineControlParamAlias(Mms_Control_Abstract $control)
    {
        $requireData = $control->getRequireData();
        if (empty($requireData)) {
            return;
        }
        $this->_controlsParamSet = array_merge($this->_controlsParamSet, $requireData);
    }

    protected function _setControlsParamSet()
    {
        foreach (array_keys($this->_controlsParamSet) as $valueAlias) {
            $methodName = '_setControlParam' . ucfirst($valueAlias);
            if (method_exists($this, $methodName)) {
                $this->_controlsParamSet[$valueAlias] = $this->$methodName();
            }
        }
    }

    protected function _setControlParamParams()
    {
        $storage = $this->getStorage();
        return array(
            'model' => $storage->getMetadata(Mms_Storage_Abstract::MD_NAME),
            'countData' => $storage->getEntitiesCount()
        );
    }

    protected function _setControlParamFormType()
    {
        return $this->getParams(self::C_FORM, Mms_Control_Form::TYPE_CREATE);
    }

    protected function _setControlParamData()
    {
        $storage = $this->getStorage();
        return $storage->getDataSet();
    }

    protected function _setControlParamProcData()
    {
        $storage = $this->getStorage();
        return $storage->getProcDataSet();
    }

    protected function _setControlParamExportData()
    {
        $storage = $this->getStorage();
        $storage->isExport = true;
        return $storage->getDataSet(false, true);
    }

    protected function _setControlParamTitle()
    {
        $storage = $this->getStorage();
        return $storage->getTitle();
    }

    protected function _setControlParamMetadata()
    {
        $storage = $this->getStorage();
        return $storage->getMetadata();
    }

    protected function _prepareControl(Mms_Control_Abstract $control)
    {
        if ($control->isPrepared() === true) {
            return;
        }
        $specification = $control->getSpecification();
        if (isset($specification['controls']) && !empty($specification['controls'])) {
            $controlSet = $this->_controlSet;
            $controls = array();
            foreach ($specification['controls'] as $relatedControlAlias) {
                if ($controlSet[$relatedControlAlias]->isPrepared() === false) {
                    $this->_prepareControl($relatedControlAlias);
                }
                $controls[$relatedControlAlias] = $controlSet[$relatedControlAlias]->isPrepared();
            }
            $control->setRelateControls($controls);
        }
        if (isset($specification['params']) && !empty($specification['params'])) {
            $paramSet = array();
            foreach ($specification['params'] as $paramKey) {
                $paramSet[$paramKey] = $this->_controlsParamSet[$paramKey];
            }
            $control->setParams($paramSet);
        }
        $control->setPrepared(true);
    }

/******************************************************************************
* GATHERING QUERY FROM CONTROLS
******************************************************************************/

    protected function _gatherQuerySet()
    {
        $controlAliasSet = $this->_getControlAliasArray();

        $request = $this->getRequest();
        $querySet = array();
        foreach ($controlAliasSet as $controlAlias) {
            $controlName = 'Mms_Control_' . ucfirst($controlAlias);
            $addToQuery = $controlName::getQuerySet($request);
            if ($addToQuery != null) {
                if (!empty($querySet)) {
                    $querySet = array_merge($querySet, $addToQuery);
                } else {
                    $querySet = $addToQuery;
                }
            }
        }
        return $querySet;
    }

/******************************************************************************
 * PARAMS
 ******************************************************************************/

    protected $_params = array();

    /**
     * @param array $params
     */
    public function setParams(array $params)
    {
        if (is_array($this->_params)) {
            $this->_params = array_merge($this->_params, $params);
        } elseif (!empty($this->_params)) {
            $this->_params = array_merge($params, array($this->_params));
        } else {
            $this->_params = $params;
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getParams($key = null, $default = null)
    {
        if ($key === null) {
            return $this->_params;
        } elseif (isset($this->_params[$key])) {
            return $this->_params[$key];
        }
        return $default;
    }

    public function hasParam($name)
    {
        return isset($this->_params[$name]);
    }

/******************************************************************************
 * REQUEST
 ******************************************************************************/

    /**
     * @var Zend_Controller_Request_Http
     */
    protected $_request;

    public function setRequest(Zend_Controller_Request_Http $request)
    {
        $this->_request = $request;
    }

    public function getRequest()
    {
        return $this->_request;
    }

/******************************************************************************
* END.
******************************************************************************/
}