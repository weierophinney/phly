<?php

class Phly_Mvc_Subscriber_RequestEnv
{
    protected $_request;

    public function __construct(Phly_Mvc_Request_Base $request = null)
    {
        if (null !== $request) {
            $this->setRequest($request);
        }
    }

    public function setRequest(Phly_Mvc_Request_Base $request)
    {
        $this->_request = $request;
        return $this;
    }

    public function getRequest(Phly_Mvc_Event $e = null)
    {
        if (null === $this->_request) {
            $this->setRequest(new Phly_Mvc_Request_Http());
        }

        if (null !== $e) {
            $e->requestEnv = $this->_request;
        }

        return $this->_request;
    }
}
