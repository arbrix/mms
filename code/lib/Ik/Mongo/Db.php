<?php
/*
 * INTERKASSA Ltd. (c)
 * 
 * $Id: Db.php 158 2010-09-04 16:49:32Z denis $
 * $HeadURL: http://www.interkassa.com/svn/interkassa1/trunk/lib/Ik/Mongo/Db.php $
 * $LastChangedBy: denis $
 * $LastChangedDate: 2010-09-04 19:49:32 +0300 (Сб, 04 сен 2010) $
 * $LastChangedRevision: 158 $
 */

class Ik_Mongo_Db extends MongoDB
{
    /**
     * Default DB object.
     *
     * @var MongoDB
     */
    protected static $_defaultDb;
    
    public static function setDefaultDb($db = null)
    {
        self::$_defaultDb = self::setupDb($db);
    }

    public static function getDefaultDb()
    {
        return self::$_defaultDb;
    }  

    public static function setupDb($db)
    {
        if ($db === null) {
            return null;
        }
        if (is_string($db)) {
            //Zend_Debug::dump($db,__METHOD__);
            $db = Zend_Registry::get($db);
        }
        if (!$db instanceof MongoDB) {
            throw new Ik_Mongo_Exception('Argument must be of type Ik_Mongo_Db, or a Registry key where a Ik_Mongo_Db object is stored');
        }
        return $db;
    }      
}