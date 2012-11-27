<?php
class Mms_Control_Form extends Mms_Control_Abstract
{
/******************************************************************************
* SYSTEM
******************************************************************************/

    const TYPE_ADD    = 'add';
    const TYPE_SINGLE = 'single';
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
        $formType = $this->getParams(self::P_FORM_TYPE, self::TYPE_SINGLE);

        if (!isset($controlForm[$formType])) {
            throw new Mms_Control_Exception('Error! This page requered config for ' . $formType . ' form');
        }
        $formOptions = $controlForm[$formType];

        $view = $this->getView();
        $templateName = (isset($formOptions['view'])) ? $formOptions['view'] : $formType;

        //sorted field by group
        $group = $this->_getMetadata(Mms_Storage_Abstract::MD_GROUP);
        if (!empty($group)) {
            $groupTitleAliasSet = array_keys($group);
            $groupAliasSet = (isset($formOptions['group']) ? $formOptions['group'] : array());
            $groupAliasWithoutTitle = array_diff($groupAliasSet, $groupTitleAliasSet);
            if (!empty($groupAliasSet) && !empty($groupAliasWithoutTitle)) {
                throw new Mms_Control_Exception('For single form groups need to be setted');
            }
        }

        $aliasSet = $formOptions['alias'];
        $groupSet = array();
        $metaData = $this->_getMetadata(Mms_Storage_Abstract::MD_FIELD);
        foreach (array_keys($aliasSet) as $keyAlias) {
            if (isset($metaData[$aliasSet[$keyAlias]]['group']) && isset($group[$metaData[$aliasSet[$keyAlias]]['group']])) {
                $groupSet[$metaData[$aliasSet[$keyAlias]]['group']][] = $aliasSet[$keyAlias];
                unset($aliasSet[$keyAlias]);
            }
        }
        //Zend_Debug::dump($groupSet);
        if (empty($groupSet)) {
            throw new Mms_Control_Exception('Groups cannot be empty for single form');
        }
        $request = $this->getRequest();
        $this->setTemplate($templateName);
        $view->activeGroup = current(array_keys($groupSet));
        $view->groupSet = $groupSet;
        $view->scripts = (isset($formOptions['scripts'])? $formOptions['scripts'] : array());
        $view->post = $request->getParam('form');
    }

/******************************************************************************
* END.
******************************************************************************/
}