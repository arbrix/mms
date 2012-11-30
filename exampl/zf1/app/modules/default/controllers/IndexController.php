<?php
class IndexController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $this->forward('control');
    }

    public function controlAction()
    {
        $request = $this->getRequest();
        $model = $request->getParam('model', 'order');
        $manager = new Mms_Manager(array('storageAlias' => $model, 'request' => $request, 'params' => $request->getParams()));
        $this->view->content = $manager->generateInterface();
    }
}