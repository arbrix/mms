<?php
class Mms_Model_User_Storage extends Mms_Storage_Mongo
{
    protected static $_metadata = array(
        self::MD_NAME => 'user',
        self::MD_SPEC_STORAGE => 'User_Collection',
        self::MD_SPEC_ENTITY => 'User',
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
            'name' => array(
                'title' => array(
                    'en' => 'Name',
                ),
            ),
            'phone' => array(
                'title' => array(
                    'en' => 'Phone',
                ),
            ),
            'address' => array(
                'title' => array(
                     'en' => 'Address',
                ),
            ),
        ),
        self::MD_PATH => array(
            'id'      => '_id',
            'name'  => User::P_NAME,
            'phone'  => User::P_PHONE,
            'address' => User::P_ADDRESS,
        ),
        self::MD_CONDITION => array('default' => array(
            'correlation' => array(
                'id',
                'name',
                'phone',
                'address',
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
                'name',
                'phone',
                'address',
            ),
            'operations' => array(
                'each' => array(),
                'all' => array(),
            ),
        )),
        self::MD_CONTROL_FILTER => array('default' => array(
            'alias' => array(
                'id',
                'name',
                'phone',
                'address',
            ),
            'condition' => array(),
        )),
        self::MD_CONTROL_FORM => array('default' => array(
            'single' => array(
                'alias' => array(
                    'id',
                    'name',
                    'phone',
                    'address',
                ),
            ),
        )),
    );

/******************************************************************************
* END
******************************************************************************/
}