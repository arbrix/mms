<?php
/*
 * INTERKASSA Ltd. (c)
 * 
 * $Id: Connection.php 158 2010-09-04 16:49:32Z denis $
 * $HeadURL: http://www.interkassa.com/svn/interkassa1/trunk/lib/Ik/Mongo/Connection.php $
 * $LastChangedBy: denis $
 * $LastChangedDate: 2010-09-04 19:49:32 +0300 (Сб, 04 сен 2010) $
 * $LastChangedRevision: 158 $
 */

class Ik_Mongo_Connection extends Mongo
{
    
    public static $defaultConnServer = 'mongodb://localhost:27017';
    
    /**
     * Default connection
     * 
     * @var Ik_Mongo_Connection
     */
    protected static $_defaultConn;
    
    public function __construct($server = null, $options = array('connect' => true)) 
    {    
        if ($server === null) {
            $server = self::$defaultConnServer;
        }        
        
        parent::__construct($server, $options);
        
        if (self::$_defaultConn === null) {
            self::$_defaultConn = $this;
        }
    }
    
    public static function setupDefaultConn($server = null, $options = array('connect' => true))
    {
        if ($server === null) {
            $server = self::$defaultConnServer;
        }
        self::setDefaultConn(new self($server, $options));
        return self::$_defaultConn;
    }
    
    public static function setDefaultConn($conn = null)
    {
        self::$_defaultConn = self::_setupConn($conn);
    }    

    /**
     * @return Ik_Mongo_Connection
     */
    public static function getDefaultConn()
    {
        //Zend_Debug::dump(self::$_defaultConn === null, __METHOD__);
        if (self::$_defaultConn === null) {
            
            self::setupDefaultConn();
        }
        //Zend_Debug::dump(self::$_defaultConn, 'defaultConn: ');
        return self::$_defaultConn;
    }

    protected static function _setupConn($conn)
    {
        if ($conn === null) {
            return null;
        }
        if (is_string($conn)) {
            $conn = Zend_Registry::get($conn);
        }
        if (!$conn instanceof Mongo) {
            throw new Ik_Mongo_Exception('Argument must be of type Mongo, or a Registry key where a Mongo object is stored');
        }
        return $conn;
    } 
}