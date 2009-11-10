<?php
namespace phly\mvc\action;

class HelperBroker implements HelperBrokerInterface
{
    protected $_helpers;

    public function hasHelper($helper)
    {
        $class = $this->_getHelperClass($helper);
        return (!$class) ? false : $class;
    }

    public function getHelper($helper)
    {
        return $this->_getHelper($helper);
    }

    public function addHelper($helper)
    {
        if (is_object($helper)) {
            if (!is_callable($helper)) {
                throw new InvalidHelperException();
            }
            $name = get_class($helper);
            $this->_helpers[$name] = $helper;
            return $this;
        }

        $this->_helpers[$helper] = null;
        return $this;
    }

    protected function _getShortName($name)
    {
        $names = explode('\\', $name);
        $shortName = array_pop($names);
        return $shortName;
    }

    protected function _getHelperClass($name)
    {
        if (!in_array($name, array_keys($this->_helpers))) {
            foreach (array_keys($this->_helpers) as $helper) {
                $shortName = $this->_getShortName($helper);
                if ($name == $shortName) {
                    return $helper;
                }
            }
            return false;
        }
        return $name;
    }

    protected function _getHelper($name)
    {
        if (in_array($name, array_keys($this->_helpers))) {
            if (null === $this->_helpers[$name]) {
                $this->_helpers[$name] = new $name();
            }
            return $this->_helpers[$name];
        }

        $class = $this->_getHelperClass($name);
        if (!$class) {
            throw new InvalidHelperException();
        }
        if (null === $this->_helpers[$class]) {
            $this->_helpers[$class] = new $class();
        }
        return $this->_helpers[$class];
    }
}
