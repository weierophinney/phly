<?php
class Phly_Mvc_Event extends ArrayObject
{
    /** @var Phly_Mvc_EventManager */
    protected $_eventManager;

    /** @var Phly_Mvc_Request_Request */
    protected $_request;

    /** @var Phly_Mvc_Response_IResponse */
    protected $_response;

    /**
     * Constructor
     *
     * Ensure we have an array, and set the ARRAY_AS_PROPS flag.
     * 
     * @param  null|array $array 
     * @return void
     */
    public function __construct($array = null)
    {
        if (null === $array) {
            $array = array();
        } elseif (!is_array($array)) {
            $array = (array) $array;
        }
        parent::__construct($array, ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Set value
     *
     * Overrides offsetSet, to ensure event manager, request, and response 
     * objects are typed.
     * 
     * @param  string $name 
     * @param  mixed $value 
     * @return void
     */
    public function offsetSet($name, $value)
    {
        $normalized = strtolower($name);
        if (in_array($normalized, array('eventmanager', 'request', 'response'))) {
            throw new Phly_Mvc_Exception(sprintf('Must use accessor method to set %s', $name));
        }

        return parent::offsetSet($name, $value);
    }

    /**
     * Retrieve by offset name
     * 
     * @param  string $name 
     * @return mixed
     */
    public function offsetGet($name)
    {
        switch (strtolower($name)) {
            case 'eventmanager':
                return $this->getEventManager();
            case 'request':
                return $this->getRequest();
            case 'response':
                return $this->getResponse();
            default:
                return parent::offsetGet($name);
        }
    }

    /**
     * Set event manager
     * 
     * @param  Phly_Mvc_EventManager $em 
     * @return Phly_Mvc_Event
     */
    public function setEventManager(Phly_Mvc_EventManager $em)
    {
        $this->_eventManager = $em;
        return $this;
    }

    /**
     * Retrieve event manager
     * 
     * @return Phly_Mvc_EventManager
     */
    public function getEventManager()
    {
        if (null === $this->_eventManager) {
            return null;
        }
        return $this->_eventManager;
    }

    /**
     * Set request object
     * 
     * @param  Phly_Mvc_Request_Request $request 
     * @return Phly_Mvc_Event
     */
    public function setRequest(Phly_Mvc_Request_Request $request)
    {
        $this->_request = $request;
        return $this;
    }

    /**
     * Retrieve request object
     * 
     * @return Phly_Mvc_Request_Request
     */
    public function getRequest()
    {
        if (null === $this->_request) {
            return null;
        }

        return $this->_request;
    }

    /**
     * Set response object
     * 
     * @param  Phly_Mvc_Response_IResponse $response 
     * @return Phly_Mvc_Event
     */
    public function setResponse(Phly_Mvc_Response_IResponse $response)
    {
        $this->_response = $response;
        return $this;
    }

    /**
     * Get response object
     * 
     * @return Phly_Mvc_Response_IResponse
     */
    public function getResponse()
    {
        if (null === $this->_response) {
            return null;
        }

        return $this->_response;
    }
}
