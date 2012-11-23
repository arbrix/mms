<?php
/*
 * INTERKASSA Ltd. (c)
 * 
 * $Id: Document.php 158 2010-09-04 16:49:32Z denis $
 * $HeadURL: http://www.interkassa.com/svn/interkassa1/trunk/lib/Ik/Mongo/Document.php $
 * $LastChangedBy: denis $
 * $LastChangedDate: 2010-09-04 19:49:32 +0300 (Сб, 04 сен 2010) $
 * $LastChangedRevision: 158 $
 */

class Ik_Mongo_Document implements Serializable, ArrayAccess, Countable, IteratorAggregate
{
    protected $_collectionClass;
    
    /**
     * Collection
     * @var Ik_Mongo_Collection
     */
    protected $_collection;
    
    protected $_isStored = false;

    protected $_isDataChanged = false;
    
    protected $_data = array();
    
    protected static $_dataDefaultValues = array();
      
/******************************************************************************
 * SYSTEM
 ******************************************************************************/
    
    public function __construct($options = null)
    {
        $this->_setupCollectionClass();    
        
        if (!empty($options)) {
            if (is_array($options) || $options instanceof Zend_Config) {
                $this->setOptions($options);
            }  else {
                throw new Ik_Object_Exception('Invalid option provided to constructor');
            }
        }
        
        $this->_init();
        
        $this->_isDataChanged = false;        
    } 

    protected function _init()
    {
    }    
    
    /**
     * Set options en masse
     *
     * @param  array|Zend_Config $options
     * @return void
     */
    public function setOptions($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif (!is_array($options)) {
            throw new Ik_Object_Exception('setOptions() expects either an array or a Zend_Config object');
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
    
/******************************************************************************
 * INTERFACES
 ******************************************************************************/
    
    public function __get($key)
    {
        return $this->getData($key);
    }
    
    public function __set($key, $value)
    {
        $this->setData($key, $value);
    }
    
    public function __isset($key)
    {
        return $this->hasData($key);
    }
    
    public function __unset($key)
    {
        $this->unsetData($key);
    }
    
    public function offsetExists($offset) 
    {
        return $this->hasData($offset);
    }

    public function offsetGet($offset) 
    {
        return $this->getData($offset);
    }

    public function offsetSet($offset, $value) 
    {
        $this->setData($offset, $value);
    }

    public function offsetUnset($offset) 
    {
        $this->unsetData($offset);
    }    
    
    public function count()
    {
        return count($this->_data);
    }
    
    public function getIterator() 
    {
        return new ArrayIterator($this->_data); 
    }
    
    public function serialize()
    {
        return serialize($this->_data);
    }
    
    public function unserialize($data)
    {
        $this->_data = unserialize($data); 
    }

/******************************************************************************
 * DATA
 ******************************************************************************/    
    
    public function getData($path = null, $default = null, $i18nOff = false)
    {
        if ($path === null) {
            return $this->_data;
        }
        
        if ($default === null && isset(static::$_dataDefaultValues[$path])) {
            $default = static::$_dataDefaultValues[$path];
        }
        
        $pathKeys = explode('.', $path);
        $return =& $this->_data;
        foreach ($pathKeys as $key) {
            if (isset($return[$key])) {
                $return =& $return[$key];
            } else {
                return $default;
            } 
        }
        
        if ($i18nOff === false && self::isI18nData($return)) {
            return Ik_I18n::getI18nValue($return);
        } 
        
        return $return;
    }
    
    public function setData($path, $value = null, $append = false) 
    {
        if ($value === null && is_array($path)) {
            $this->_data = (array) $path;
        } else {
            $pathKeys = explode('.', $path);
            $dataRef =& $this->_data;
            foreach ($pathKeys as $count => $path) {
                if (!isset($dataRef[$path]) && $count != count($pathKeys)) {
                    $dataRef[$path] = array();
                }
                $dataRef =& $dataRef[$path]; 
            }
            
            if ($dataRef === $value
                || (isset(static::$_dataDefaultValues[$path])
                    && static::$_dataDefaultValues[$path] === $value)
            ) {
                return $this;
            }
            
            if ($append === true) {
                $dataRef[] = $value;
            } else {
                $dataRef = $value;
            }
        }

        $this->_isDataChanged = true;
        
        return $this;
    }

    public function addData($dataSet)
    {
        foreach($dataSet as $dataPath => $dataValue) {
            $this->setData($dataPath, $dataValue);
        }
        
        return $this;
    }
    
    public function hasDataDefaultValue($path) 
    {
        return isset(static::$_dataDefaultValues[$path]);
    }    
    
    public function hasData($path) 
    {
        $pathKeys = explode('.', $path);
        $return =& $this->_data;
        foreach ($pathKeys as $path) {
            if (isset($return[$path])) {
                $return =& $return[$path];
            } else {
                return false;
            } 
        }
        
        return true;
    }
    
    /**
     * hasData() function alias
     */
    public function issetData($path) 
    {
        return $this->hasData($path);
    }
    
    public function unsetData($path)
    {
        $this->_isDataChanged = true;
        unset($this->_data[$path]);
    }
    
    protected function _cleanData($data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->_cleanData($value);
            }
            if (empty($data[$key])) {
                if ($data[$key] != 0) {
                    unset($data[$key]);
                }
            }
        }
        return $data;
    }
    
    public function findInData($findPath, $needle, $key = null, $default = null)
    {
        $dataSet = $this->getData($findPath, array()); 
        foreach ($dataSet as $data) {
            $value = $data;
            if ($key !== null && isset($data[$key])) {
                $value = $data[$key];
            }
            if ($value == $needle) 
            {
                return $data; 
            }
        }
        return $default;
    }

    public function isDataChanged()
    {
        return $this->_isDataChanged;
    }
    
    public function toArray($packed = false, $mapReduce = null)
    {
        if (!$packed) {
            return (array) $this->_data;
        } else {
            $packed = (array) $this->_data;
            $this->toArrayPack($packed, $mapReduce);
            return $packed;
        }
    }
    
    public static function toArrayPack(&$data, $mapReduce = null, $path = null)
    {
        foreach ($data as $key => &$value) {
            if ($path === null) {
                $dataPath = (string) $key;
            } else {
                $dataPath = $path . '.' . (string) $key;
            }
            
            if (!empty($mapReduce)) {
                $unset = true;
                foreach ($mapReduce as $mapReducePath) {
                    if (strpos($dataPath, $mapReducePath) !== false) {
                        $unset = false;
                        break;
                    }
                }
                if ($unset) {
                    unset($data[$key]);
                    continue;
                }
            }
            
            if ($value instanceof MongoId) {
                $data[$key] = (string) $value;
            } elseif (self::isI18nData($value)) {
                $data[$key] = Ik_I18n::getI18nValue($value);
            } elseif (is_array($value)) {
                self::toArrayPack($value, $mapReduce, $dataPath);
            }
        }
        
        return $data;
    }

    public function moveData($oldPath, $newPath, $type = null)
    {
        $data = $this->getData($oldPath, null, true);
        if (!empty($data)) {
            if ($type !== null) {
                settype($data, $type);
            }
            $this->setData($newPath, $data);
        }
        $this->unsetData($oldPath);        
    }
    
/******************************************************************************
 * MONGO DOCUMENT
 ******************************************************************************/    
    
    protected function _setupCollectionClass()
    {
        if (!empty($this->_collectionClass)) {
            return;
        }
        
        $collClass = get_class($this);
        $rowClass = substr($collClass, 0, strrpos($collClass, '_Collection'));
        if (@class_exists($collClass)) {
            $this->_collectionClass = $collClass;
        }        
    }
    
    public function getId()
    {
        return $this->_id;
    }
    
    public function getIdDatetime()
    {
        return date('c', $this->_id->getTimestamp());
    }    
    
    public function getCollection()
    {
        if ($this->_collection === null) {
            $this->_collection = Ik_Mongo::getCollection($this->_collectionClass);
        }
        
        return $this->_collection;
    }     
    
    public function setCollection($coll)
    {
        $this->_collection = $coll;
    }
    
    public function isStored()
    {
        return $this->_isStored;
    }     
    
    public function setStored($isStored = true)
    {
        $this->_isStored = $isStored;
        
        if ($this->_isStored) {
            $this->_isDataChanged = false;
        }
    }
        
    public function save()
    {
        if ($this->_isStored === true && $this->_isDataChanged === false) {
            return;
        }
        
        $data = $this->_cleanData($this->_data);
        try {
            if ($this->_isStored == true) {
                return $this->getCollection()->update(
                    array('_id' => $this->_data['_id']), 
                    $data, 
                    array(
                        'multiple' => false, 
                        'safe' => true
                    )
                );
            } else {
                return $this->getCollection()->insert($data, true);
            }
        } catch(MongoException $e) {
            throw new Ik_Mongo_Exception('Error while saving the object!', 0, $e);
        } 
    }
    
    public function delete()
    {
        if ($this->_isStored === false || empty($this->_id)) {
            return;
        }        
        
        return $this->getCollection()->remove(array(
                '_id' => $this->_id
            ), array(
                'justOne' => true,
                'safe' => true,
            )
        );
    }

    public static function isI18nData($data)
    {
        return (is_array($data) 
            && isset($data[0]) 
            && count($data[0]) == 2 
            && isset($data[0]['l'])
            && isset($data[0]['v'])
        );
    }

    // TODO: refactor using Ik_I18n::getI18nValue()
    /*
    public static function getI18nValue($data, $langs = null)
    {
        return Ik_I18n::getI18nValue($data, $langs);
    } 
    */   
    
/******************************************************************************
 * END
 ******************************************************************************/    
}