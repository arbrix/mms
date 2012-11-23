<?php
/*
 * INTERKASSA Ltd. (c)
 * 
 * $Id: Collection.php 158 2010-09-04 16:49:32Z denis $
 * $HeadURL: http://www.interkassa.com/svn/interkassa1/trunk/lib/Ik/Db.php $
 * $LastChangedBy: denis $
 * $LastChangedDate: 2010-09-04 19:49:32 +0300 (Сб, 04 сен 2010) $
 * $LastChangedRevision: 158 $
 */

class Ik_Db
{
    static protected $_tableSet;
        
    /**
     * Get table
     * 
     * @param string $class
     * @param array $options
     * @return Ik_Db_Table_Abstract
     */
    static public function getTable($class, $options = array())
    {
        if (isset(self::$_tableSet[$class])) {
            return self::$_tableSet[$class];
        }
        
        if (@class_exists($class)) {
            $table = new $class($options);
        } else {
            $table = new Ik_Db_Table(array(
            	'name' => $class
            ));
        }
        
        self::$_tableSet[$class] = $table;
        
        return $table; 
    }   
}