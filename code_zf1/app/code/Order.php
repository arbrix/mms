<?php
class Order extends Ik_Db_Table_Row_Abstract
{
    protected $_tableClass = 'Order_Table';

    const P_USER_ID = 'userId';
    const P_AMOUNT  = 'amount';
    const P_CREATED = 'created';
    const P_COMMENT = 'comment';
    const P_STATE   = 'state';
}