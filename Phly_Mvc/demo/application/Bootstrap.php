<?php
namespace application;
use phly\mvc as Mvc;
use phly\pubsub\Provider as Provider;

class Bootstrap extends \Zend_Application_Bootstrap_Bootstrap
{
    protected function _initFsm()
    {
        $fsm = new Mvc\FrontController();
        return $fsm;
    }

    protected function _initEventManager()
    {
        $this->bootstrap('Fsm');
        $fsm = $this->getResource('Fsm');
        $manager = new Mvc\EventManager();
        $fsm->setPubSub($manager);
        return $manager;
    }

    protected function _initControllers()
    {
        $this->bootstrap('EventManager');
        $manager = $this->getResource('EventManager');
        $dispatcher = $manager->getDispatcher();
        $dispatcher->addControllerDirectory(dirname(__FILE__) . '/controllers');
    }

    protected function _initView()
    {
        $this->bootstrap('EventManager');
        $manager = $this->getResource('EventManager');
        $response = $manager->getResponse();
        $renderer = $response->getRenderer();
        $view     = $renderer->getView();
        $view->addScriptPath(dirname(__FILE__) . '/views/scripts');
        $layout = new \Zend_Layout();
        $layout->setLayoutPath(dirname(__FILE__) . '/layouts/scripts');
        $view->getHelper('layout')->setLayout($layout);
        $renderer->setLayout($layout);
        return $view;
    }

    public function run()
    {
        $fsm = $this->getResource('Fsm');
        $e = new Mvc\Event();
        $e->bootstrap = $this;
        $fsm->handle($e);
    }
}
