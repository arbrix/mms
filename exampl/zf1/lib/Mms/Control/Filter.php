<?php
class Mms_Control_Filter extends Mms_Control_Abstract
{
/******************************************************************************
 * SYSTEM
 ******************************************************************************/

    public static $filterActive = false;
    protected static $_postData;
    protected static $_conditionSet = array(
        'correlation' => array(
            'eq' => array(
                'title' => '==',

            )
        )
    );

    protected $_requireData = array(
        self::P_TITLE     => true,
        self::P_PARAMS    => true,
        self::P_METADATA    => true,
    );


    public static function getQuerySet($request)
    {
        $filter = $request->getParam('filter');
        if (isset($filter['model'])) {
            unset($filter['model']);
        }
        if (empty($filter)) {
            return;
        }
        $where = array();
        $currentKey = 0;
        self::$_postData = $filter;
        //restructure data for storage
        foreach ($filter as $alias => $dataSet) {
            $addNotSet = false;
            foreach ($dataSet as $dataKey => $data) {
                if ($data['not'] == '1') {
                    $addNotSet = true;
                    $where[$alias][($currentKey+1)]['valueSet'][] = $data['val'];
                    $where[$alias][($currentKey+1)]['logic'] = 'not';
                } elseif (isset($data['val'])) {
                    $where[$alias][$currentKey]['valueSet'][] = $data['val'];
                } elseif (isset($data['from'])) {
                    $where[$alias][$currentKey]['valueSet'] = $data;
                    $where[$alias][$currentKey]['type'] = 'between';
                } else {
                    $where[$alias][$currentKey]['valueSet'] = $data;
                }
                if ($addNotSet === true) {
                    $currentKey += 2;
                } else {
                    $currentKey++;
                }
            }
        }
        static::$filterActive = true;
        return array('where' => $where);
    }

    protected function _dispatch()
    {
        $view = $this->getView();

        $view->postData = self::$_postData;
        $metadata = $this->getParams(self::P_METADATA);
        $view->fieldMetadata = $metadata[Mms_Storage_Abstract::MD_FIELD];
        $view->filterParams = $metadata[Mms_Storage_Abstract::MD_CONTROL_FILTER];
        $view->fieldSet = (isset($metadata[Mms_Storage_Abstract::MD_FIELD_SET]))
            ? $metadata[Mms_Storage_Abstract::MD_FIELD_SET]
            : array();
        $view->titleSet = $this->getParams(self::P_TITLE);
    }
}