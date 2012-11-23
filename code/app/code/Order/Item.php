<?php
class Order_Item extends Ik_Db_Table_Row_Abstract
{
    protected $_tableClass = 'Order_Item_Table';

    const P_ORDER_ID = 'orderId';
    const P_ITEM_ID  = 'itemId';
    const P_PRICE    = 'price';
    const P_COUNT    = 'count';
}