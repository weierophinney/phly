<?php

class Phly_Mvc_Request_Request
{
    protected $_classMethods;
    protected $_env;
    protected $_server;

    public function __construct($options = null)
    {
        if (null !== $options) {
            $this->setOptions((array) $options);
        }
    }

    public function setOptions(array $options)
    {
        $methods = $this->_getMethods();
        foreach ($options as $key => $value) {
            $method = 'set' . $key;
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    public function setEnv($data)
    {
        return $this->_setSource('env', $data);
    }

    public function getEnv($name = null, $default = null)
    {
        return $this->_getSource('env', $name, $default, false);
    }

    public function setServer($data)
    {
        return $this->_setSource('server', $data);
    }

    public function getServer($name = null, $default = null)
    {
        return $this->_getSource('server', $name, $default, false);
    }

    protected function _setSource($source, $data)
    {
        $intKey = '_' . strtolower($source);
        $this->$intKey = (array) $data;
        return $this;
    }

    protected function _getSource($source, $name = null, $default = null, $resetOrig = true)
    {
        $intKey = '_' . strtolower($source);
        $refKey = '_' . strtoupper($source);

        if (null === $this->$intKey) {
            // Variable variables do not work with superglobals within 
            // function calls; handle specially
            switch ($refKey) {
                case '_COOKIE':
                    $data = $_COOKIE;
                    if ($resetOrig) {
                        $_COOKIE = array();
                    }
                    break;
                case '_ENV':
                    $data = $_ENV;
                    if ($resetOrig) {
                        $_ENV = array();
                    }
                    break;
                case '_GET':
                    $data = $_GET;
                    if ($resetOrig) {
                        $_GET = array();
                    }
                    break;
                case '_POST':
                    $data = $_POST;
                    if ($resetOrig) {
                        $_POST = array();
                    }
                    break;
                case '_SERVER':
                    $data = $_SERVER;
                    if ($resetOrig) {
                        $_SERVER = array();
                    }
                    break;
                default:
                    $data = $$refKey;
                    if ($resetOrig) {
                        $$refKey = array();
                    }
                    break;
            }

            $this->_setSource($source, $data);
        }

        if (null === $name) {
            return $this->$intKey;
        }

        if (array_key_exists($name, $this->$intKey)) {
            return $this->{$intKey}[$name];
        }

        return $default;
    }

    protected function _getMethods()
    {
        if (null === $this->_classMethods) {
            $methods = get_class_methods($this);
            $this->_classMethods = array_map('strtolower', $methods);
        }
        return $this->_classMethods;
    }
}
