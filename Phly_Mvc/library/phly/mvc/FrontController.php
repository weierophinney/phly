<?php
namespace phly\mvc;
use phly\pubsub\Provider as Provider;

/**
 * FrontController
 *
 * Defines the following application states:
 * - routing
 * - dispatching
 * - response
 *
 * and the following pubsub events:
 * - mvc.routing.pre
 * - mvc.routing
 * - mvc.routing.post
 * - mvc.dispatching.pre
 * - mvc.dispatching
 * - mvc.dispatching.post
 * - mvc.response.pre
 * - mvc.response
 * - mvc.response.post
 * 
 * @uses FrontControllerInterface
 * @package Ortus
 * @version $Id: $
 * @copyright Copyright (C) 2006-Present, Zend Technologies, Inc.
 * @author Matthew Weier O'Phinney <matthew@zend.com> 
 * @license New BSD {@link http://framework.zend.com/license/new-bsd}
 */
class FrontController implements FrontControllerInterface
{
    /**
     * PubSub provider used within {@link run()}
     * @var Provider
     */
    protected $_pubsub;

    /**
     * Application states
     * @var array
     */
    protected $_states = array(
        'routing',
        'dispatching',
        'response',
        'error',
    );

    /**
     * Set pubsub provider
     * 
     * @param  Provider $pubsub 
     * @return FrontController
     */
    public function setPubSub(Provider $pubsub)
    {
        $this->_pubsub = $pubsub;
        return $this;
    }

    /**
     * Get pubsub provider
     *
     * Instantiates one if none is provided
     * 
     * @return Provider
     */
    public function getPubSub()
    {
        if (null === $this->_pubsub) {
            $this->setPubSub(new Provider());
        }
        return $this->_pubsub;
    }

    /**
     * Handle the event
     * 
     * @param  Event $e 
     * @return void
     */
    public function handle(EventInterface $e = null)
    {
        if (null === $e) {
            $e = new Event();
        }
        $pubsub = $this->getPubSub();
        $e->setPubSub($pubsub);
        $e->setStates($this->_states);

        // Figure this part out...
        $e->setResponse($pubsub->getResponse());

        // Closure to determine if state has changed
        $stateChanged = function () use ($e) {
            return $e->isStateChanged();
        };

        routing:
            $e->markState();
            $pubsub->publishUntil($stateChanged, 'mvc.routing.pre', $e);
            if ($e->isStateChanged()) {
                goto switchState;
            }

            $pubsub->publishUntil($stateChanged, 'mvc.routing', $e);
            if ($e->isStateChanged()) {
                goto switchState;
            }
            
            $pubsub->publishUntil($stateChanged, 'mvc.routing.post', $e);
            if ($e->isStateChanged()) {
                goto switchState;
            }

            $e->setState('dispatching');

        dispatching:
            $e->markState();
            $pubsub->publishUntil($stateChanged, 'mvc.dispatching.pre', $e);
            if ($e->isStateChanged()) {
                goto switchState;
            }

            $pubsub->publishUntil($stateChanged, 'mvc.dispatching', $e);
            if ($e->isStateChanged()) {
                goto switchState;
            }

            $pubsub->publishUntil($stateChanged, 'mvc.dispatching.post', $e);
            if ($e->isStateChanged()) {
                goto switchState;
            }

            $e->setState('response');

        response:
            $e->markState();
            $pubsub->publishUntil($stateChanged, 'mvc.response.pre', $e);
            if ($e->isStateChanged()) {
                goto switchState;
            }

            $pubsub->publishUntil($stateChanged, 'mvc.response', $e);
            if ($e->isStateChanged()) {
                goto switchState;
            }

            $pubsub->publishUntil($stateChanged, 'mvc.response.post', $e);
            if ($e->isStateChanged()) {
                goto switchState;
            }
            return;

        error:
            $e->markState();
            $pubsub->publishUntil($stateChanged, 'mvc.error', $e);
            if ($e->isStateChanged()) {
                goto switchState;
            }
            goto response;

        switchState:
            switch ($e->getState()) {
                case 'routing':
                    goto routing;
                case 'dispatching':
                    goto dispatching;
                case 'response':
                    goto response;
                case 'error':
                    goto error;
                default:
                    throw new StateException();
            }

    }
}
