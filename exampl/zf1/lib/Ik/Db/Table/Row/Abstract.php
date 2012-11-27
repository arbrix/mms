<?php
/*
 * $Id: Abstract.php 158 2010-09-04 16:49:32Z denis $
 * $HeadURL: http://www.interkassa.com/svn/interkassa1/trunk/lib/Ik/Db/Table/Row/Abstract.php $
 * $LastChangedBy: denis $
 * $LastChangedDate: 2010-09-04 19:49:32 +0300 (Сб, 04 сен 2010) $
 * $LastChangedRevision: 158 $
 */

abstract class Ik_Db_Table_Row_Abstract extends Zend_Db_Table_Row_Abstract
{
    protected $_saveRefresh = false;
    
	public function __construct(array $config = array())
	{
	    if ($this->_tableClass === null) {
            $tableClass = get_class($this) . '_Table';
            
            if (@class_exists($tableClass)) {
                $this->_tableClass = $tableClass;
            }
	    }
        
        parent::__construct($config);
	}
	
    protected function _insert()
    {
        if (isset($this->created)) {
            $this->created = new Zend_Db_Expr('NOW()');
        }
    }
	    
    protected function _update()
    {
        if (isset($this->updated)) {
            $this->updated = new Zend_Db_Expr('NOW()');
        }
    }
    
    public function getPrimaryKeys()
    {
        return $this->getTable()->info('primary');
    }
    
    public function getPrimaryValues()
    {
        if ($this->isLoaded()) {
            $primaryKeys = $this->getPrimaryKeys();
            $primaryValues = array();
            foreach ($primaryKeys as $key) {
                $primaryValues[$key] = $this[$key];
            }
            return $primaryValues;
        } else {
            throw new Ik_Db_Table_Row_Exception(get_class($this) . ' is not loaded');
        }
    }
    
    /**
     * Load by primary key(s)
     *
     * @param unknown_type $primaryKeyValue
     */
    public function load()
    {
        $args = func_get_args();
        
        $colsNames = array_values((array) $this->getTable()->info(Zend_Db_Table_Abstract::COLS));
        $primaryKeys = array_values((array) $this->getTable()->info(Zend_Db_Table_Abstract::PRIMARY));
        
        $primaryValues = array();
        if (count($args) == 1 && is_array($args[0])) {
            $primaryValues = $args[0];
            foreach ($primaryValues as $primaryKey => $primaryValue) {
                if (array_search($primaryKey, $primaryKeys) === false) {
                    unset($primaryValues[$primaryKey]);
                }
            }
        } else {
            foreach ($primaryKeys as $primaryKey) {
                $primaryValues[$primaryKey] = array_shift($args); 
            }
        }
        
        if (count($primaryValues) < count($primaryKeys)) {
            throw new Zend_Db_Table_Exception("Too few columns for the primary key");
        }

        if (count($primaryValues) > count($primaryKeys)) {
            throw new Zend_Db_Table_Exception("Too many columns for the primary key");
        }

        
        try {
            foreach ($primaryValues as $primaryKey => $primaryValue) {
                $this->_data[$primaryKey] = $primaryValue;
            }
            $this->_refresh();
            return true;
        } catch (Exception $ex) {
            $this->_data = array();
            return false;
        }
    }
    
    /**
     * Save changes without refresh
     *
     * @param unknown_type $primaryKeyValue
     */
    public function save($refresh = false)
    {
        if ($refresh) {
            $this->_saveRefresh = true;
        }
    
        parent::save();
    }
    
    /**
     * Refreshes properties from the database.
     *
     * @return void
     */
    protected function _refresh()
    {
        if ($this->_saveRefresh == true || empty($this->_cleanData)) {
            parent::_refresh();
        }
    }    
    
    /**
     * Set row from another row
     *
     * @param unknown_type $primaryKeyValue
     */
    public function setFromRow($row)
    {
        if (null === $row) {
            throw new Ik_Db_Table_Row_Exception('Cannot refresh row as parent is missing');
        }

        $this->_data = $row->toArray();
        $this->_cleanData = $this->_data;
        $this->_modifiedFields = array();
    }

    public function isLoaded()
    {
        return !empty($this->_cleanData);
    }
}