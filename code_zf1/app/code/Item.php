<?php
class Item extends Ik_Mongo_Document
{
    protected $_collectionClass = 'Item_Collection';

    const P_ALIAS = 'alias';
    const P_TITLE = 'title';
    const P_PRICE = 'price';
    const P_REGEX = 'regex';

}