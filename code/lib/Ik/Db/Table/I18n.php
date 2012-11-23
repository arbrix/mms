<?php
/*
 * $Id: I18n.php 158 2010-09-04 16:49:32Z denis $
 * $HeadURL: http://www.interkassa.com/svn/interkassa1/trunk/lib/Ik/Db/Table/I18n.php $
 * $LastChangedBy: denis $
 * $LastChangedDate: 2010-09-04 19:49:32 +0300 (Сб, 04 сен 2010) $
 * $LastChangedRevision: 158 $
 */

class Ik_Db_Table_I18n extends Ik_Db_Table_Abstract
{
    protected $_lang = null;
    
    public function __construct(array $config = array())
    {
        if (isset($config['lang']) && !empty($config['lang'])) {
            $this->setLang($config['lang']);
        } else {
            $this->setLang(Zend_Registry::get('lang'));
        }
        
        parent::__construct($config);
    }
    
    public function setLang($lang) 
    {
        $this->_lang = $lang;
    }
    
    public function getLang() 
    {
        return $this->_lang;
    }
    
    /**
     * Get i18n select
     * 
     * @return Zend_Db_Select
     */
    public function i18nSelect($withFromPart = self::SELECT_WITHOUT_FROM_PART)
    {
        $select = parent::select($withFromPart);
        
        $select->setIntegrityCheck(false);
        $select->assemble();
        
        $primary = $this->getPrimary();
        $primary = reset($primary);
                
        $select->joinInner(
            array('i18n' => $this->_name . '_i18n'), 
            $this->_name . '.' . $primary . ' = i18n.' . $primary
        );
        
        $select->where('`i18n`.`lang` = ?', $this->getLang());
        
        return $select;
    }
    
    public function i18nFetchAll($lang = null)
    {
        if (null !== $lang) {
            $this->setLang($lang);
        }
        
        $select = $this->i18nSelect();
        return parent::fetchAll($select);
    }
}