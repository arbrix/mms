<?php
/*
 * Zend Framework Db Tree (zf-tree)
 * 
Package for DB Tree with different tree adapters.
Node structure. Object based.
AdjacencyList - included.

 * $Id: Tree.php 158 2010-09-04 16:49:32Z denis $
 * $HeadURL: http://www.interkassa.com/svn/interkassa1/trunk/lib/Ik/Db/Tree.php $
 * $LastChangedBy: denis $
 * $LastChangedDate: 2009-08-06$
 * $LastChangedRevision: 158 $
 */

/*
        $tree = new Ik_Db_Tree_AdjacencyList();
        
        //$tree = Ik_Db_Tree::factory('AdjacencyList');
        
        //$tree->fetchAll()->toArray();
        
        //Zend_Debug::dump($tree->insertNode(5)->toArray());
        
        //Zend_Debug::dump($tree->deleteNode(6));
                
        //Zend_Debug::dump($tree->deleteBranch(2));
        
        //Zend_Debug::dump($tree->getTree()->toArray(array('Id', 'Deep')));

        //Zend_Debug::dump($tree->optimizeNode(5)->toArray());        
        
        //$tree->moveNodeTo(5, 2, Ik_Db_Tree_AdjacencyList::TREE_POS_LAST);
        
        //$tree->optimizeBranch(1);
        
        //$tree->optimizeTree();
        
        //Zend_Debug::dump($tree->validateTree());
        
        echo '<pre>';
        $tree->viewTree();
        echo '</pre>';
        
        //Zend_Debug::dump($tree->getNode(3)->toArray());

        //Zend_Debug::dump($tree->getDeep(5, true));
        
        //Zend_Debug::dump($tree->hasChildren(2, true));
        
        //Zend_Debug::dump($tree->getBranch(2)->toArray());
        
        //Zend_Debug::dump($tree->getParent(2)->toArray());
        
        //Zend_Debug::dump($tree->getAncestor(3)->toArray());
        
        //Zend_Debug::dump($tree->getSibling(2)->toArray());
        
        //Zend_Debug::dump($tree->getDeep(5));
        
        //Zend_Debug::dump($tree->hasChildren(2));
        
        //var_dump(get_class($tree));
 */

class Ik_Db_Tree
{
    public static function factory($adapterName, array $options = null)
    {
        $className = 'Ik_Db_Tree_' . ucfirst($adapterName);
        
        if(class_exists($className)) {
            return new $className($options);
        } else {
            throw new Ik_Db_Tree_Exception('Couldn\'t find adapter class - ' . $className);
        }
    }
}