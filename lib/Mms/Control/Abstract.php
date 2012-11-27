<?php
abstract class Mms_Control_Abstract
{
/******************************************************************************
 * SYSTEM
 ******************************************************************************/

    public function __construct($options = null)
    {
        if (!empty($options)) {
            if (is_array($options) || $options instanceof Zend_Config) {
                $this->setOptions($options);
            }  else {
                throw new Exception('Invalid option provided to constructor');
            }
        }
    }

    public function setOptions($options)
    {
        if (empty($options)) {
            return;
        }

        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif (!is_array($options)) {
            throw new Exception('setOptions() expects either an array or a Zend_Config object');
        }

        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if ($method == 'setOptions') {
                continue;
            }
            if (method_exists($this, $method)) {
                $this->{$method}($value);
            }
        }
    }

    public function getControlAlias()
    {
        $name = get_class($this);
        return lcfirst(substr($name, strlen('Mms_Control_')));
    }

    public static function getQuerySet($request)
    {
    }


/******************************************************************************
 * SPECIFICATION
 ******************************************************************************/

    protected $_specifications = null;

    public function getSpecification($specification = array())
    {
        if (!empty($this->_relateControls)) {
            foreach ($this->_relateControls as $control) {
                $specification['controls'][] = $control;
            }
        }

        if (!empty($this->_requireData)) {
            foreach ($this->_requireData as $alias => $need) {
                if ($need === false) {
                    continue;
                }
                $specification['params'][] = $alias;
            }
        }
        return $specification;
    }

/******************************************************************************
* VIEW
******************************************************************************/

    /**
     * @var Zend_View
     */
    protected $_view;

    protected $_template = 'default';

    protected $_renderOutput = null;

    public function getView()
    {
        if (empty($this->_view)) {
            $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
            if (null === $viewRenderer->view) {
                $viewRenderer->initView();
            }
            $this->_view = $viewRenderer->view;
            $this->_view->addScriptPath('../lib/Mms/Control/view/');
        }
        return $this->_view;
    }

    public function getViewScript($templateName ='default')
    {
        $alias = $this->getControlAlias();
        if ($alias == Mms_Manager::C_FORM) {
            $script = strtolower($alias) . '/' . $templateName . '.phtml';
        } else {
            $script = strtolower($alias) . '.phtml';
        }
        return $script;
    }


    public function setView($view)
    {
        $this->_view = $view;
    }

    public function getTemplate()
    {
        return $this->_template;
    }

    public function setTemplate($name)
    {
        $this->_template = $name;
    }
    public function render($templateName = null)
    {
        if ($this->isRendered()) {
            return $this->getRenderOutput();
        }

        $this->_dispatch();

        if ($templateName === null) {
            $templateName = $this->getTemplate();
        }

        $this->_render($templateName);

        return $this->getRenderOutput();
    }

    protected function _render($templateName)
    {
        $script = $this->getViewScript($templateName);

        $view = $this->getView();

        $this->_renderOutput = '';

        if (isset($this->_params['noRender']) && $this->_params['noRender'] === true) {
            return;
        }
        foreach ($this->_params as $alias => $value) {
            $view->$alias = $value;
        }

        $this->_renderOutput .= $view->render($script);
    }

    public function getRenderOutput()
    {
        return (string) $this->_renderOutput;
    }

    public function isRendered()
    {
        return !is_null($this->_renderOutput);
    }

    protected function _dispatch()
    {}

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

    public function clearParams($name = null)
    {
        if (null === $name) {
            $this->_params = array();
        } elseif (is_string($name) && isset($this->_params[$name])) {
            unset($this->_params[$name]);
        } elseif (is_array($name)) {
            foreach ($name as $key) {
                if (is_string($key) && isset($this->_params[$key])) {
                    unset($this->_params[$key]);
                }
            }
        }

        return $this;
    }

    public function hasParam($name)
    {
        return isset($this->_params[$name]);
    }

    public function unsetParam($name)
    {
        unset($this->_params[$name]);
    }

/******************************************************************************
* REQUIRED DATA
******************************************************************************/

    const P_DATA      = 'data';
    const P_TITLE     = 'title';
    const P_PARAMS    = 'params';
    const P_METADATA    = 'metadata';
    const P_FORM_TYPE   = 'formType';
    const P_EXPORT_DATA = 'exportData';

    //in specific control define keys only with true, that really need
    protected $_requireData = array(
        self::P_DATA      => false,
        self::P_TITLE     => false,
        self::P_PARAMS    => false,
        self::P_METADATA    => false,
        self::P_FORM_TYPE   => false,
        self::P_EXPORT_DATA => false,
    );

    public function getRequireData()
    {
        return $this->_requireData;
    }

    public function setRequiredData($alias, $set)
    {
        $this->setParams(array($alias => $set));
        $this->getView()->$alias = $set;
    }

    protected function _getMetadata($key)
    {
        $metadata = $this->getParams(self::P_METADATA);
        if (isset($metadata[$key])) {
            return $metadata[$key];
        }
        return null;
    }

/******************************************************************************
* PREPARED
******************************************************************************/

    protected $_isPrepared = false;

    public function isPrepared()
    {
        return $this->_isPrepared;
    }

    public function setPrepared($state)
    {
        $this->_isPrepared = $state;
    }

/******************************************************************************
* RELATION
******************************************************************************/

    protected $_relateControls = array();

    public function setRelateControls($controlSet)
    {
        $newRelated = array();
        foreach ($controlSet as $alias => $state) {
            if (in_array($alias, $this->_relateControls)) {
                $newRelated[$alias] = $state;
            }
        }
        if (count($newRelated) > 0) {
            $this->_relateControls = $newRelated;
        }
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