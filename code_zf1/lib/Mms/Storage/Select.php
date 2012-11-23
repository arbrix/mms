<?php
class Mms_Storage_Select
{
/******************************************************************************
* SYSTEM
******************************************************************************/

    public function __construct($options = array())
    {
        if (isset($options['querySet'])) {
            $this->setQuerySet($options['querySet']);
        }
    }

    public function toString($array = null)
    {
        $string = '';
        if (null === $array) {
            $array = $this->_querySet;
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

    protected $_querySet = array(
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

    public function setQuerySet($querySet)
    {
        foreach ($querySet as $key => $options) {
            $options += $this->_querySet[$key];
            $this->_querySet[$key] = $options;
        }
    }

    public function getQuerySet($key = null)
    {
        if ($key === null) {
            return $this->_querySet;
        } elseif (isset($this->_querySet[$key])) {
            return $this->_querySet[$key];
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
                    if (!isset($this->_querySet[self::QS_WHERE][$alias][$keySet])) {
                        $this->_querySet[self::QS_WHERE][$alias][$keySet] = $set;
                        continue;
                    }
                    if (isset($set['valueSet'])) {
                        $this->_querySet[self::QS_WHERE][$alias][$keySet]['valueSet'] = $set['valueSet'];
                    }
                    if (isset($set['type'])) {
                        $this->_querySet[self::QS_WHERE][$alias][$keySet]['type'] = $set['type'];
                    }
                    if (isset($set['logic'])) {
                        $this->_querySet[self::QS_WHERE][$alias][$keySet]['logic'] = $set['logic'];
                    }
                }
                return;
            }
            if (isset($this->_querySet[self::QS_WHERE][$alias])) {
                $this->_querySet[self::QS_WHERE][$alias] = array_merge($this->_querySet['where'][$alias], $sets);
            } else {
                $this->_querySet[self::QS_WHERE][$alias] = $sets;
            }
        }
    }

    public function getLimitSet(Zend_Controller_Request_Http $request)
    {
        if (empty($this->_querySet['limit'])) {
            $limit = Mms_Control_Paginator::getLimitSet($request);
            $limit += $this->_querySet;
            $this->_querySet = $limit;

        }
        return $this->_querySet['limit'];
    }

/******************************************************************************
* END
******************************************************************************/
}