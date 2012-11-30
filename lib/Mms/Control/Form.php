<?php
class Mms_Control_Form extends Mms_Control_Abstract
{
/******************************************************************************
* SYSTEM
******************************************************************************/

    const TYPE_UPDATE = 'update';
    const TYPE_CREATE = 'create';

    protected $_requireData = array(
        self::P_DATA      => true,
        self::P_TITLE     => true,
        self::P_PARAMS    => true,
        self::P_METADATA    => true,
        self::P_FORM_TYPE   => true,
    );

    protected function _dispatch()
    {
        $controlForm = $this->_getMetadata(Mms_Storage_Abstract::MD_CONTROL_FORM);
        $formType = $this->getParams(self::P_FORM_TYPE, self::TYPE_UPDATE);

        if (!isset($controlForm[$formType])) {
            return;
        }
        $formOptions = $controlForm[$formType];

        $view = $this->getView();
        $templateName = (isset($formOptions['view'])) ? $formOptions['view'] : $formType;

        $group = $this->_getMetadata(Mms_Storage_Abstract::MD_GROUP);

        $aliasSet = $formOptions['alias'];
        $groupSet = array();
        $fieldMetadata = $this->_getMetadata(Mms_Storage_Abstract::MD_FIELD);
        foreach (array_keys($aliasSet) as $keyAlias) {
            if (isset($fieldMetadata[$aliasSet[$keyAlias]]['group']) && isset($group[$fieldMetadata[$aliasSet[$keyAlias]]['group']])) {
                $groupSet[$fieldMetadata[$aliasSet[$keyAlias]]['group']][] = $aliasSet[$keyAlias];
                unset($aliasSet[$keyAlias]);
            }
        }
        $this->setTemplate($templateName);
        $view->activeGroup = current(array_keys($groupSet));
        $view->groupSet = $groupSet;
        $view->aliasSet = $aliasSet;
        $view->valueSet = $this->_getMetadata(Mms_Storage_Abstract::MD_FIELD_SET);
        $view->fieldMetadata = $fieldMetadata;
        $view->scripts = (isset($formOptions['scripts'])? $formOptions['scripts'] : array());
        $view->post = $this->getRequest()->getParam('form');
    }

/******************************************************************************
* END.
******************************************************************************/
}