<?php
class Mms_Model_Order_Storage extends Mms_Storage_Table
{
    protected static $_metadata = array(
        self::MD_NAME => 'order',
        self::MD_SPEC_STORAGE => 'Order_Table',
        self::MD_SPEC_ENTITY => 'Order',
        'instance' => null,
        'prepared' => false,
        self::MD_DEFAULT => array('default' => array(
            'isArray' => false,
            'type' => 'string',
            'tpl' => 'text',
            'condition' => array(),
            'isChangeable' => false,
        )),
        self::MD_FIELD => array(
            'id' => array(
                'title' => array(
                    'en' => 'Id'
                ),
                'type' => 'int',
            ),
            'userId' => array(
                'title' => array(
                    'en' => 'User Id',
                ),
            ),
            'amount' => array(
                'title' => array(
                    'en' => 'Amount',
                ),
                'type' => 'float'
            ),
            'created' => array(
                'title' => array(
                     'en' => 'Created',
                ),
            ),
            'state' => array(
                'title' => array(
                     'en' => 'State',
                ),
                'type' => 'int',
                'tpl' => 'list_of_array',
                'isChangeable' => true,
            ),
        ),
        self::MD_PATH => array(
            'id'      => 'id',
            'userId'  => Order::P_USER_ID,
            'amount'  => Order::P_AMOUNT,
            'created' => Order::P_CREATED,
            'state'   => Order::P_STATE,
        ),
        self::MD_OPERATION => array('default' => array(
            Mms_Storage_Abstract::OPERATION_CREATE => array(),
            Mms_Storage_Abstract::OPERATION_UPDATE => array('processData' => array(
                'field' => array('id',
                    'userId',
                    'amount',
                    'created',
                    'state',
                ))),
            Mms_Storage_Abstract::OPERATION_DELETE => array(),
            Mms_Storage_Abstract::OPERATION_EXPORT => array('link' => '/export/model/%s'),
            Mms_Storage_Abstract::OPERATION_FILTER => array(),
        )),
        self::MD_PRESET => array('default' => array(
            'where' => array(),
            'order' => array('id' => 'DESC')
        )),
        self::MD_CONTROL_DATAGRID => array('default' => array(
            'alias' => array(
                'id',
                'userId',
                'amount',
                'created',
                'state',
            ),
            'operations' => array(
                'each' => array(
                    'update',
                ),
                'all' => array('export'),
            ),
        )),
        self::MD_CONTROL_FILTER => array('default' => array(
            'alias' => array(
                'id',
                'userId',
                'amount',
                'created',
                'state',
            ),
            'condition' => array(),
        )),
        self::MD_CONTROL_FORM => array('default' => array(
            'single' => array(
                'alias' => array(
                    'userId',
                    'amount',
                    'created',
                    'state',
                ),
            ),
            'update' => array(
                'alias' => array(
                    'userId',
                    'amount',
                    'created',
                    'state',
                ),
            ),
        )),
        self::MD_HELPERS => array('default' => array(
            'state' => array('state'))
        ),
        self::MD_FIELD_SET => array('default' => array(
            'state' => 'state',
        )),
    );

    protected static function _getStateSet()
    {
        return array(
            0 => array('label' => 'wait', 'title' => 'новый'),
            4 => array('label' => 'warning', 'title' => 'в процессе'),
            5 => array('label' => 'success', 'title' => 'выполненный'),
            9 => array('label' => 'important', 'title' => 'неудачный'),
        );
    }

    public static function helperState(& $data, $alias, $params)
    {
        $stateSet = self::getMetadata(self::MD_FIELD_SET);
        $stateSet = $stateSet['state'];

        foreach (array_keys($data) as $rowKey) {
            $stateKey = $data[$rowKey][$alias];
            $data[$rowKey][$alias] =  ' <span class="label label-'.  $stateSet[$stateKey]['label'] . '" >'. $stateSet[$stateKey]['title'].' </span>';
        }
    }

/******************************************************************************
* END
******************************************************************************/
}