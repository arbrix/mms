<?php
class IndexController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $this->forward('control');
    }

    public function controlAction()
    {
        $model = $this->_getParam('model', 'order');
        $manager = new Mms_Manager(array('storageAlias' => $model, 'request' => $this->getRequest()));
        $this->view->content = $manager->generateInterface();
    }
}