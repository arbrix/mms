<?php
class Mms_Model_Item_Storage extends Mms_Storage_Collection
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
        self::MD_OPERATION => array('default' => array(
            Mms_Storage_Abstract::OPERATION_CREATE => array(
                'processData' => array(
                    'field' => array(
                        'alias',
                        'title',
                        'price',
                        'regex'
                    )
                ),
            ),
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
            'create' => array(
                'alias' => array(
                    'alias',
                    'title',
                    'price',
                    'regex',
                ),
            ),
            'single' => array(
                'alias' => array(
                    'alias',
                    'title',
                    'price',
                    'regex',
                ),
            ),
        )),
    );

/******************************************************************************
* END
******************************************************************************/
}