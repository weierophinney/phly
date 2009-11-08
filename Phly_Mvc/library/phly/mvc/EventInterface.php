<?php
namespace phly\mvc;
use \phly\pubsub\Provider as Provider;

interface EventInterface
{
    /**
     * @param \Zend_Controller_Request_Abstract
     * @return EventInterface
     */
    public function setRequest($request);

    /**
     * @return \Zend_Controller_Request_Abstract
     */
    public function getRequest();

    /**
     * @param \Zend_Controller_Response_Abstract
     * @return EventInterface
     */
    public function setResponse($response);

    /**
     * @return \Zend_Controller_Response_Abstract
     */
    public function getResponse();

    /**
     * @param Provider $pubsub
     * @return EventInterface
     */
    public function setPubSub(Provider $pubsub);

    /**
     * @return Provider $pubsub
     */
    public function getPubSub();

    /**
     * @param string $state
     * @return EventInterface
     */
    public function setState($state);

    /**
     * @return string
     */
    public function getState();

    /**
     * Resets the state changed flag to false, indicating the state has been 
     * transitioned into.
     *
     * @return EventInterface
     */
    public function markState();

    /**
     * @return bool
     */
    public function isStateChanged();

    /**
     * Set valid states
     * 
     * @param  array $states 
     * @return EventInterface
     */
    public function setStates(array $states);

    /**
     * @return array
     */
    public function getStates();
}
