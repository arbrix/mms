<?php

class Order_Item_Table extends Ik_Db_Table_Abstract
{
    protected $_name = 'order_item';
    protected $_rowClass = 'Order_Item';
    
    protected $_referenceMap = array(
        'Order_Table' => array(
            'columns' => array(
                'orderId'
            ), 
            'refTableClass' => 'Order_Table',
            'refColumns' => array(
                'id'
            )
        )
    );
}