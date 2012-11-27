<?php
class Mms_Control_Datagrid extends Mms_Control_Abstract
{
/******************************************************************************
* SYSTEM
******************************************************************************/

    protected $_requireData = array(
        self::P_DATA      => true,
        self::P_TITLE     => true,
        self::P_PARAMS    => true,
        self::P_METADATA    => true,
    );

    protected function _dispatch()
    {
        $request = $this->getRequest();
        $paginator = Mms_Control_Paginator::processRequest($request);
        $params = $this->getParams(self::P_PARAMS);
        $countData = $params['countData'];
        if ($paginator['page'] > ceil($countData / $paginator['count'])) {
            $paginator['page'] = ceil($countData / $paginator['count']);
        }
        Mms_Control_Paginator::$paginator = $paginator;

        $dataGrid = $this->_getMetadata(Mms_Storage_Abstract::MD_CONTROL_DATAGRID);

        $view = $this->getView();
        $view->aliasSet = $dataGrid['alias'];
        $view->beginCounterValue = ($paginator['page'] -1) * $paginator['count'];
    }

/******************************************************************************
* END
******************************************************************************/

}