<?php
class Item_Collection extends Ik_Mongo_Collection
{
    public $dbName = 'mms';
    public $name   = 'item';

    protected $_documentClass = 'Item';
}