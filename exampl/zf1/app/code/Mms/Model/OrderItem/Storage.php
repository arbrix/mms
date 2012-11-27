<?php
class Mms_Model_OrderItem_Storage extends Mms_Storage_Mysql
{
    protected static $_metadata = array(
        self::MD_NAME => 'order_item',
        self::MD_SPEC_STORAGE => 'Order_Item_Table',
        self::MD_SPEC_ENTITY => 'Order_Item',
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
            'itemId' => array(
                'title' => array(
                    'en' => 'Item Id',
                ),
            ),
            'orderId' => array(
                'title' => array(
                    'en' => 'Order Id',
                ),
            ),
            'price' => array(
                'title' => array(
                     'en' => 'Price',
                ),
            ),
            'count' => array(
                'title' => array(
                     'en' => 'Count',
                ),
            ),
        ),
        self::MD_PATH => array(
            'id'      => 'id',
            'itemId'  => Order_Item::P_ITEM_ID,
            'orderId' => Order_Item::P_ORDER_ID,
            'price'   => Order_Item::P_PRICE,
            'count'   => Order_Item::P_COUNT,
        ),
        self::MD_CONDITION => array('default' => array(
            'correlation' => array(
                'id',
                'itemId',
                'orderId',
                'price',
                'count',
            ),
        )),
        self::MD_OPERATION => array('default' => array(
            Mms_Storage_Abstract::OPERATION_CREATE => array(),
            Mms_Storage_Abstract::OPERATION_UPDATE => array(),
            Mms_Storage_Abstract::OPERATION_DELETE => array(),
            Mms_Storage_Abstract::OPERATION_FILTER => array(),
        )),
        self::MD_PRESET => array('default' => array(
            'where' => array(),
        )),
        self::MD_CONTROL_DATAGRID => array('default' => array(
            'alias' => array(
                'id',
                'itemId',
                'orderId',
                'price',
                'count',
            ),
            'operations' => array(
                'each' => array(),
                'all' => array(),
            ),
        )),
        self::MD_CONTROL_FILTER => array('default' => array(
            'alias' => array(
                'id',
                'itemId',
                'orderId',
                'price',
                'count',
            ),
            'condition' => array(),
        )),
        self::MD_CONTROL_FORM => array('default' => array(
        )),
    );

/******************************************************************************
* END
******************************************************************************/
}