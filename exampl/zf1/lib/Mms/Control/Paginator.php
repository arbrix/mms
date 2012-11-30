<?php
class Mms_Control_Paginator extends Mms_Control_Abstract
{
    const COUNT_ON_PAGE = 3;
    const COUNT_STEP = 30;
    const COUNT_PAGE_IN_RANGE = 15;
    const COOKIE_SAVE_TIME = 2678400;

    protected $_relateControls = array(
        Mms_Manager::C_DATAGRID,
        Mms_Manager::C_FILTER,
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
        $page = (int) $request->getParam('page', 1);
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
        return static::$paginator;
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
        $current = static::$paginator['page'];
        $params = $this->getParams(self::P_PARAMS);

        $view = $this->getView();

        $view->current = $current;
        $view->totalItemCount = $params['countData'];
        $view->count   = ceil($params['countData']/$countOnPage);
        $view->previous = ($current > 1) ? ($current - 1) : $current;
        $view->next = $current + 1;
        $view->last = $current + floor(self::COUNT_PAGE_IN_RANGE/2);
        $view->last = ($view->count > $view->last) ? $view->last : $view->count;
        $view->next = ($view->next > $view->last) ? $view->last : $view->next;
        $firstInPageRange = $current - floor(self::COUNT_PAGE_IN_RANGE/2);
        $firstInPageRange = (($firstInPageRange > 0) ? $firstInPageRange : 1 );
        $view->firstItemNumber = ($firstInPageRange - 1) * self::COUNT_ON_PAGE;
        $view->lastItemNumber = ($firstInPageRange) * self::COUNT_ON_PAGE;
        $pagesInRange = array();
        for ($page = $firstInPageRange; $page <= ((self::COUNT_PAGE_IN_RANGE > $view->last) ? $view->last : self::COUNT_PAGE_IN_RANGE); $page++) {
            $pagesInRange[] = $page;
        }
        $view->pagesInRange = $pagesInRange;
    }
}