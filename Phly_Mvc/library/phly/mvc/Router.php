<?php
namespace phly\mvc;

class Router implements RouterInterface
{
    public function route(EventInterface $e)
    {
        $request = $e->getRequest();
        $this->getRouter()->route($request);
    }

    public function setRouter(\Zend_Controller_Router_Interface $router)
    {
        $this->_router = $router;
        return $this;
    }

    public function getRouter()
    {
        if (null === $this->_router) {
            $this->setRouter(new \Zend_Controller_Router_Rewrite());
        }
        return $this->_router;
    }
}
