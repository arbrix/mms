<?php
class Mms_Entity_Row extends Mms_Entity_Abstract
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
        $entityData = $this->_specificEntity->toArray();
        foreach ($fieldsData as $alias) {
            if (isset($entityData[$pathSet[$alias]])) {
                $data[$alias] = $entityData[$pathSet[$alias]];
            }
        }
        return $data;
    }

    public function _saveData()
    {
        $storageClass = $this->getStorageClass();
        $pathSet = $storageClass::getPath();
        $fieldSet = $storageClass::getMetadata(Mms_Storage_Abstract::MD_FIELD);
        foreach ($this->_data as $alias => $value) {
            if ($fieldSet[$alias]['isChangeable'] === true) {
                $this->_specificEntity[$pathSet[$alias]] = $value;
            }
        }
        $this->_specificEntity->save();
    }


/******************************************************************************
* END.
******************************************************************************/
}