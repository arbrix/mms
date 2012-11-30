<?php
/**
 * Interkassa Ltd.
 * Info: Mms/Storage/Abstract.php 2012.11.20 14:02:00 Kutuzov Ivan
 */
abstract class Mms_Storage_Abstract
{
/******************************************************************************
* SYSTEM
******************************************************************************/

   public function __construct(array $options = array())
    {
        if (isset($options['request'])) {
            $this->setRequest($options['request']);
        }
        $this->_getProfiledMetadata();
        $this->_initPath();
        $this->_initFieldsSet();
        $this->_fillField();
        $this->_initAdapter();
        if (isset($options['select'])) {
            $this->_initQuery($options['select']);
        }
    }

    protected function _initPath() {}

    protected function _fillField()
    {
        if (!isset(static::$_metadata[self::MD_DEFAULT])) {
            return;
        }
        $defaultFieldValue = self::getMetadata(self::MD_DEFAULT);
        foreach (array_keys(static::$_metadata[self::MD_FIELD]) as $alias) {
            static::$_metadata[self::MD_FIELD][$alias] += $defaultFieldValue;
        }
    }

    protected function _initFieldsSet()
    {
        $fieldSet = self::getMetadata(self::MD_FIELD_SET);
        if (empty($fieldSet)) {
            return;
        }
        $profile = $this->getProfile();
        foreach ($fieldSet as $fieldAlias) {
            if (!isset(static::$_metadata[self::MD_FIELD][$fieldAlias])
                && !isset(static::$_metadata[self::MD_VIRTUAL][$fieldAlias])
            ) {
                continue;
            }
            $getMethodName = '_get' . ucfirst($fieldAlias) . 'Set';
            if (method_exists($this, $getMethodName)) {
                static::$_metadata[self::MD_FIELD_SET][$profile][$fieldAlias] = $this->{$getMethodName}();
            }
        }
    }

/******************************************************************************
* FILTER
******************************************************************************/

    public function filterPreQueryCurrency($sets)
    {
        $newSet = array();
        foreach ($sets[0]['valueSet'] as $key => $value) {
            $newSet[$key]['valueSet'][] = '%' . $value . '_%';
            $newSet[$key]['logic'] = 'or';
        }
        return $newSet;
    }

/******************************************************************************
* INTERFACE
******************************************************************************/

    protected $_titleSet;

    public function getTitle()
    {
        if (empty($this->_titleSet)) {
            foreach (array(self::MD_FIELD,self::MD_GROUP, self::MD_VIRTUAL) as $_metadataKey) {
                if (empty(static::$_metadata[$_metadataKey])) {
                    continue;
                }
                foreach (static::$_metadata[$_metadataKey] as $alias => $params) {
                    $this->_titleSet[$alias] = Ik_I18n::getI18nValue($params['title']);
                }
            }
            $profiledTitleSet = $this->getMetadata(self::MD_TITLE);
            if (!empty($profiledTitleSet)) {
                foreach ($profiledTitleSet as $alias => $profiledTitle) {
                    $this->_titleSet[$alias] = Ik_I18n::getI18nValue($profiledTitle);
                }
            }
            foreach (static::$operationTitleSet as $operationAlias => $operationTitles) {
                $this->_titleSet[$operationAlias] = Ik_I18n::getI18nValue($operationTitles);
            }
        }
        return $this->_titleSet;
    }

/******************************************************************************
* ADAPTER
******************************************************************************/

    protected $_adapter;

    protected function _initAdapter()
    {
        $this->_adapter = new Ik_Db_Table($this->getAdapterOptions());
    }

    public function getAdapter()
    {
        if ($this->_adapter == null) {
            $this->_initAdapter();
        }
        return $this->_adapter;
    }

    public function getAdapterOptions()
    {
        return array(
            'name' => $this->getMetadata(self::MD_NAME),
            'rowClass' => $this->getMetadata(self::MD_SPEC_ENTITY),
        );
    }

/******************************************************************************
* SELECT
******************************************************************************/

    /**
     * @var Mms_Storage_Query
     */
    protected $_query;

    protected function _initQuery($options = array())
    {
        $this->_query = new Mms_Storage_Query($options);
        //preparing query from metadata
        $preset = self::getMetadata(Mms_Storage_Abstract::MD_PRESET);
        if (!empty($preset[Mms_Storage_Query::QS_ORDER])) {
            $this->_query->setQueryDataSet(array(Mms_Storage_Query::QS_ORDER => $preset[Mms_Storage_Query::QS_ORDER]));
        }
        if (!empty($preset[Mms_Storage_Query::QS_WHERE])) {
            $this->_query->addToWhere($preset[Mms_Storage_Query::QS_WHERE]);
        }


        //preparing query from request
        $aliasSet = self::getAliasSet();
        $request = $this->getRequest();
        foreach ($aliasSet as $alias) {
            if (!$request->has($alias)) {
                continue;
            }
            $value = self::typeConversion($alias, $request->getParam($alias));
            $this->_query->addToWhere(array($alias => array(array(
                'valueSet' => array($value),
                'type' => 'equal'
            ))), true);
        }
    }

    protected function _getQuery()
    {
        if ($this->_query === null) {
            $this->_initQuery();
        }
        return $this->_query;
    }

    public static $sqlWhereConditionPatterns = array(
                    'set' => '%s IS EXISTS',
                    'notequal' => '%s <> %s',
                    'equal' => '%s = %s',
                    'in' => '%s IN (%s)',
                    'like' => '%s LIKE %s',
                    'likep' => '%s LIKE %s',
                    'lt' => '%s < %s',
                    'lte' => '%s <= %s',
                    'gt' => '%s > %s',
                    'gte' => '%s >= %s',
                    'between' => '%s < %s < %s',
    );

    public static function prepareValue($value)
    {
        if (is_numeric($value)) {
            return $value;
        }
        $value = preg_replace('[^\w\.@-]', '', $value);
        return "'" . addslashes($value) . "'";
    }

    protected function _preQuery(& $where)
    {
        $virtualSet = self::getMetadata(self::MD_VIRTUAL);
        foreach ($virtualSet as $alias => $params) {
            if (!isset($where[$alias])) {
                continue;
            }
            $methodName = 'filterPreQuery' . ucfirst($alias);
            if (method_exists($this, $methodName)) {
                $where[$alias] = $this->{$methodName}($where[$alias]);
            }
            foreach ($where[$alias] as $keySet => $sets) {
                if (!isset($sets['valueSet'])) {
                    continue;
                }

                if (!isset($sets['type'])) {
                    $sets['type'] = 'likep';
                }
                if (isset($params['cond'][$sets['type']])) {
                    $sets['type'] = $params['cond'][$sets['type']];
                }
                $where[$params['field']][$keySet] = $sets;
            }
            unset($where[$alias]);
        }
    }

/******************************************************************************
 * METADATA
 ******************************************************************************/

    protected static $_metadata = array();

    const MD_PATH      = 'path';
    const MD_NAME      = 'name';
    const MD_PRESET    = 'preset';
    const MD_DEFAULT   = 'default'; //structure of field
    const MD_FIELD     = 'field';
    const MD_FIELD_SET = 'set';
    const MD_VIRTUAL   = 'virtual';
    const MD_GROUP     = 'group';
    const MD_TITLE     = 'title';
    const MD_HELPERS   = 'helpers';
    const MD_EXPORT_FILETERS   = 'exportFilters';
    const MD_CONTROL_FILTER    = 'filter';
    const MD_CONTROL_DATAGRID  = 'datagrid';
    const MD_CONTROL_FORM      = 'form';
    const MD_OPERATION = 'operation';
    const MD_SPEC_ENTITY  = 'specEntity';
    const MD_SPEC_STORAGE = 'specStorage';

    protected static $_profiledMeradataKey = array(
        self::MD_PRESET,
        self::MD_DEFAULT,
        self::MD_FIELD_SET,
        self::MD_VIRTUAL,
        self::MD_GROUP,
        self::MD_TITLE,
        self::MD_HELPERS,
        self::MD_EXPORT_FILETERS,
        self::MD_CONTROL_DATAGRID,
        self::MD_CONTROL_FILTER,
        self::MD_CONTROL_FORM,
        self::MD_OPERATION
    );

    /**
     * @param string $key
     * @return array or null - if key not exists
     */
    public static function getMetadata($key = null)
    {
        if ($key === null) {
            return static::$_metadata;
        } elseif (isset(static::$_metadata[$key])) {
            return static::$_metadata[$key];
        }
        return array();
    }

    protected function _getProfiledMetadata()
    {
        $profile = self::getProfile();
        foreach (array_keys(static::$_metadata) as $key) {
            if (in_array($key, self::$_profiledMeradataKey)) {
                if (isset(static::$_metadata[$key][$profile])) {
                    static::$_metadata[$key] = static::$_metadata[$key][$profile];
                } elseif (isset(static::$_metadata[$key]['default'])) {
                    static::$_metadata[$key] = static::$_metadata[$key]['default'];
                } else {
                    unset(static::$_metadata[$key]);
                }
            }
        }

    }

    public static function getProfile()
    {
        //TODO:: use ACL for define profile
        return 'default';
    }

/******************************************************************************
 * OPERATION
 ******************************************************************************/

    const OPERATION_CREATE = 'create';
    const OPERATION_UPDATE = 'update';
    const OPERATION_DELETE = 'delete';
    const OPERATION_EXPORT = 'export';
    const OPERATION_FILTER = 'filter';
    const OPERATION_ACCEPT = 'accept';
    const OPERATION_PROCESS = 'process';

    public static $operationTitleSet = array(
        self::OPERATION_UPDATE => array(
            'en' => 'Settings',
            'ru' => 'Настройки'
        ),
        self::OPERATION_DELETE => array(
            'en' => 'Delete',
            'ru' => 'Удаление'
        ),
        self::OPERATION_EXPORT => array(
            'en' => 'Export',
            'ru' => 'Экспорт'
        ),
        self::OPERATION_FILTER => array(
            'en' => 'Fileter',
            'ru' => 'Фильтр'
        ),
        self::OPERATION_CREATE => array(
            'en' => 'Create',
            'ru' => 'Создать'
        ),
        self::OPERATION_PROCESS => array(
            'en' => 'Process',
            'ru' => 'Выполнить'
        ),
        self::OPERATION_ACCEPT => array(
            'en' => 'Accept',
            'ru' => 'Принять'
        ),
    );

    public static $operations = array(
        self::OPERATION_DELETE => 'd',
        self::OPERATION_CREATE => 'c',
        self::OPERATION_FILTER => 'f',
        self::OPERATION_EXPORT => 'e',
        self::OPERATION_UPDATE => 'u',
        self::OPERATION_PROCESS => 'p',
        self::OPERATION_ACCEPT => 'a',
    );

    public function processOperation(Zend_Controller_Request_Http $request, $params)
    {
        $operationAlias = $request->getParam('operation');
        $methodName = '_operation' . ucfirst($operationAlias);
        if (!method_exists($this, $methodName)) {
            throw new Mms_Storage_Exception('Operation with name (' . $operationAlias . ') not found');
        }
        $this->{$methodName}($request, $params);
    }

    protected function _operationUpdate(Zend_Controller_Request_Http $request, $params = null)
    {
        $operationParams = self::getMetadata(self::MD_OPERATION);
        if (!isset($operationParams[self::OPERATION_UPDATE]['processData'])) {
            throw new Mms_Storage_Exception(_t('This operation required configure: processData'));
        }
        $entityClass = $this->_getEntityClass();

        $formData = $request->getParam('update');

        $specificEntityId = $request->getParam('id');
        $specificEntity = $this->_getEntityForUpdate($specificEntityId);
        if ($specificEntity == null) {
            throw new Mms_Storage_Exception(_t(self::getMetadata(self::MD_SPEC_ENTITY) . ' entity with id ' . $specificEntityId . ' not found'));
        }

        $entity = new $entityClass(array(
            'specificEntity' => $specificEntity,
            'storage' => $this,
        ));

        try {
            $data = $this->_filterData($entity, $formData, $operationParams[self::OPERATION_UPDATE]['processData']);
        } catch (Mms_Entity_Exception $ex) {
            throw new Mms_Storage_Exception($ex->getMessage());
        }
        if (!empty($data)) {
            $entity->setData($data);
            $entity->save();
        }
    }

    protected function _operationCreate(Zend_Controller_Request_Http $request, $params = null)
    {
        $operationParams = self::getMetadata(self::MD_OPERATION);
        if (!isset($operationParams[self::OPERATION_CREATE]['processData'])) {
            throw new Mms_Storage_Exception(_t('This operation required configure: processData'));
        }
        $formData = $request->getParam('form');
        foreach ($operationParams[self::OPERATION_CREATE]['processData']['field'] as $alias) {
            $formData[$alias] = self::typeConversion($alias, $formData[$alias]);
        }
        $specificEntity =  $this->_getNewEntity($formData);
        $specificEntity->save();
    }

    abstract protected function _getEntityForUpdate($id);
    abstract protected function _getNewEntity($data);

/******************************************************************************
 * HELPERS
 ******************************************************************************/

    public static function helperMoney(& $data, $alias, $params = null)
    {
        foreach (array_keys($data) as $rowKey) {
            $data[$rowKey][$alias] = number_format($data[$rowKey][$alias], 2);
        }
    }

    public static function helperCut(& $data, $alias, $params)
    {
        foreach (array_keys($data) as $rowKey) {
            $data[$rowKey][$alias] = substr($data[$rowKey][$alias], $params['from'], $params['to']);
        }
    }

    public static function helperDuplicate(& $data, $alias, $params)
    {
        foreach (array_keys($data) as $rowKey) {
            $data[$rowKey][$alias] = $data[$rowKey][$params['source']];
        }
    }

    public static function helperDatetime(& $data, $alias, $params)
    {
        foreach (array_keys($data) as $rowKey) {
            if ($data[$rowKey][$alias] == null) {
                $data[$rowKey][$alias] = '';
                continue;
            }
            if (is_string($data[$rowKey][$alias])) {
                $time = strtotime($data[$rowKey][$alias]);
            } elseif(is_numeric($data[$rowKey][$alias])) {
                $time = $data[$rowKey][$alias];
            } else {
                $data[$rowKey][$alias] = '';
                continue;
            }
            $data[$rowKey][$alias] = date($params['format'], $time);
        }
    }

    public static function helperLink(& $data, $alias, $params)
    {
        if (isset($params['filterId'])) {
            $filterId = $params['filterId'];
        }
        $form = '<form name="filter' . $params['model']
            . '%1s" style="display:inline!important;" method="post" action="' . $params['actionLink'] . '">
                   <input type="hidden" name="filter[' . $params['model'] . '][' . $params['alias'] . '][condition]" value="' . $params['condition'] . '" />
                   <input type="hidden" name="filter[' . $params['model'] . '][' . $params['alias'] . '][criterion1]" value="%1s" />
                   </form><a class="pointer" onclick="document.filter' . $params['model'] . '%1s.submit();">%2s</a>';
        foreach (array_keys($data) as $rowKey) {
            if (!isset($filterId)) {
                $filterId = $data[$rowKey][$alias];
            }
            $data[$rowKey][$alias] = sprintf($form, $filterId, $data[$rowKey][$alias]);
        }
    }

    public static function helperState(& $data, $alias, $params)
    {
        $statesesSet = array(
            0 => 'yellow',
            1 => 'green',
            2 => 'green',
            3 => 'red',
            4 => 'red',
            5 => 'red',

        );
        foreach (array_keys($data) as $rowKey) {
            $data[$rowKey][$alias] = $statesesSet[$data[$rowKey][$alias]];
        }
    }

/******************************************************************************
 * EXPORT FILTERS
 ******************************************************************************/

    public static function exportFilterStatus(& $dataSet, $path = null)
    {
        foreach (array_keys($dataSet) as $key) {
            $dataSet[$key]['status'] = 'define entity statuses';
        }
    }

    public static function exportFilterState(& $dataSet, $path = null)
    {
        foreach (array_keys($dataSet) as $key) {
            $dataSet[$key]['state'] = 'define entity states';
        }
    }

    public static function exportFilterMoney(& $dataSet, $path = null)
    {
        foreach (array_keys($dataSet) as $key) {
            $dataSet[$key][$path] = number_format($dataSet[$key][$path], 2, '.', '');
        }
    }

/******************************************************************************
* DATA
******************************************************************************/

    protected $_dataSet;
    public $isExport = false;

    public function getDataSet($withLimits = true, $withFields = false)
    {
        $dataSet = array();
        $pathSet = $this->_selectData($dataSet, $withLimits, $withFields);
        $exportFilterSet = self::getMetadata(self::MD_EXPORT_FILETERS);
        $helperSet = self::getMetadata(self::MD_HELPERS);
        foreach ($pathSet as $alias => $path) {
            if (($this->isExport === true)
                && isset($exportFilterSet[$alias])
            ) {
                $filterName = 'exportFilter' . ucfirst($exportFilterSet[$alias]);
                self::$filterName($dataSet, $alias);
            } elseif ($this->isExport === false && isset($helperSet[$alias])) {
                foreach ($helperSet[$alias] as $helperAlias => $params) {
                    if (is_string($params)) {
                        $helperAlias = $params;
                        $params = null;
                    }
                    $helperMethodName = 'helper' . ucfirst($helperAlias);
                    self::$helperMethodName($dataSet, $alias, $params);
                }
            }
        }
        return $dataSet;
    }

    abstract protected function _selectData(& $dataSet, $withLimits = true, $withFields = false);


    protected function _filterData(Mms_Entity_Abstract $entity, array $array, $options)
    {
        $data = array();
        $virtualFields = self::getMetadata(Mms_Storage_Abstract::MD_VIRTUAL);
        $fieldsData =  self::getMetadata(Mms_Storage_Abstract::MD_FIELD) + $virtualFields;
        $entityData = $entity->getData();
        foreach ($options['field'] as $alias) {
            if (!isset($array[$alias])) {
                continue;
            }
            $fieldType = 'field';
            if (!empty($virtualFields) && isset($virtualFields[$alias])) {
                $fieldType = 'virtual';
            }
            $value = $array[$alias];
            if (isset($fieldsData[$fieldType][$alias]['filters'])) {
                $value = $this->_processFilter($value, $fieldsData[$fieldType][$alias]['filters']);
            }
            if (isset($fieldsData[$fieldType][$alias]['validators'])) {
                $this->_processValidate($value, $fieldsData['field'][$alias]['validators']);
            }
            if ($fieldType == 'field') {
                if (isset($entityData[$alias]) && $entityData[$alias] == $value) {
                    continue;
                }
            }
            $data[$alias] = self::typeConversion($alias, $value);
        }
        return $data;
    }

    protected function _processValidate($value, $validators)
    {
        foreach ($validators as $validator => $params) {
            if (is_string($params)) {
                $validator = $params;
                $params = array();
            }
            if ($validator instanceof Zend_Validate_Interface) {
                $result = $validator->isValid($value);
            } else {
                $result = Zend_Validate::is($value, $validator, $params);
            }
            if ($result !== true) {
                throw new Mms_Entity_Exception(sprintf(_t('Value (%s) is not valid.'), $value));
            }
        }
    }

    protected function _processFilter($value, $filters)
    {
        foreach ($filters as $filter => $params) {
            if (is_string($params)) {
                $filter= $params;
                $params = array();
            }
            if ($filter instanceof Zend_Filter_Interface) {
                $value = $filter->filter($value);
            } else {
                $value = Zend_Filter::filterStatic($value, $filter, $params);
            }
        }
        return $value;
    }

    public static function typeConversion($alias, $value)
    {
        if (!isset(static::$_metadata[self::MD_FIELD][$alias]['type'])) {
            return $value;
        }
        $type = static::$_metadata[self::MD_FIELD][$alias]['type'];
        switch ($type) {
            case 'mongoId':
                if ($value instanceof  MongoId) {
                    return $value;
                } else {
                    return new MongoId($value);
                }
            case 'mongoDate':
                if ($value instanceof  MongoDate) {
                    return $value;
                } else {
                    return new MongoDate($value);
                }
            case 'int':
                return (int) $value;
            case 'string':
                return preg_replace('/\'/','', self::prepareValue($value));
            case 'float':
                return (floatval($value));
            case 'boolean':
                return (bool) $value;
            case 'bool':
                return (bool) $value;
            default: return $value;
        }
    }

/******************************************************************************
* ENTITY
******************************************************************************/

    protected $_entitySet = array();
    protected $_entitiesCount = 0;
    protected $_entityClass;

    public function getEntitySet()
    {
        return $this->_entitySet;
    }

    public function getEntitiesCount()
    {
        return $this->_entitiesCount;
    }

    /**
     * @return Mms_Entity_Abstract
     */
    protected function _getEntityClass()
    {
        if ($this->_entityClass === null) {
            $this->_entityClass = 'Mms_Model_' . ucfirst(self::getMetadata(self::MD_NAME)) . '_Entity';
        }
        return $this->_entityClass;
    }

    protected function _initEntity()
    {
        $dataSet = $this->_dataSet;
        $entityClass = $this->_getEntityClass();
        $this->_entitySet = array();
        if (count($dataSet) == 0) {
            $this->_entitySet[] = new $entityClass(
                array(
                    'storage' => $this,
                )
            );
            return;
        }

        foreach ($dataSet as $entityKey => $data) {
            $this->_entitySet[$entityKey] = new $entityClass(
                array(
                    'specificEntity' => $data,
                    'storage' => $this,
                )
            );
        }
    }


/******************************************************************************
* PATH
******************************************************************************/

    protected static $_pathSet;
    protected static $_aliasSet;

    public static function getPath($alias)
    {
        $pathSet = static::getPathSet();
        if (!isset($pathSet[$alias])) {
            throw new Mms_Storage_Exception('Path with alias (' . $alias . ') not exists');
        }
        return $pathSet[$alias];
    }

    public static function getPathSet() {
        if (static::$_pathSet == null) {
            $pathSet = static::getAliasSet();
            if (isset(static::$_metadata[self::MD_PATH]) && is_array(static::$_metadata[self::MD_PATH])) {
                $pathSet = array_merge($pathSet, static::$_metadata[self::MD_PATH]);
            }
            static::$_pathSet = $pathSet;
        }
        return static::$_pathSet;
    }

    public static function getAliasSet()
    {
        if (static::$_aliasSet == null) {
            $aliasSet = self::getMetadata(self::MD_FIELD);
            foreach (array_keys($aliasSet)  as $alias) {
                static::$_aliasSet[$alias] = $alias;
            }
        }
        return static::$_aliasSet;
    }

/******************************************************************************
 * REQUEST
 ******************************************************************************/

    /**
     * @var Zend_Controller_Request_Http
     */
    protected $_request;

    public function setRequest(Zend_Controller_Request_Http $request)
    {
        $this->_request = $request;
    }

    public function getRequest()
    {
        return $this->_request;
    }
/******************************************************************************
* END.
******************************************************************************/
}