<?php
/*
 * $Id: AdjacencyList.php 158 2010-09-04 16:49:32Z denis $
 * $HeadURL: http://www.interkassa.com/svn/interkassa1/trunk/lib/Ik/Db/Tree/AdjacencyList.php $
 * $LastChangedBy: denis $
 * $LastChangedDate: 2009-08-06$
 * $LastChangedRevision: 158 $
 */

class Ik_Db_Tree_AdjacencyList extends Ik_Db_Table_Abstract implements Ik_Db_Tree_Interface
{
    const TREE_ID_KEY       = 'id';
    const TREE_PID_KEY      = 'parentId';
    const TREE_DEEP_KEY     = 'deep';
    const TREE_SORT_KEY     = 'sort';
    const TREE_HAS_CHILDREN_KEY = 'hasChildren';

    protected static $_keyMap = array(
        self::TREE_ID_KEY           => 'id',
        self::TREE_PID_KEY          => 'parentId',
        self::TREE_DEEP_KEY         => 'deep',
        self::TREE_SORT_KEY         => 'sort',
        self::TREE_HAS_CHILDREN_KEY => 'hasChildren',
    );
    
    const TREE_POS_FIRST    = 'moveFirst';
    const TREE_POS_LAST     = 'moveLast';
    const TREE_POS_NEXT     = 'moveNext';
    const TREE_POS_PREV     = 'movePrev';
    
    protected $_name = 'node_tree';
    
    protected $_rowClass = 'Ik_Db_Tree_Node';
    
    protected $_rowsetClass = 'Ik_Db_Tree_Nodeset';
    
    public function getKey($alias)
    {
        return static::$_keyMap[$alias];
    }
    
    /**
     * Get base select for tree
     * 
     * @return Zend_Db_Select
     */
    protected function _getBaseSelect()
    {
        $select = $this->select();
        $select->order($this->getKey(self::TREE_SORT_KEY));
        $select->order($this->getKey(self::TREE_ID_KEY));
        return $select;
    }
    
    protected function _isTreeIdKey($value)
    {
        return is_int($value);
    }
        
    /**
     * Get full tree
     * 
     * @return Ik_Db_Tree_Node_Abstract
     */
    public function getTree()
    {
        return $this->getRootNode()->loadBranch();
    }
    
    /**
     * Корень
     * Get root node
     * 
     * @return Ik_Db_Tree_Node_Abstract
     */
    public function getRootNode()
    {
        $select = $this->_getBaseSelect();
        $select->where($this->getKey(self::TREE_PID_KEY) . ' = 0');
        $select->limit(1);
        $result = $this->fetchRow($select);
        return $result;
    }
    
    /**
     * Get branch
     * 
     * @return Ik_Db_Tree_Node_Abstract
     */
    public function getBranch($node)
    {
        return $this->getNode($node)->loadBranch();
    }
    
    /**
     * Get node
     * 
     * @return Ik_Db_Tree_Node_Abstract
     */
    public function getNode($nodeId)
    {
        $select = $this->_getBaseSelect();
        $select->where($this->getKey(self::TREE_ID_KEY) . ' = ?', $nodeId);
        $result = $this->fetchRow($select);
        
        if (null === $result) {
            throw new Ik_Db_Tree_Exception('Node not found (Id: ' . $nodeId . ')');
        }
        
        return $result;        
    }

    /**
     * Get children
     * 
     * @return Ik_Db_Tree_Nodeset_Abstract
     */
    public function getChildren($node, $useOptimize = true)
    {
        if ($this->_isTreeIdKey($node)) {
            $node = $this->getNode($node);
        }
        
        if ($useOptimize && !$node->{$this->getKey(self::TREE_HAS_CHILDREN_KEY)}) {
            return array();
        } else {
            $select = $this->_getBaseSelect();
            $select->where($this->getKey(self::TREE_PID_KEY) . ' = ?', $node->{$this->getKey(self::TREE_ID_KEY)});
            $result = $this->fetchAll($select);
            return $result;
        }   
    }
    
    /**
     * Родитель
     * Get parent
     * 
     * @return Ik_Db_Tree_Node_Abstract
     */    
    public function getParent($node)
    {
        if ($this->_isTreeIdKey($node)) {
            $node = $this->getNode($node);
        }
        
        return $this->getNode($node->{$this->getKey(self::TREE_PID_KEY)});
    }
        
    /**
     * Get Ancestor (Path)
     *  
     * @return Ik_Db_Tree_Nodeset_Abstract
     */
    public function getAncestor($node)
    {
        if ($this->_isTreeIdKey($node)) {
            $node = $this->getNode($node);
        }        

        $rows = array();
        
        $pid = $node->{$this->getKey(self::TREE_PID_KEY)};
        
        while($pid !== null) {
            $select = $this->select();
            $select->where($this->getKey(self::TREE_ID_KEY) . ' = ?', $pid);
            $resultRows = $select->query()->fetchAll();
            
            if (isset($resultRows[0])) {
                $pid = $resultRows[0][$this->getKey(self::TREE_PID_KEY)];
                
                $rows = array_merge($rows, $resultRows);
            } else {
                $pid = null;
            }
        }
        
        $rows = array_reverse($rows);
        
        $data  = array(
            'table'    => $this,
            'data'     => $rows,
            'readOnly' => true,
            'rowClass' => $this->getRowClass(),
            'stored'   => true
        );

        $rowsetClass = $this->getRowsetClass();
        return new $rowsetClass($data);
    }

    /**
     * Get Sibling
     *  
     * @return Ik_Db_Tree_Nodeset_Abstract
     */
    public function getSibling($node)
    {
        if ($this->_isTreeIdKey($node)) {
            $node = $this->getNode($node);
        }        
        
        $pid = $node->{$this->getKey(self::TREE_PID_KEY)};
        
        $select = $this->_getBaseSelect();
        $select->where($this->getKey(self::TREE_PID_KEY) . ' = ?', $pid);
        $result = $this->fetchAll($select);
        return $result;        
    }
    
    /**
     * Get deep
     * 
     * @param $node
     * @return int
     */
    public function getDeep($node, $asQuery = false)
    {
        if ($this->_isTreeIdKey($node)) {
            $node = $this->getNode($node);
        } 

        if ($asQuery) {
            return (int)count($this->getAncestor($node));
        } else {
            return (int)$node->{self::TREE_DEEP_KEY};
        }
    }
    
    /**
     * Has children?
     * 
     * @param $node
     * @return bool
     */
    public function hasChildren($node, $asQuery = false)
    {
        if ($this->_isTreeIdKey($node)) {
            $node = $this->getNode($node);
        }
                
        if ($asQuery) {
            
            $nodeId = $node->{$this->getKey(self::TREE_ID_KEY)};
            
            $select = $this->_getBaseSelect();
            $select->from($this->_name, 'COUNT(*) AS count');
            $select->where($this->getKey(self::TREE_PID_KEY) . ' = ?', $nodeId);
            $count = (int)$select->query()->fetchColumn();

            return ($count > 0) ? true : false;
            //return (count($this->getChildren($node)) > 0) ? true : false;
        } else {
            return (bool)$node->{$this->getKey(self::TREE_HAS_CHILDREN_KEY)};
        }
    }
    
    public function deleteNode($node)
    {
        if ($this->_isTreeIdKey($node)) {
            $node = $this->getNode($node);
        }
                
        return $node->delete();   
    }
    
    public function deleteBranch($node)
    {
        if ($this->_isTreeIdKey($node)) {
            $node = $this->getNode($node);
        }
                
        $node->deleteBranch();
        
        return true;
    }

    protected function _validateTreeRootNode()
    {
        $select = $this->_getBaseSelect();
        $select->where($this->getKey(self::TREE_PID_KEY) . ' = 0');
        $result = $select->query()->fetchAll();
        if (count($result) == 0) {
            throw new Ik_Db_Tree_Exception('Tree has no root node');
        } elseif (count($result) > 1) {
            throw new Ik_Db_Tree_Exception('Tree has more then 1 root node');
        }
    }
    
    protected function _validateTreeHierarchy()
    {
        // TODO
    }
    
    protected function _validateTreeIntegrity()
    {
        $select = $this->select()
             ->from(array('n1' => $this->_name),
                    array('InvalidNodeId' => $this->getKey(self::TREE_ID_KEY)))
             ->joinleft(array('n2' => $this->_name),
                    'n1.' . $this->getKey(self::TREE_PID_KEY) . ' = n2.' . $this->getKey(self::TREE_ID_KEY), array())
             ->where('n2.' . $this->getKey(self::TREE_ID_KEY) . ' IS NULL')
             ->where('n1.' . $this->getKey(self::TREE_PID_KEY) . ' != 0');        
        
        //Zend_Debug::dump($select->assemble());             
             
        $result = $select->query()->fetchAll();
        
        $invalidNodeIds = array();
        foreach ($result as $row) {
            $invalidNodeIds[] = $row['InvalidNodeId'];
        }
        
        if (count($invalidNodeIds) > 0) {
            throw new Ik_Db_Tree_Exception('Tree has undocked nodes - ' . implode(', ', $invalidNodeIds));
        }
    }
    
    public function validateTree()
    {
        try {
            $this->_validateTreeRootNode();
            $this->_validateTreeIntegrity();
        } catch (Exception $exception) {
            return $exception;
        }
        
        return true;
    }
    
    public function optimizeTree()
    {
        $this->optimizeBranch($this->getRootNode());
        
        return true;
    }
    
    public function optimizeNode($node, $save = true)
    {
        if ($this->_isTreeIdKey($node)) {
            $node = $this->getNode($node);
        }
        
        $node->{self::TREE_DEEP_KEY} = (int)$this->getDeep($node, true);
        $node->{$this->getKey(self::TREE_HAS_CHILDREN_KEY)} = (bool)$this->hasChildren($node, true);
        
        if ($save) {
            $node->save();
        }

        return $node;
    }
    
    public function optimizeBranch($node, $save = true)
    {
        if ($this->_isTreeIdKey($node)) {
            $node = $this->getNode($node);
        }
        
        $this->optimizeNode($node);
        
        $children = $node->getChildren();
        
        $sort = 1;        
        foreach ($children as $key => $child) {
            $child->{$this->getKey(self::TREE_SORT_KEY)} = $sort;
            $this->optimizeBranch($child);
            $sort++;
        }
        
        return $node;
    }
    
    
    public function insertNode($onNode = null, $position = self::TREE_POS_LAST)
    {
        if (null === $onNode) {
            $onNode = $this->getRootNode();
        }
        
        if ($this->_isTreeIdKey($onNode)) {
            $onNode = $this->getNode($onNode);
        } 

        $pid = 0;
        $deep = 0;
        $sort = 0;
        switch($position) {
            case self::TREE_POS_FIRST:
            case self::TREE_POS_LAST:
                $pid = $onNode->{$this->getKey(self::TREE_ID_KEY)};
                $deep = $this->getDeep($onNode) + 1;
                break;
            case self::TREE_POS_NEXT:
            case self::TREE_POS_PREV:
                $pid = $onNode->{$this->getKey(self::TREE_PID_KEY)};
                $deep = $this->getDeep($onNode);
                break;
        }
        
        $data = array(
            $this->getKey(self::TREE_PID_KEY)      => $pid,
            self::TREE_DEEP_KEY     => $deep,
            $this->getKey(self::TREE_SORT_KEY)     => $sort,
            $this->getKey(self::TREE_HAS_CHILDREN_KEY) => false,
        );

        try {
            $this->getAdapter()->beginTransaction();
            
            $newNode = $this->createRow($data);
            $newNode->save();
            
            $this->getAdapter()->commit();
        } catch (Exception $exception) {
            $this->getAdapter()->rollBack();
        }
        
        return $newNode;
    }
    
    public function moveNodeTo($node, $toNode, $position = self::TREE_POS_LAST)
    {
        if ($this->_isTreeIdKey($node)) {
            $node = $this->getNode($node);
        }
        
        if ($this->_isTreeIdKey($toNode)) {
            $toNode = $this->getNode($toNode);
        }
        
        try {
            $this->getAdapter()->beginTransaction();
            
            switch($position) {
                case self::TREE_POS_FIRST:
                    $this->_moveNodeInto($node, $toNode);
                    $this->_moveNodeToFirst($node);
                    break;
                case self::TREE_POS_LAST:
                    $this->_moveNodeInto($node, $toNode);
                    $this->_moveNodeToLast($node);
                    break;
                case self::TREE_POS_NEXT:
                case self::TREE_POS_PREV:
                    $this->_moveNodeInto($node, $this->getParent($toNode));
                    $this->_moveNodeRel($node, $toNode, $position);
                    break;
            }
                
            $this->getAdapter()->commit();
        } catch (Exception $exception) {
            //Zend_Debug::dump($exception);
            Zend_Debug::dump($exception->__toString());
            $this->getAdapter()->rollBack();
        }
        
        return $node;
    }
    
    public function moveNode($node, $position = self::TREE_POS_NEXT)
    {
        // TODO
    }
    
    protected function _moveNodeInto(Ik_Db_Tree_Node_Abstract $node, Ik_Db_Tree_Node_Abstract $intoNode)
    {
        if ($node->{$this->getKey(self::TREE_PID_KEY)} != $intoNode->{$this->getKey(self::TREE_ID_KEY)}) {
            $node->{$this->getKey(self::TREE_PID_KEY)} = $intoNode->{$this->getKey(self::TREE_ID_KEY)};
            
            if ($intoNode->{$this->getKey(self::TREE_HAS_CHILDREN_KEY)} == false) {
                $intoNode->{$this->getKey(self::TREE_HAS_CHILDREN_KEY)} = true;
                $intoNode->save();
            }
            
            $this->optimizeBranch($node);
            
            $node->save();
        }
        
        return $node;
    }
    
    protected function _moveNodeToFirst(Ik_Db_Tree_Node_Abstract $node)
    {
        $select = $this->_getBaseSelect();
        $select->where($this->getKey(self::TREE_PID_KEY) . ' = ?', $node->{$this->getKey(self::TREE_PID_KEY)});
        $select->reset('order');
        $select->order($this->getKey(self::TREE_SORT_KEY) . ' ' . Zend_Db_Select::SQL_ASC);
        $select->limit(1);
        
        $result = $select->query()->fetchAll();
        
        $minSort = $result[0][$this->getKey(self::TREE_SORT_KEY)]; 
        $minId = $result[0][$this->getKey(self::TREE_ID_KEY)];

        if ($minId != $node->{$this->getKey(self::TREE_ID_KEY)}) {
            $sql = 'UPDATE ' . $this->_name 
                 . ' SET ' . $this->getKey(self::TREE_SORT_KEY) . ' = ' . $this->getKey(self::TREE_SORT_KEY) . ' + 1 ' 
                 . 'WHERE ' . $this->getKey(self::TREE_PID_KEY) . ' = ' . $node->{$this->getKey(self::TREE_PID_KEY)};
            $query = $this->getAdapter()->query($sql);
            
            $node->{$this->getKey(self::TREE_SORT_KEY)} = $minSort; 
            $node->save();
        }        

        return $node;
    }
    
    protected function _moveNodeToLast(Ik_Db_Tree_Node_Abstract $node)
    {
        $select = $this->_getBaseSelect();
        $select->where($this->getKey(self::TREE_PID_KEY) . ' = ?', $node->{$this->getKey(self::TREE_PID_KEY)});
        $select->reset('order');
        $select->order($this->getKey(self::TREE_SORT_KEY) . ' ' . Zend_Db_Select::SQL_DESC);
        $select->order($this->getKey(self::TREE_ID_KEY) . ' ' . Zend_Db_Select::SQL_DESC);
        $select->limit(1);
        
        $result = $select->query()->fetchAll();
        
        $maxSort = $result[0][$this->getKey(self::TREE_SORT_KEY)]; 
        $maxId = $result[0][$this->getKey(self::TREE_ID_KEY)];
        
        if ($maxId != $node->{$this->getKey(self::TREE_ID_KEY)}) {
            $node->{$this->getKey(self::TREE_SORT_KEY)} = $maxSort + 1; 
            $node->save();
        }
        
        return $node;
    }
    
    protected function _moveNodeRel(
        Ik_Db_Tree_Node_Abstract $node, 
        Ik_Db_Tree_Node_Abstract $onNode,
        $position = self::TREE_POS_NEXT
        )
    {
        if ($node->{$this->getKey(self::TREE_PID_KEY)} != $onNode->{$this->getKey(self::TREE_PID_KEY)}) {
            throw new Ik_Db_Tree_Exception('Nodes has different parants');
        }
        
        $select = $this->_getBaseSelect();
        $select->from($this->_name, $this->getKey(self::TREE_ID_KEY));                    
        $select->where($this->getKey(self::TREE_PID_KEY) . ' = ?', $node->{$this->getKey(self::TREE_PID_KEY)});
        $result = $select->query()->fetchAll();
        
        if ($position == self::TREE_POS_NEXT) {
            $firstEl = $onNode->{$this->getKey(self::TREE_ID_KEY)};
            $secondEl = $node->{$this->getKey(self::TREE_ID_KEY)};
        } else {
            $firstEl = $node->{$this->getKey(self::TREE_ID_KEY)};
            $secondEl = $onNode->{$this->getKey(self::TREE_ID_KEY)};
        }
        
        $elements = array();
        foreach ($result as $row) {
            if ($firstEl == end($elements)  
                && $secondEl == $row[$this->getKey(self::TREE_ID_KEY)]) {
                return $node;
            }
            
            $elements[] = $row[$this->getKey(self::TREE_ID_KEY)];
        }
        // Zend_Debug::dump($elements);

        $searchId = $onNode->{$this->getKey(self::TREE_ID_KEY)};
        $searchElemKey = array_search($searchId, $elements);
        $elementsAfter = array_slice($elements, $searchElemKey);
        if ($position == self::TREE_POS_NEXT) {
            array_shift($elementsAfter);
        }

        if (count($elementsAfter) > 0) {
            $sqlPartIn = implode(', ', $elementsAfter);
            //Zend_Debug::dump($sqlPartIn);
        
            $sql = 'UPDATE ' . $this->_name 
                 . ' SET ' . $this->getKey(self::TREE_SORT_KEY) . ' = ' . $this->getKey(self::TREE_SORT_KEY) . ' + 2 ' 
                 . 'WHERE ' . $this->getKey(self::TREE_ID_KEY) . ' IN (' . $sqlPartIn . ')';
            $query = $this->getAdapter()->query($sql);
            
            $node->{$this->getKey(self::TREE_SORT_KEY)} = $onNode->{$this->getKey(self::TREE_SORT_KEY)} + 1;
            $node->save();
        }
        
        return $node;
    }
    
    protected function _viewTree(array $node, $eol = PHP_EOL)
    {
        echo str_repeat('==', $node['Deep']) . '> ' . $node['Id']
             . ' (' . $node['ParentId'] . ' / ' . $node['Sort'] . ')' . $eol;

        if (isset($node['_children'])) {
            foreach ($node['_children'] as $child) {
                $this->_viewTree($child, $eol);
            }
        }
    }    
    
    public function viewTree()
    {
        echo '<pre>';
        $this->_viewTree($this->getTree()->toArray());
        echo '</pre>';
    }
}