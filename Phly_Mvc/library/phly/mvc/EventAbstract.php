<?php
namespace phly\mvc;
use \phly\pubsub\Provider as Provider;

abstract class EventAbstract
    extends \ArrayObject
    implements EventInterface
{
    protected $_request;
    protected $_response;
    protected $_pubsub;
    protected $_state = 'init';
    protected $_stateChanged = false;
    protected $_states = array();
    protected $_exceptions = array();

    public function __construct(
        array $array = array(), 
        $flags = \ArrayObject::ARRAY_AS_PROPS, 
        $iterator_class = '\ArrayIterator'
    ) {
        parent::__construct($array, $flags, $iterator_class);
    }

    /**
     * @param \Zend_Controller_Request_Abstract
     * @return EventInterface
     */
    public function setRequest($request)
    {
        $this->_request = $request;
        return $this;
    }

    /**
     * @return \Zend_Controller_Request_Abstract
     */
    public function getRequest()
    {
        if (null === $this->_request) {
            $this->setRequest(new \Zend_Controller_Request_Http());
        }
        return $this->_request;
    }

    /**
     * @param Response
     * @return EventInterface
     */
    public function setResponse($response)
    {
        $this->_response = $response;
        return $this;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        if (null === $this->_response) {
            $this->setResponse(new Response());
        }
        return $this->_response;
    }

    /**
     * @param Provider $pubsub
     * @return EventInterface
     */
    public function setPubSub(Provider $pubsub)
    {
        $this->_pubsub = $pubsub;
        return $this;
    }

    /**
     * @return Provider $pubsub
     */
    public function getPubSub()
    {
        if (null === $this->_pubsub) {
            $this->setPubSub(new Provider());
        }
        return $this->_pubsub;
    }

    /**
     * Set event state
     * 
     * @param  string $state 
     * @return EventAbstract
     */
    public function setState($state)
    {
        $state = (string) $state;

        if (!in_array($state, $this->getStates())) {
            throw new StateException();
        }

        $this->_state = $state;
        $this->_stateChanged = true;
        return $this;
    }

    /**
     * Get current event state
     * 
     * @return string
     */
    public function getState()
    {
        return $this->_state;
    }

    /**
     * Mark state as unchanged.
     * 
     * @return EventAbstract
     */
    public function markState()
    {
        $this->_stateChanged = false;
        return $this;
    }

    /**
     * Return state transition status
     * 
     * @return bool
     */
    public function isStateChanged()
    {
        return $this->_stateChanged;
    }

    /**
     * Set valid states
     * 
     * @param  array $states 
     * @return EventAbstract
     */
    public function setStates(array $states)
    {
        $this->_states = $states;
        return $this;
    }

    /**
     * @return array
     */
    public function getStates()
    {
        return $this->_states;
    }

    public function addException(\Exception $e)
    {
        $this->_exceptions[] = $e;
        return $this;
    }

    public function getExceptions()
    {
        return $this->_exceptions;
    }
}
