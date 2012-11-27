<?php
/*
 * INTERKASSA Ltd. (c)
 * 
 * $Id: Collection.php 158 2010-09-04 16:49:32Z denis $
 * $HeadURL: http://www.interkassa.com/svn/interkassa1/trunk/lib/Ik/Mongo/Collection.php $
 * $LastChangedBy: denis $
 * $LastChangedDate: 2010-09-04 19:49:32 +0300 (Сб, 04 сен 2010) $
 * $LastChangedRevision: 158 $
 */

class Ik_Mongo_Collection extends MongoCollection  
{
    public $dbName;
    
    protected $_documentClass;
    
    protected $_cacheDocSet = array();
    protected $_cacheDocSetLoaded = false;
    
    public function __construct($options = null)
    {
        if (null !== $options) {
            if (is_array($options) || $options instanceof Zend_Config) {
                $this->setOptions($options);
            }  else {
                $this->setName($options);
            }
        }
        
        $this->_setup();
        parent::__construct($this->db, $this->name);
        
        $this->_init();
    } 

    protected function _init()
    {
    }    
    
    protected function _setup()
    {
        // Setup collection name
        if (empty($this->name)) {
            $this->name = get_class($this);
        }
        
        // Setup collection db
        if (!isset($this->db)) {
            if (!empty($this->dbName)) {
                $this->setDb($this->dbName);
            } else {
                $this->setDb(Ik_Mongo_Db::getDefaultDb());
            }
        }
        
        // Setup document class
        if ($this->_documentClass === null) {
            $collClass = get_class($this);
            $docClass = substr($collClass, 0, strrpos($collClass, '_Collection'));
            if (@class_exists($docClass) 
                && $docClass !== 'Ik_Mongo'
            ) {
                $this->_documentClass = $docClass;
            } else {
                $this->_documentClass = 'Ik_Mongo_Document';
            }
        }        
    }     
    
    /**
     * Set options en masse
     *
     * @param  array|Zend_Config $options
     * @return void
     */
    public function setOptions($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif (!is_array($options)) {
            throw new Ik_Mongo_Exception('setOptions() expects either an array or a Zend_Config object');
        }
        
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if ($method == 'setOptions') {
                continue;
            }
            if (method_exists($this, $method)) {
                $this->{$method}($value);
            }
        }
    }
    
    public function getClass()
    {
        return get_class($this);
    }
    
    public function getDocumentClass()
    {
        return $this->_documentClass;
    }
    
    public function setDocumentClass($docName)
    {
        $this->_documentClass = $docName;
    }
        
    public function setDb($db = null)
    {
        if ($db === null) {
            $db = (string) $this->dbName;
        }
        
        if (is_string($db)) {
            $conn = Ik_Mongo_Connection::getDefaultConn();
            $db = $conn->selectDB($db);
        }
        
        $this->db = Ik_Mongo_Db::setupDb($db);
        
    }

    public function getDb()
    {
        return $this->db;
    }  
    
    public function setName($name)
    {
        $this->name = $name;
    }
    
    /**
     * Find doc
     * 
     * @param $query
     * @param $sort
     * @param $fields
     * @return Ik_Mongo_Document
     */
    //OLD: public function findDoc($query = array(), $sort = null, $fields = array())
    public function findDoc($queryOptions = array())
    {
        if (!(is_array($queryOptions) && isset($queryOptions['query']))) {
            $queryOptions = array('query' => $queryOptions);
        }

        $queryOptions['limit'] = 1;
                
        return $this->findDocs($queryOptions);
    }

   	/**
     * Find docs
     * 
     * $queryOptions = array(
     *   'query' => array(),
     *   'fields' => array(),
     *   'sort' => path,
     *   'limit' => int,
     *   'skip' => int,
     *   'arrayKey' => string,
     *   'cache' => true|false,
     * )
     * 
     * @param array $queryOptions
     * @return Ik_Mongo_Document
     */
    //OLD: public function findDocs($query = array(), $sort = null, $limit = null, $fields = array(), $arrayKey = '_id')
    public function findDocs($queryOptions = array())
    {
        if (!(is_array($queryOptions) && isset($queryOptions['query']))) {
            $queryOptions = array('query' => $queryOptions);
        }
        $query = $queryOptions['query'];
            
        $fields = array();
        if (!empty($queryOptions['fields'])) {
            $fields = (array) $queryOptions['fields'];
        }

        if ($query instanceof MongoId) {
            $query = array('_id' => $query);
        } elseif (is_string($query) 
            && strlen($query) == 24
            && ctype_xdigit($query)
        ) {
            $query = array('_id' => new MongoId($query));
        } elseif (!is_array($query)) {
            $query = array('_id' => $query);
        }

        if (empty($queryOptions['cache']) || $queryOptions['cache'] !== false) {
            if (empty($query) && $this->_cacheDocSetLoaded == true) {
                return $this->_cacheDocSet;
            } else {
                if (isset($query['_id']) && count($query) == 1) {
                    $key = (string) $query['_id'];
                    if (isset($this->_cacheDocSet[$key])) {
                        //Zend_Debug::dump('FROM CACHE: ' . $key);
                        return $this->_cacheDocSet[$key];
                    }
                }
            }        
        }
        $cursor = $this->find($query, $fields);
        if ($cursor === null || $cursor->count() == 0) {
            return null;
        }
        
        if (!empty($queryOptions['sort'])) { 
            $cursor->sort($queryOptions['sort']);
        }
        
        if (!empty($queryOptions['skip'])) {
            $cursor->skip($queryOptions['skip']);
        }
        
        $limit = 0;
        if (!empty($queryOptions['limit'])) {
            $limit = (int) $queryOptions['limit'];
            $cursor->limit($limit);
        }
        
        $arrayKey = '_id';
        if (!empty($queryOptions['arrayKey'])) {
            $arrayKey = (string) $queryOptions['arrayKey'];
        }
        
        $docs = array();
        foreach ($cursor as $data) {
            $doc = new $this->_documentClass(array(
                'data' => $data,
                'collection' => $this,
                'stored' => true,
            ));
            
            if (isset($data[$arrayKey])) {
                $docs[(string) $data[$arrayKey]] = $doc;
            } else {
                $docs[] = $doc; 
            }
        }
        
        if ($arrayKey == '_id') {
            if (empty($query)) {
                $this->_cacheDocSet = $docs;
                $this->_cacheDocSetLoaded = true;
            } else {
                $this->_cacheDocSet += $docs;
            }
        }
        
        if ($limit == 1) {
            return current($docs);
        }
        
        return $docs;
    }
    
    public function getCacheDocSet()
    {
        return $this->_cacheDocSet;
    }
    
    /**
     * Create new doc
     * 
     * @param array $data
     * @return Ik_Mongo_Document
     */
    public function createDoc($data = null)
    {
        if (empty($data['_id']) 
            || !($data['_id'] instanceof MongoId)
        ) {
            $data['_id'] = new MongoId();
        }
        
        $newDoc = new $this->_documentClass(array(
                        'data' => $data,
                        'collection' => $this,
                        'stored' => false,
                      )); 
        return $newDoc;
    }
    
    public function distinct($key, $query = null)
    {
        $result = $this->getDb()->command(array(
            'distinct' => $this->name, 
            'key' => $key, 
            'query' => $query,
        ));
        return $result;
    }
    
    public static function cleanArray($data)
    {
        $data = (array) $data;
        
        unset($data['_id']);
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = self::cleanArray($value);
            }
            if (empty($data[$key])) {
                unset($data[$key]);
            }
        }
        
        return $data;
    }
    
    public function toArray($packed = false, $mapReduce = null, $options = array())
    {
        if (isset($options['query'])) {
            $cursor = $this->find($options['query']);
        } else {
            $cursor = $this->find();
        }

        if (isset($options['sort'])) {
            $cursor->sort($options['sort']);
        }

        $result = array();
        foreach ($cursor as $doc) {
            if ($packed === true) {
                Ik_Mongo_Document::toArrayPack($doc, $mapReduce);
            }
            $result[(string)$doc['_id']] = $doc;
        }
        return $result;
    }

    public function toArrayWithoutId($packed = false, $mapReduce = null)
    {
        $cursor = $this->find();

        $result = array();
        foreach ($cursor as $doc) {
            if ($packed === true) {
                Ik_Mongo_Document::toArrayPack($doc, $mapReduce);
            }
            $result[] = $doc;
        }
        return $result;
    }
}