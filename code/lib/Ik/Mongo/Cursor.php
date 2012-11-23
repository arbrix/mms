<?php
/*
 * INTERKASSA Ltd. (c)
 * 
 * $Id: Cursor.php 158 2010-09-04 16:49:32Z denis $
 * $HeadURL: http://www.interkassa.com/svn/interkassa1/trunk/lib/Ik/Mongo/Cursor.php $
 * $LastChangedBy: denis $
 * $LastChangedDate: 2010-09-04 19:49:32 +0300 (Сб, 04 сен 2010) $
 * $LastChangedRevision: 158 $
 */

class Ik_Mongo_Cursor extends MongoCursor
{
    public $documentClass;
    
    public function __construct($conn = null, 
                                $ns = null, 
                                $query = array(), 
                                $fields = array(),
                                $docClass = null) 
    {
        parent::__construct($conn, $ns, $query, $fields);
        
        $this->documentClass = $docClass;
    }
    
    public function next()
    {
        parent::next();
        
        Zend_Debug::dump($this->current);
        
        /*
        if ($this->documentClass !== null) {
            $this->current = new $this->documentClass(array('data' => $this->current));
        }*/
    }
}