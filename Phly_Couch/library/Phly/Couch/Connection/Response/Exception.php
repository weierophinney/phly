<?php

class Phly_Couch_Connection_Response_Exception extends Phly_Couch_Connection_Exception
{
    protected $_httpResponse = null;
    protected $_httpRequest  = null;

    public function __construct($message, Phly_Couch_Response $httpResponse, $httpRequest="")
    {
        $this->_httpResponse = $httpResponse;
        $this->_httpRequest  = $httpRequest;
        parent::__construct($message);
    }

    /**
     * Return the Request that lead to the CouchDB Response Error
     *
     * @return string
     */
    public function getHttpRequest()
    {
        return $this->_httpRequest;
    }

    /**
     * While Querying the CouchDB an Response Error occured. Transport the Response Object.
     *
     * @return Zend_Http_Response
     */
    public function getHttpResponse()
    {
        return $this->_httpResponse;
    }
}