<?php
abstract class Mms_Storage_Mongo extends Mms_Storage_Abstract
{

/******************************************************************************
* ADAPTER
******************************************************************************/

    protected $_adapter;

    protected function _initAdapter()
    {
        $this->_adapter = new Ik_Mongo_Collection($this->getAdapterOptions());
    }

    public function getAdapterOptions()
    {
        return array(
            'documentClass' => self::getMetadata(self::MD_SPEC_ENTITY),
            'name' => self::getMetadata(self::MD_NAME),
            'db' => Zend_Registry::get('config')->mongo->db,
        );
    }


/******************************************************************************
* ENTITY
******************************************************************************/

    public function getEntitiesCount()
    {
        if ($this->_entitiesCount != null) {
            return $this->_entitiesCount;
        }
        $cursor = $this->_getCursor();
        $this->_entitiesCount = $cursor->count();

        return $this->_entitiesCount;
    }

    protected function _loadData($withLimits = true, $withFields = false)
    {
        $cursor = $this->_getCursor($withLimits, $withFields);
        $documentClass = self::getMetadata(self::MD_SPEC_ENTITY);
        $collection = Ik_Mongo::getCollection(self::getMetadata(self::MD_SPEC_STORAGE));
        while ($cursor->hasNext()) {
            $data = $cursor->getNext();
            $this->_dataSet[(string) $data['_id']] = new $documentClass(array(
                'data' => $data,
                'collection' => $collection,
                'stored' => true,
            ));
        }
    }

    public function getEntitySet()
    {
        if ($this->_entitiesCount == null) {
            $this->getEntitiesCount();
        }

        if (count($this->_entitySet) == $this->_entitiesCount) {
            return $this->_entitySet;
        }

        $this->_loadData();
        $this->_initEntity();
        return $this->_entitySet;
    }

/******************************************************************************
* DATA
******************************************************************************/



    protected function _selectData(& $dataSet, $withLimits = true, $withFields = false)
    {
        $select = $this->_getSelect();
        if ($this->_entitiesCount == null) {
            $this->getEntitiesCount($select);
        }
        if ($this->_entitiesCount == 0 || count($this->_dataSet) != $this->_entitiesCount) {
            $this->_loadData($withLimits, $withFields);
        }

        $pathSet = static::getPathSet();
        $dataSet = array();
        if ($withFields === false) {
            $this->_selectFields = array_keys($pathSet);
        }
        foreach ($this->_selectFields as $alias) {
            if (!isset($pathSet[$alias]) || empty($this->_dataSet)) {
                continue;
            }
            foreach ($this->_dataSet as $key => $data) {
                $dataSet[$key][$alias] = $data->getData($pathSet[$alias]);
            }
        }
        $virtual = self::getMetadata(self::MD_VIRTUAL);
        if ($virtual !== null && is_array($virtual)) {
            foreach ($virtual as $fieldAlias => $params) {
                $pathSet[$fieldAlias] = $fieldAlias;
            }
        }
        return $pathSet;
    }

/******************************************************************************
* SELECT
******************************************************************************/

    protected $_selectFields = array();

    protected function _getCursor($withLimit = true, $selectFields = false)
    {
        $select = $this->_getSelect();
        $where = $select->getQuerySet(Mms_Storage_Select::QS_WHERE);
        if (!empty($where)) {
            $where = $this->_buildWhere($where);
        }
        $adapter = $this->getAdapter();
        if ($selectFields === true) {
            $this->_selectFields = $select->getQuerySet(Mms_Storage_Select::QS_FIELD);
            $pathSet = static::getPathSet();
            $findPathSet = array();
            foreach ($this->_selectFields as $alias) {
                if (isset($pathSet[$alias])) {
                    $findPathSet[] = $pathSet[$alias];
                } else {
                    $findPathSet[] = $alias;
                }
            }
            $cursor = $adapter->find($where, $findPathSet);
        } else {
            $cursor = $adapter->find($where);
        }
        
        if ($withLimit === true) {
            $limit = $select->getLimitSet($this->getRequest());
            $count = $cursor->count();
            $onPage = $limit['count'];
            if (($limit['page'] - 1)*$limit['count'] > $count) {
                $limit['page'] = floor($count / $limit['count']);
                $limit['count'] = $count - $limit['page'] * $limit['count'];
            }
            $cursor->skip(($limit['page']-1) * $onPage)->limit($limit['count']);
        }

        return $cursor;
    }

    public static $sqlWhereConditionPatterns = array(
                    'equal' => 'array',
                    'like' => 'array',
                    'likep' => 'array',
                    'lt' => 'array',
                    'lte' => 'array',
                    'gt' => 'array',
                    'gte' => 'array',
                    'between' => 'array',
    );


    protected function _buildWhere($where = '*')
    {
        if ($where == '*') {
            return null;
        }

        $query = array();
        foreach ($where as $alias => $sets) {
            foreach ($sets as $set) {
                $countValues = count($set['valueSet']);

                if (!isset($set['type']) || !isset(static::$sqlWhereConditionPatterns[$set['type']])) {
                    $set['type'] = 'like'; // any kind of sql condition
                }

                if ($countValues == 0) {
                    continue;
                } else {
                    foreach (array_keys($set['valueSet']) as $keyValueSet) {
                        $set['valueSet'][$keyValueSet] = self::typeConversion($alias, $set['valueSet'][$keyValueSet]);
                    }
                }

                if ($set['type'] == 'equal') {
                    $result = current($set['valueSet']);
                } elseif ($set['type'] == 'like' || $set['type'] == 'likep') {
                    $result = new MongoRegex('/' . current($set['valueSet']) . '/i');
                } elseif ($set['type'] == 'lt' || $set['type'] == 'lte' || $set['type'] == 'gt' || $set['type'] == 'gte') {
                    $result = array('$' . $set['type'] => current($set['valueSet']));
                } elseif ($set['type'] == 'between') {
                    $result = array(
                        '$ts' => array(
                            '$gte' => $set['valueSet']['from'],
                            '$lte' => $set['valueSet']['to']
                    ));
                }

                if (isset($set['logic'])
                    && (preg_match('/not/i', $set['logic']))
                    && ($set['type'] == 'equal'
                        || $set['type'] == 'like'
                        || $set['type'] == 'likep' )
                ) {
                    $result = array('$ne' => $result);
                }

            }
            $query[static::getPath($alias)] = $result;
        }

        return $query;
    }

/******************************************************************************
 * OPERATION
******************************************************************************/

    /**
     * @param $id
     * @return Mongo_Document
     */
    protected function _getEntityForUpdate($id)
    {
        return Ik_Mongo::getCollection(self::getMetadata(self::MD_SPEC_STORAGE))->findDoc($id);
    }

/******************************************************************************
* END.
******************************************************************************/

}