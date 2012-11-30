<?php
/**
 * Interkassa Ltd.
 * Info: Mms/Entity/Abstract.php 2012.11.19 14:02:00 Kutuzov Ivan
 */
abstract class Mms_Entity_Abstract
{
/******************************************************************************
* SYSTEM
******************************************************************************/
    public function __construct($options = array())
    {
        if (empty($options)) {
            return;
        }
        foreach ($options as $alias => $value) {
            $methodName = 'set' . ucfirst($alias);
            if (method_exists($this, $methodName)) {
                $this->$methodName($value);
            }
        }
    }

/******************************************************************************
* SPECIFIC ENTITY
******************************************************************************/
    /**
     * @var Ik_Mongo_Document | Ik_Db_Table_Row
     */
    protected $_specificEntity;
    protected $_specificEntityClass;

    public function setSpecificEntity($specificEntity)
    {
        $this->_specificEntity = $specificEntity;
        $this->_specificEntityClass = get_class($specificEntity);
    }

    /**
     * @return Mms_Storage_Abstract
     */
    public function getSpecificEntity()
    {
        return $this->_specificEntity;
    }

    public function callSpecificEntity($methodName, $params = array())
    {
        if (method_exists($this->_specificEntity, $methodName)) {
            if (empty($params)) {
                return $this->_specificEntity->$methodName();
            } else {
                return $this->_specificEntity->$methodName($params);
            }
        }
    }

/******************************************************************************
* STORAGE
******************************************************************************/

    /**
     * @var Mms_Storage_Abstract
     */
    protected $_storage;
    protected $_storageClass;

    public function setStorage(Mms_Storage_Abstract $storage)
    {
        $this->_storage = $storage;
        $this->_storageClass = get_class($storage);
    }

    /**
     * @return string - storage class
     */
    public function getStorageClass()
    {
        return $this->_storageClass;
    }

    /**
     * @return Mms_Storage_Abstract
     */
    public function getStorage()
    {
        return $this->_storage;
    }

/******************************************************************************
* DATA
******************************************************************************/

    protected $_changed = false;
    protected $_data;

    public function getData()
    {
        if ($this->_data === null) {
            $this->_data = $this->_loadData();
        }
        return $this->_data;
    }

    abstract protected function _loadData();

    public function setData($data)
    {
        $data += $this->_data;
        $this->_changed = true;
        $this->_data = $data;
        Zend_Debug::dump($this->_data);
    }

    public function save()
    {
        if ($this->_changed === true) {
            $this->_saveData();
        }
    }

    abstract protected function _saveData();

/******************************************************************************
* END.
******************************************************************************/
}