<?php
class Mms_Control_Paginator extends Mms_Control_Abstract
{
    const COUNT_ON_PAGE = 2;
    const COUNT_STEP = 30;
    const COUNT_PAGE_IN_RANGE = 15;
    const COOKIE_SAVE_TIME = 2678400;

    protected $_relateControls = array(
        Mms_Manager::C_DATAGRID,
    );
    protected $_requireData = array(
        self::P_PARAMS => true,
    );

    public static $paginator;

    public static function getQuerySet($request)
    {
        return static::getLimitSet($request);
    }

    public static function processRequest(Zend_Controller_Request_Http $request)
    {
        if (!empty(static::$paginator)) {
            return static::$paginator;
        }
        $page = (int) $request->getParams('page', 1);
        if ($page <= 0) {
            $page = 1;
        }

        $countCookie = $request->getCookie('countOnPage');
        $countParam = $request->getParam('countOnPage');
        if (($countCookie == null && $countParam == null) || ($countParam != null && $countCookie != $countParam)) {
            if ($countParam == null) {
                $count = static::COUNT_ON_PAGE;
            } else {
                $count = $countParam;
            }
            setcookie('countOnPage', $count, (time() + static::COOKIE_SAVE_TIME));
        } else {
            $count = $countCookie;
        }

        $paginator = array(
            'page' => $page,
            'count' => $count
        );
        static::$paginator = $paginator;
        return $paginator;
    }

    public static function getLimitSet($request)
    {
        static ::processRequest($request);
        return array(
            'limit' => array(
                'from' => (static::$paginator['page']-1) * static::$paginator['count'],
                'to' => static::$paginator['page'] * static::$paginator['count'],
                'count' => static::$paginator['count'],
                'page' => static::$paginator['page']
            )
        );
    }

    protected function _dispatch()
    {
        $countOnPage = static::$paginator['count'];
        $currentPage = static::$paginator['page'];
        $params = $this->getParams(self::P_PARAMS);
        $pages = array('current' => $currentPage);
        $pages['count'] = ceil($params['countData']/$countOnPage);
        $lastPageInRange = $currentPage + floor(self::COUNT_PAGE_IN_RANGE/2);
        $pages['last'] = ($pages['count'] > $lastPageInRange) ? $lastPageInRange : $pages['count'];
        $pages['range'] = self::COUNT_PAGE_IN_RANGE;
        $paginator = array(
            'page' => $pages,
            'countOnPage' => $countOnPage,
            'filter' => $this->getRequest()->getParam(Mms_Manager::C_FILTER),
        );
        $this->getView()->paginator = $paginator;
    }
}