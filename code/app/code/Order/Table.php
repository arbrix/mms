<?php

class Order_Table extends Ik_Db_Table_Abstract
{
    protected $_name = 'order';
    protected $_rowClass = 'Order';
    
    protected $_dependentTables = array(
        'Order_Item_Table'
    );
}