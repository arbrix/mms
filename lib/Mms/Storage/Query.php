<?php
class Mms_Storage_Query
{
/******************************************************************************
* SYSTEM
******************************************************************************/

    public function __construct($options = array())
    {
        if (isset($options['querySet'])) {
            $this->setQueryDataSet($options['querySet']);
        }
    }

    public function toString($array = null)
    {
        $string = '';
        if (null === $array) {
            $array = $this->_queryDataSet;
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $string .= $this->toString($value);
            }else {
                $string .= $key . '=' . $value;
            }
        }
        return $string;
    }

/******************************************************************************
* QUERY
******************************************************************************/

    protected $_queryDataSet = array(
        self::QS_WHERE => array(),
        self::QS_LIMIT => array(),
        self::QS_ORDER => array(),
        self::QS_FIELD => array(),
        self::QS_FROM => '',
    );

    const QS_WHERE = 'where';
    const QS_FROM  = 'from';
    const QS_LIMIT = 'limit';
    const QS_ORDER = 'order';
    const QS_FIELD = 'field';

    public function setQueryDataSet($querySet)
    {
        foreach ($querySet as $key => $options) {
            $options += $this->_queryDataSet[$key];
            $this->_queryDataSet[$key] = $options;
        }
    }

    public function getQueryDataSet($key = null)
    {
        if ($key === null) {
            return $this->_queryDataSet;
        } elseif (isset($this->_queryDataSet[$key])) {
            return $this->_queryDataSet[$key];
        }
        return null;
    }
    /**
     *
     * Add params to where options ...
     * @param array $where ({aliasField:{<setKey>:[{valueSet:[],logic:'not or',type:'like,in,other'}])
     *
     */
    public function addToWhere($where, $replace = false)
    {
        /*
        if (empty($where)) {
            throw new Mms_Storage_Exception('Where empty');
        }*/
        foreach ($where as $alias => $sets) {
            if ($replace === true) { // replace only existing field, not fool replace
                foreach ($sets as $keySet => $set) {
                    if (!isset($this->_queryDataSet[self::QS_WHERE][$alias][$keySet])) {
                        $this->_queryDataSet[self::QS_WHERE][$alias][$keySet] = $set;
                        continue;
                    }
                    if (isset($set['valueSet'])) {
                        $this->_queryDataSet[self::QS_WHERE][$alias][$keySet]['valueSet'] = $set['valueSet'];
                    }
                    if (isset($set['type'])) {
                        $this->_queryDataSet[self::QS_WHERE][$alias][$keySet]['type'] = $set['type'];
                    }
                    if (isset($set['logic'])) {
                        $this->_queryDataSet[self::QS_WHERE][$alias][$keySet]['logic'] = $set['logic'];
                    }
                }
                return;
            }
            if (isset($this->_queryDataSet[self::QS_WHERE][$alias])) {
                $this->_queryDataSet[self::QS_WHERE][$alias] = array_merge($this->_queryDataSet['where'][$alias], $sets);
            } else {
                $this->_queryDataSet[self::QS_WHERE][$alias] = $sets;
            }
        }
    }

    public function getLimitSet(Zend_Controller_Request_Http $request)
    {
        if (empty($this->_queryDataSet['limit'])) {
            $limit = Mms_Control_Paginator::getLimitSet($request);
            $limit += $this->_queryDataSet;
            $this->_queryDataSet = $limit;

        }
        return $this->_queryDataSet['limit'];
    }

/******************************************************************************
* END
******************************************************************************/
}