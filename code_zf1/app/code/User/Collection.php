<?php
class User_Collection extends Ik_Mongo_Collection
{
    public $dbName = 'mms';
    public $name = 'user';
    
    protected $_documentClass = 'User';
}