<?php
namespace phly\mvc;

class ActionController implements ActionControllerInterface
{
    protected $_broker;

    public function __invoke(EventInterface $e)
    {
        if (isset($e['mvc.actioncontroller.broker'])) {
            $this->setHelperBroker($e['mvc.actioncontroller.broker']);
        }
        $request = $e->getRequest();
        $action  = $request->getActionName();
        if (null === $action) {
            $action = 'index';
        }
        $method  = $this->_formatActionName($action);

        $response = $e->getResponse();
        $context  = $request->getControllerName() . '/' . $request->getActionName();
        $response->assign(array(), $context);

        $this->$method($e);

        $values = $response->getValues();
    }

    public function __call($method, $args)
    {
        if ('Action' == substr($method, -6)) {
            if (!method_exists($this, $method)) {
                throw new PageNotFoundException();
            }
            return call_user_func_array(array($this, $method), $args);
        }

        $broker = $this->getHelperBroker();
        if (null === $broker) {
            throw new \BadMethodCallException();
        }
        if (!$broker->hasHelper($method)) {
            throw new \BadMethodCallException();
        }
        $helper = $broker->getHelper($method);
        if (!is_callable($helper)) {
            throw new InvalidHelperException();
        }
        return call_user_func_array($helper, $args);
    }

    public function setHelperBroker(action\HelperBrokerInterface $broker)
    {
        $this->_broker = $broker;
    }

    public function getHelperBroker()
    {
        return $this->_broker;
    }

    protected function _formatActionName($name)
    {
        $name = str_replace(array('.', '-'), ' ', $name);
        $name = ucwords($name);
        $name = str_replace(' ', '', $name);
        $name = lcfirst($name);
        return $name . 'Action';
    }
}
