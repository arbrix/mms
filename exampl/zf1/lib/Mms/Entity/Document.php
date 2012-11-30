<?php
class Mms_Entity_Document extends Mms_Entity_Abstract
{
/******************************************************************************
* DATA
******************************************************************************/

    public function _loadData()
    {
        $storageClass = $this->getStorageClass();
        $pathSet = $storageClass::getPathSet();
        $fieldsData =  $storageClass::getMetadata(Mms_Storage_Abstract::MD_FIELD);
        $data = array();
        $entityData = $this->_specificEntity->getData();
        foreach (array_keys($fieldsData) as $alias) {
            if (isset($entityData[$pathSet[$alias]])) {
                $data[$alias] = $entityData[$pathSet[$alias]];
            }
        }
        return $data;
    }

    public function _saveData()
    {
        $storageClass = $this->getStorageClass();
        $pathSet = $storageClass::getPathSet();
        $fieldSet = $storageClass::getMetadata(Mms_Storage_Abstract::MD_FIELD);
        foreach ($this->_data as $alias => $value) {
            if ($fieldSet[$alias]['isChangeable'] === true) {
                $this->_specificEntity->setData($pathSet[$alias], $value);
            }
        }
        $this->_specificEntity->save();
    }

/******************************************************************************
* END.
******************************************************************************/
}