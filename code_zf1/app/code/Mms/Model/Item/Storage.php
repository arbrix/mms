<?php
class Mms_Model_Item_Storage extends Mms_Storage_Mongo
{
    protected static $_metadata = array(
        self::MD_NAME => 'item',
        self::MD_SPEC_STORAGE => 'Item_Collection',
        self::MD_SPEC_ENTITY => 'Item',
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
            'alias' => array(
                'title' => array(
                    'en' => 'Alias',
                ),
            ),
            'title' => array(
                'title' => array(
                    'en' => 'Title',
                ),
            ),
            'regex' => array(
                'title' => array(
                     'en' => 'Regex',
                ),
            ),
            'price' => array(
                'title' => array(
                     'en' => 'Price',
                ),
            ),
        ),
        self::MD_PATH => array(
            'id'      => '_id',
            'alias'  => Item::P_ALIAS,
            'price'  => Item::P_PRICE,
            'title' => Item::P_TITLE,
            'regex'   => Item::P_REGEX,
        ),
        self::MD_CONDITION => array('default' => array(
            'correlation' => array(
                'id',
                'alias',
                'title',
                'price',
                'regex',
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
                'alias',
                'title',
                'price',
            ),
            'operations' => array(
            ),
        )),
        self::MD_CONTROL_FILTER => array('default' => array(
            'alias' => array(
                'id',
                'alias',
                'title',
                'price',
            ),
            'condition' => array(),
        )),
        self::MD_CONTROL_FORM => array('default' => array(
            'single' => array(
                'alias' => array(
                    'alias',
                    'title',
                    'price',
                ),
            ),
        )),
    );

/******************************************************************************
* END
******************************************************************************/
}