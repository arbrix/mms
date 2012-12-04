<?php
abstract class Mms_Storage_Table extends Mms_Storage_Abstract
{
/******************************************************************************
* ENTITY
******************************************************************************/

    public function getEntitiesCount()
    {
        $query = $this->_getQuery();
        if ($this->_entitiesCount != null) {
            return $this->_entitiesCount;
        }

        $where = $query->getQueryDataSet(Mms_Storage_Query::QS_WHERE);
        if (!empty($where)) {
            $where = $this->_buildWhere($where);
        } else {
            $where = array();
        }
        $adapter = $this->getAdapter();
        $query = $adapter->select()
                          ->from(self::getMetadata(self::MD_NAME), 'COUNT(id) as count');
        $this->_entitiesCount = (int) $adapter->fetchRow($this->_buildSelect($query, $where))->count;
        return $this->_entitiesCount;
    }

    protected function _loadData($withLimits = true, $withFields = false)
    {
        $query = $this->_getQuery();
        $where = $query->getQueryDataSet(Mms_Storage_Query::QS_WHERE);
        if (isset($where)) {
            $where = $this->_buildWhere($where);
        } else {
            $where = array();
        }
        $adapter = $this->getAdapter();
        $limit = array();
        if ($withLimits === true) {
            $limit = $query->getLimitSet($this->getRequest());
        }
        $order = $query->getQueryDataSet(Mms_Storage_Query::QS_ORDER);
        $newFieldSet = array();
        if ($withFields ===  true) {
            $fieldSet = $query->getQueryDataSet(Mms_Storage_Query::QS_FIELD);
            $fieldSetKeys = array_keys($fieldSet);
            $virtualSet = self::getMetadata(self::MD_VIRTUAL);
            foreach ($fieldSetKeys as $keyOrder => $key) {
                if (isset($virtualSet[$fieldSet[$key]])) {
                    $newFieldSet[$fieldSet[$key]] = 'id';
                } else {
                    $newFieldSet[$keyOrder] = $fieldSet[$key];
                }
            }
        }
        if ($withFields === true && !empty($newFieldSet)) {
            $select = $adapter->select()
                                ->from(self::getMetadata(self::MD_NAME), $newFieldSet);
        } else {
            $select = $adapter->select()
                                ->from(self::getMetadata(self::MD_NAME));
        }

        $this->_dataSet = $adapter->fetchAll($this->_buildSelect($select, $where, $limit, $order));
    }

    public function getEntitySet()
    {
        $select = $this->_getQuery();

        if ($this->_entitiesCount == null) {
            $this->getEntitiesCount($select);
        }
        if (count($this->_entitySet) == $this->_entitiesCount) {
            return $this->_entitySet;
        }
        $this->_loadData($select);
        $this->_initEntity();
        return $this->_entitySet;
    }

/******************************************************************************
* DATA
******************************************************************************/

    protected function _selectData(& $dataSet, $withLimits = true, $withFields = false)
    {
        if ($this->_entitiesCount == null) {
            $this->getEntitiesCount();
        }
        if (count($this->_dataSet) != $this->_entitiesCount) {
            $this->_loadData($withLimits, $withFields);
        }
        $pathSet = static::getPathSet();
        $virtualSet = self::getMetadata(self::MD_VIRTUAL);
        if (is_array($virtualSet)) {
            foreach ($virtualSet as $fieldAlias => $params) {
                $pathSet[$fieldAlias] = $fieldAlias;
            }
        }
        if (!empty($this->_dataSet)) {
            $dataSet = $this->_dataSet->toArray();
        }

        return $pathSet;
    }

/******************************************************************************
* SELECT
******************************************************************************/

    /**
     * @param Zend_Db_Table_Select $select
     * @param array $where
     * @param array $limit
     * @param array $order
     * @return Zend_Db_Table_Select
     */
    protected function _buildSelect(Zend_Db_Table_Select $select, array $where, $limit = array(), $order = array())
    {
        $select->setIntegrityCheck(false);
        foreach ($where as $alias => $sets) {
            foreach ($sets as $set) {
                if (isset($set['or'])) {
                    continue;
                }
                $select->where($set['query']);
            }
        }
        $or = $this->_groupOrWhere($where);
        if (count($or) > 0) {
            $select->where('(' . implode(') OR (', $or) . ')');
        }
        if (!empty($limit) && isset($limit['page']) && isset($limit['count']) && !isset($limit['from'])) {
            if (($limit['page'] - 1)*$limit['count'] > $this->_entitiesCount) {
                $limit['page'] = floor($this->_entitiesCount / $limit['count']);
                $limit['count'] = $this->_entitiesCount - $limit['page'] * $limit['count'];
            }
            $select->limitPage($limit['page'], $limit['count']);
        } elseif (!empty($limit) && isset($limit['from']) && isset($limit['count'])) {
            if ($limit['count'] > $this->_entitiesCount) {
                $limit['count'] = $this->_entitiesCount;
            }
            $select->limit($limit['count'], $limit['from']);
        }
        if (!empty($order)) {
            $orderParams = array();
            foreach ($order as $alias => $orderType) {
                $orderParams[] = $alias . ' ' . strtoupper($orderType);
            }
            $select->order($orderParams);
        }
        //Zend_Debug::dump($select->assemble());
        return $select;
    }

    protected $_orWhere;

    protected function _groupOrWhere($where)
    {
        if ($this->_orWhere == null) {
            foreach ($where as $alias => $sets) {
                foreach ($sets as $set){
                    if (isset($set['or'])) {
                        $this->_orWhere[] = $set['query'];
                    }
                }
            }
        }
        return $this->_orWhere;
    }

    /**
     *
     * Decorate where set with SQL syntaxis
     * @param array $where
     * @return array $query
     */
    protected function _buildWhere($where = '*')
    {
        if ($where == '*') {
            return null;
        }
        $query = array();
        $virtualSet = self::getMetadata(self::MD_VIRTUAL);
        if ($virtualSet !== null) {
            $this->_preQuery($where);
        }
        foreach ($where as $alias => $sets) {
            //TODO define default type. now it's like
            foreach ($sets as $set) {
                $countValues = count($set['valueSet']);

                if (!isset($set['type']) || !isset(static::$sqlWhereConditionPatterns[$set['type']])) {
                    $fieldType = static::$_metadata[self::MD_FIELD][$alias]['type'];
                    if ($fieldType == 'int' || $fieldType == 'float' ) {
                        $set['type'] = 'equal'; // any kind of sql condition
                    } else {
                        $set['type'] = 'like'; // any kind of sql condition
                    }
                }

                if ($countValues == 0) {
                    continue;
                } else {
                    foreach (array_keys($set['valueSet']) as $keyValueSet) {
                        $set['valueSet'][$keyValueSet] = self::prepareValue($set['valueSet'][$keyValueSet]);
                    }
                }
                $fieldPart = $alias;
                if (isset($set['logic'])
                    && (preg_match('/not/i', $set['logic']))
                    && ($set['type'] == 'equal'
                        || $set['type'] == 'like'
                        || $set['type'] == 'likep' )
                ) {
                    $fieldPart = $alias . ' NOT';
                }

                if ($countValues == 1) {
                    $set['valueSet'] = current($set['valueSet']);
                } elseif ($countValues > 1 && $set['type'] != 'between') {
                    $set['type'] = 'in';
                    $set['valueSet'] = implode(', ', $set['valueSet']);
                }

                if ($set['type'] == 'between') {
                    $result = sprintf(static::$sqlWhereConditionPatterns[$set['type']], $set['valueSet']['from'], $fieldPart, $set['valueSet']['to']);
                } else {
                    $result = sprintf(static::$sqlWhereConditionPatterns[$set['type']], $fieldPart, $set['valueSet']);
                }
                if (isset($set['logic']) && (preg_match('/or/i', $set['logic']))) {
                    $query[$alias][] = array('query' => $result, 'or' => true);
                } else {
                     $query[$alias][] = array('query' => $result);
                }
            }
        }
        return $query;
    }

/******************************************************************************
 * OPERATION
******************************************************************************/

    /**
     * @param $id
     * @return Zend_Db_Table_Row
     */
    protected function _getEntityForUpdate($id)
    {
        $table = Ik_Db::getTable(self::getMetadata(self::MD_SPEC_STORAGE));
        return $table->fetchRow($table->select()->forUpdate()->where('id = ', $id));
    }

    /**
     * @return Zend_Db_Table_Row_Abstract
     */
    protected function _getNewEntity($data)
    {
        return Ik_Db::getTable(self::getMetadata(self::MD_SPEC_STORAGE))->createRow();
    }

/******************************************************************************
* END.
******************************************************************************/
}