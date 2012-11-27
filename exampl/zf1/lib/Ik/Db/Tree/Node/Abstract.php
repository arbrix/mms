<?php
/*
 * $Id: Abstract.php 158 2010-09-04 16:49:32Z denis $
 * $HeadURL: http://www.interkassa.com/svn/interkassa1/trunk/lib/Ik/Db/Tree/Node/Abstract.php $
 * $LastChangedBy: denis $
 * $LastChangedDate: 2009-08-06$
 * $LastChangedRevision: 158 $
 */

abstract class Ik_Db_Tree_Node_Abstract extends Ik_Db_Table_Row_Abstract
{
    /**
     * Node children
     * @var Ik_Db_Tree_Nodeset_Abstract
     */
    protected $_children = null;
    
    public function getChildren()
    {
        if (null === $this->_children) {
            $this->loadChildren();
        }
        
        return $this->_children;
    } 
        
    /**
     * Load Children
     * 
     * @return this
     */
    public function loadChildren()
    {
        $this->_children = $this->getTable()->getChildren($this);

        return $this;
    }
    
    /**
     * Load branch
     * 
     * @return this
     */
    public function loadBranch()
    {
        $this->loadChildren();
        
        foreach ($this->_children as $child) {
            $child->loadBranch();
        }
        
        return $this;
    }
        
    /**
     * Get parent
     * 
     * @return Ik_Db_Tree_Node_Abstract
     */    
    public function getParent()
    {
        return $this->getTable()->getParent($this);
    }
    
    /**
     * Get Ancestor (Path)
     * 
     * @return Ik_Db_Tree_Nodeset_Abstract
     */
    public function getAncestor()
    {
        return $this->getTable()->getAncestor($this);
    }
    
    /**
     * Get deep
     * 
     * @return int
     */
    public function getDeep()
    {
        return $this->getTable()->getDeep($this);
    }    
    
    /**
     * Has children?
     * 
     * @param $node
     * @return bool
     */
    public function hasChildren()
    {
        return $this->getTable()->hasChildren($this);       
    }
        
    public function toArray($keys = null)
    {
        
        if (null === $keys) {
            $result = (array)$this->_data;
        } else {
            if (!is_array($keys)) {
                $keys = array($keys);
            }
            
            $result = array();
            foreach ($keys as $key) {
                if (array_key_exists($key, $this->_data)) {
                    $result[$key] = $this->_data[$key];
                }
            }
        }

        if (count($this->_children) > 0) {
            $result['_children'] = $this->_children->toArray($keys);
        }
        return $result;
    }    
    
    public function delete()
    {
        if (!$this->hasChildren()) {
            parent::delete();
        } else {
            throw new Ik_Db_Tree_Exception('Node could not be deleted because it has children (Id: ' . print_r($this->_getPrimaryKey(), true) . ')');
        }
    }
    
    public function deleteBranch()
    {
        if (null === $this->_children) {
            $this->loadChildren();
        }
                
        foreach ($this->_children as $child) {
            $child->deleteBranch();
        }
        
        parent::delete();
    }
    
    public function optimizeNode()
    {
        return $this->getTable()->optimizeNode($this);   
    }
        
    public function moveNodeTo($toNode, $position = Ik_Db_Tree_AdjacencyList::NODE_POS_LAST)
    {
        return $this->getTable()->moveNodeTo($this, $toNode, $position);
    }
}