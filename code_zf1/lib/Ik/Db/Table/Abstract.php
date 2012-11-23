<?php
/*
 * $Id: Abstract.php 158 2010-09-04 16:49:32Z denis $
 * $HeadURL: http://www.interkassa.com/svn/interkassa1/trunk/lib/Ik/Db/Table/Abstract.php $
 * $LastChangedBy: denis $
 * $LastChangedDate: 2010-09-04 19:49:32 +0300 (Сб, 04 сен 2010) $
 * $LastChangedRevision: 158 $
 */

abstract class Ik_Db_Table_Abstract extends Zend_Db_Table_Abstract
{
    protected $_rowClass = 'Ik_Db_Table_Row';
    
    public function __construct(array $config = array())
    {
        if ($this->_rowClass == 'Ik_Db_Table_Row') {
            $tableClass = get_class($this);
            $rowClass = substr($tableClass, 0, strrpos($tableClass, '_Table'));
        }
        if (@class_exists($rowClass)) {
            $this->_rowClass = $rowClass;
        }
        
        parent::__construct($config);
    }

    protected function _setupPrimaryKey()
    {
        // Set first colomn as primary if primary does not set
        try {
            parent::_setupPrimaryKey();
        } catch(Exception $ex) {
            $cols = $this->_getCols();
            
            $value = reset($cols);
            $key = key($cols);
            
            if (count($cols) > 0) {
                $this->_primary[$key] = $value;
            } else {
                throw $ex;
            }
            //Zend_Debug::dump($ex);
        }
        
    }
    
    public function getCols()
    {
        return parent::_getCols();
    }
    
    public function isColumnExist($name)
    {
        $cols = parent::_getCols();
        return array_search($name, $cols);
    }
    
    public function getPrimary()
    {
        $this->_setupPrimaryKey();
        
        return (array) $this->_primary; 
    }
    
    public function getTableName()
    {
        return $this->_name; 
    }    
    
    public function count($conditions = array())
    {
        $select = $this->select();
        $select->from($this, array('count(*) as count'));
        foreach ($conditions as $conditionKey => $conditionValue) {
            $select->where($this->getAdapter()->quoteIdentifier($conditionKey) . ' = ?', $conditionValue);
        }
        $count = $select->limit(1)->query()->fetchColumn();
        return $count;       
    }
    
    public function findRow()
    {
        $this->_setupPrimaryKey();
        $args = func_get_args();
        $keyNames = array_values((array) $this->_primary);
        
        if (count($args) < count($keyNames)) {
            require_once 'Zend/Db/Table/Exception.php';
            throw new Zend_Db_Table_Exception("Too few columns for the primary key");
        }
        
        if (count($args) > count($keyNames)) {
            require_once 'Zend/Db/Table/Exception.php';
            throw new Zend_Db_Table_Exception("Too many columns for the primary key");
        }
        
        $whereClause = null;
        $tableName = $this->_db->quoteTableAs($this->_name, null, true);
        foreach ($args as $keyPosition => $keyValue) {
            $type = $this->_metadata[$keyNames[$keyPosition]]['DATA_TYPE'];
            $columnName = $this->_db->quoteIdentifier($keyNames[$keyPosition], true);
            $whereClause = $this->_db->quoteInto(
                $tableName . '.' . $columnName . ' = ?',
                $keyValue, $type);
        }

        if ($whereClause === null) {
            return null;
        }
        
        return $this->fetchRow($whereClause);
    }
}