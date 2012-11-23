<?php
class User extends Ik_Mongo_Document
{
    protected $_collectionClass = 'User_Collection';

    const P_NAME    = 'name';
    const P_PHONE   = 'phone';
    const P_ADDRESS = 'address';
}