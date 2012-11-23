<?php
/*
 * Interkassa LTD.
 * http://www.interkassa.com/
 * 
 * $Id: I18n.php 158 2010-09-04 16:49:32Z denis $
 * $HeadURL: http://www.interkassa.com/svn/interkassa1/trunk/lib/Ik/Db/Table/Row/I18n.php $
 * $LastChangedBy: denis $
 * $LastChangedDate: 2010-09-04 19:49:32 +0300 (Сб, 04 сен 2010) $
 * $LastChangedRevision: 158 $
 */

class Ik_Db_Table_Row_I18n extends Ik_Db_Table_Row_Abstract
{
    /**
     * Auto setup I18n table & row in __construct
     * @var bool
     */
    protected $_autoSetupI18n = false;
    
    /**
     * I18n row
     * @var Ik_Db_Table_Row_Abstract
     */
    protected $_i18nRow = null;
    
    /**
     * I18n table
     * @var Ik_Db_Table_Abstract
     */
    protected $_i18nTable = null;
    
	public function __construct(array $config = array())
	{
        parent::__construct($config);
        
        if ($this->_autoSetupI18n === true) {
            $this->_setupI18nTable();
            $this->_setupI18nRow();
        }
	}
	
    public function __get($columnName)
    {
        $columnName = $this->_transformColumn($columnName);
        if (isset($this->_i18nRow)
            && $this->_i18nRow->isLoaded()
            && isset($this->_i18nRow->{$columnName})) {
            return $this->_i18nRow->{$columnName};
        }
        
        return parent::__get($columnName);
    }
    	
    protected function _setupI18nTable()
    {
        $tableClass = get_class($this) . '_I18n_Table';
        if (@class_exists($tableClass)) {
            $this->_i18nTable = new $tableClass();
        } else {
            $tableConfig = array(
                'name' => $this->getTable()->info('name') . '_i18n',
            );
            $this->_i18nTable = new Ik_Db_Table($tableConfig);
        }
    }
    
    protected function _setupI18nRow()
    {
        // I18n row is set?
        if (null !== $this->_i18nRow) {
            return $this->_i18nRow;
        }

        // I18n table is set?
        if (null === $this->_i18nTable) {
            $this->_setupI18nTable();
        }
        
        // Get lang keys
        $lang = Zend_Registry::get('lang');
        $langDef = Zend_Registry::get('langDef');
        
        // Get primary values for current row
        $primaryValues = $this->getPrimaryValues();

        // Try find i18n for current language 
        $findValues = array_merge($primaryValues, (array)$lang);
        $i18nRow = call_user_func_array(array($this->_i18nTable, 'find'), $findValues)->current();
        
        // Try find i18n for default language
        if (null === $i18nRow) {
            // Get all i18n rows
            $select = $this->_i18nTable->select();
            foreach ($primaryValues as $primaryKey => $primaryValue) {
                $select->where($primaryKey . ' = ?', $primaryValue);
            }
            $i18nRowSet = $this->_i18nTable->fetchAll($select);
            
            // Search & get default i18n row
            if ($i18nRowSet->count() == 0) {
                throw new Ik_Db_Table_Row_Exception('Couldn\'t find any i18n row for current row');
            } else {
                foreach ($i18nRowSet as $i18nRow) {
                    if ($i18nRow->lang == $langDef) {
                        break;
                    }
                }
            }
        }

        return $this->_i18nRow = $i18nRow; 
    }    
    
	public function getI18n($key = null)
	{
	    if (!isset($this->_i18nRow)) {
	        $this->_setupI18nRow();
	    }

	    if (null !== $key) {
            return $this->_i18nRow->{$key};
	    } else {
	        return $this->_i18nRow->toArray();
	    }
	}
	
	public function setupI18n()
	{
        if (!isset($this->_i18nTable)) {
            $this->_setupI18nTable();
        }
        if (!isset($this->_i18nRow)) {
            $this->_setupI18nRow();
        }
	}
	
    public function toArray()
    {
        if (isset($this->_i18nRow)
            && $this->_i18nRow->isLoaded()) {
            return array_merge((array)$this->_data, $this->_i18nRow->toArray());
        } else {
            return parent::toArray();
        }
    }	
}