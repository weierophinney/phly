<?php
class Phly_Couch_Result
{
    protected $_info = array();
    protected $_response;

    public function __construct(Zend_Http_Response $response)
    {
        $body = $response->getBody();
        if (!empty($body)) {
            if (('{' == substr($body, 0, 1)) || ('[' == substr($body, 0, 1))) {
                require_once 'Zend/Json.php';
                $info = Zend_Json::decode($body);
                foreach ($info as $key => $value) {
                    $this->_info[$key] = $value;
                }
            }
        }
        $this->_response = $response;
    }

    public function getInfo()
    {
        return $this->_info;
    }

    public function __get($name)
    {
        if (isset($this->$name)) {
            return $this->_info[$name];
        }
        return null;
    }

    public function __isset($name)
    {
        return array_key_exists($name, $this->_info);
    }

    public function getResponse()
    {
        return $this->_response;
    }

    public function __call($method, $args)
    {
        if (!method_exists($this->_response, $method)) {
            require_once 'Phly/Couch/Exception.php';
            throw new Phly_Couch_Exception(sprintf('Method "%s" not found', $method));
        }
        return call_user_func_array(array($this->_response, $method), $args);
    }
}
