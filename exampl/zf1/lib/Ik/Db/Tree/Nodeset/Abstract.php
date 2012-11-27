<?php
/*
 * $Id: Abstract.php 158 2010-09-04 16:49:32Z denis $
 * $HeadURL: http://www.interkassa.com/svn/interkassa1/trunk/lib/Ik/Db/Tree/Nodeset/Abstract.php $
 * $LastChangedBy: denis $
 * $LastChangedDate: 2009-08-06$
 * $LastChangedRevision: 158 $
 */

abstract class Ik_Db_Tree_Nodeset_Abstract extends Zend_Db_Table_Rowset_Abstract
{
    public function toArray($keys = null)
    {
        foreach ($this->_rows as $i => $row) {
            $this->_data[$i] = $row->toArray($keys);
        }
        return $this->_data;
    }    
}