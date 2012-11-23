<?php
/*
 * $Id: Account.php 48 2009-12-25 11:41:40Z denis $
 * $HeadURL: http://www.interkassa.com/svn/interkassa1/trunk/application/core/Ik/Account.php $
 * $LastChangedBy: denis $
 * $LastChangedDate: 2009-08-06$
 * $LastChangedRevision: 48 $
 */

class ErrorController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $this->forward('error');
    }
    
    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        
        if (empty($errors)) {
            $this->redirect('/');
        }
        
        $config = Zend_Registry::get('config');
        
        if (null !== $this->view) {
            switch ($errors->type) {
                case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
                case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                    // 404 error -- controller or action not found
                    $this->getResponse()->setHttpResponseCode(404);
                    $this->view->title = 'Ошибка! Запрошенная вами страница не найдена.';
                    break;
                default:
                    // application error 
                    $this->getResponse()->setHttpResponseCode(500);
                    $this->view->title = 'Ошибка веб-приложения!';
                    break;
            }
            
            //$this->view->message = $errors->exception->getMessage();

            if (getenv('APPLICATION_ENV') === 'development') {
                $this->view->exception = $errors->exception;
                $this->view->request = $errors->request;
            }
        } else {
            echo 'Application Error!';
            if ($config->env->debug_mode) {
                echo '<pre>';
                echo $errors->exception->__toString();
                echo '</pre>';
            }
        }
    }
}