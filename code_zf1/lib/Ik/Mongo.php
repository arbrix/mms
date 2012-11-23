<?php
/*
 * INTERKASSA Ltd. (c)
 * 
 * $Id: Collection.php 158 2010-09-04 16:49:32Z denis $
 * $HeadURL: http://www.interkassa.com/svn/interkassa1/trunk/lib/Ik/Mongo.php $
 * $LastChangedBy: denis $
 * $LastChangedDate: 2010-09-04 19:49:32 +0300 (Сб, 04 сен 2010) $
 * $LastChangedRevision: 158 $
 */

class Ik_Mongo 
{
    //protected static $_connectionSet;
    
    //protected static $_dbSet;
    
    protected static $_collectionSet;
    /*
    protected static $_collectionsClassMap = array(
        'paysystem' => 'Ik_Paysystem_Collection',
    );
    */

    /**
     * Get static collection
     * 
     * @param string $class
     * @param array $options
     * @return Ik_Mongo_Collection
     */
    public static function getCollection($class, $options = null)
    {
        if (isset(self::$_collectionSet[$class])) {
            return self::$_collectionSet[$class];
        }
        
        if (@class_exists($class)) {
            $collection = new $class($options);
        } else {
            $collection = new Ik_Mongo_Collection(array(
            	'name' => $class
            ));
        }
        
        self::$_collectionSet[$class] = $collection;
        
        return $collection; 
    }

    public static function isMongoId($value)
    {
        if ($value instanceof MongoId) {
            return true;
        } elseif (is_string($value)) {
            return (strlen($value) == 24 && ctype_xdigit($value));
        } else {
            return false;
        }
    }
}