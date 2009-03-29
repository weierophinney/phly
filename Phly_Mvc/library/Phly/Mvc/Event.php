<?php
class Phly_Mvc_Event extends ArrayObject
{
    public function __construct($array = null)
    {
        if (null === $array) {
            $array = array();
        }
        parent::__construct($array, ArrayObject::ARRAY_AS_PROPS);
    }

    public function offsetSet($name, $value)
    {
        $normalized = strtolower($name);
        if (in_array($normalized, array('eventmanager', 'request', 'response'))) {
            $method = 'set' . $normalized;
            $this->$method($value);
            return;
        }

        return parent::offsetSet($name, $value);
    }

    public function setEventManager(Phly_Mvc_EventManager $em)
    {
        $this->eventManager = $em;
        return $this;
    }

    public function getEventManager()
    {
        if (!isset($this->eventManager)) {
            return null;
        }
        return $this->eventManager;
    }

    public function setRequest(Phly_Mvc_Request_Request $request)
    {
        $this->request = $request;
        return $this;
    }

    public function getRequest()
    {
        if (!isset($this->request)) {
            return null;
        }

        return $this->request;
    }

    public function setResponse(Phly_Mvc_Response_IResponse $response)
    {
        $this->response = $response;
        return $this;
    }

    public function getResponse()
    {
        if (!isset($this->response)) {
            return null;
        }

        return $this->response;
    }
}
