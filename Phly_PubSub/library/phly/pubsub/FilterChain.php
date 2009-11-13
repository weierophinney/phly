<?php
/**
 * Phly - PHp LibrarY
 * 
 * @category  Phly
 * @package   Phly_PubSub
 * @copyright Copyright (C) 2008 - Present, Matthew Weier O'Phinney
 * @author    Matthew Weier O'Phinney <mweierophinney@gmail.com> 
 * @license   New BSD {@link http://www.opensource.org/licenses/bsd-license.php}
 */

namespace phly\pubsub;

/**
 * FilterChain: subject/observer filter chain system
 *
 * @package Phly_PubSub
 * @version $Id: $
 */
class FilterChain
{
    /**
     * @var array All subscribers
     */
    protected $_subscribers = array();

    /**
     * Publish to all subscribers
     *
     * All arguments are passed to each subscriber
     * 
     * @param  mixed $argv Arguments to pass to subscribers (optional)
     * @return void
     */
    public function publish($argv = null)
    {
        $return = null;
        $argv   = func_get_args();
        foreach ($this->_subscribers as $handle) {
            $return = $handle->call($argv);
        }
        return $return;
    }

    /**
     * Notify subscribers until return value of one causes a callback to 
     * evaluate to true
     *
     * Publishes subscribers until the provided callback evaluates the return 
     * value of one as true, or until all subscribers have been executed.
     * 
     * @param  Callable $callback 
     * @param  mixed $argv All arguments are passed to subscribers (optional)
     * @return mixed
     * @throws InvalidCallbackException if invalid callback provided
     */
    public function publishUntil($callback, $argv = null)
    {
        if (!is_callable($callback)) {
            throw new InvalidCallbackException('Invalid filter callback provided');
        }

        $return = null;
        $argv   = func_get_args();
        array_shift($argv);

        foreach ($this->_subscribers as $handle) {
            $return = $handle->call($argv);
            if (call_user_func($callback, $return)) {
                break;
            }
        }
        return $return;
    }

    /**
     * Filter a value
     *
     * Notifies all subscribers passes the single value provided
     * as an argument. Each subsequent subscriber is passed the return value
     * of the previous subscriber, and the value of the last subscriber is 
     * returned.
     * 
     * @param  mixed $value Value to filter
     * @param  mixed $argv Any additional arguments
     * @return mixed
     */
    public function filter($value, $argv = null)
    {
        $argv = func_get_args();
        array_shift($argv);

        foreach ($this->_subscribers as $handle) {
            $callbackArgs = $argv;
            array_unshift($callbackArgs, $value);
            $value = $handle->call($callbackArgs);
        }
        return $value;
    }

    /**
     * Subscribe
     * 
     * @param  string|object $context Function name, class name, or object instance
     * @param  null|string $handler If $context is a class or object, the name of the method to call
     * @return Handle Pub-Sub handle (to allow later unsubscribe)
     */
    public function subscribe($context, $handler = null)
    {
        if (empty($context)) {
            throw new InvalidCallbackException('No callback provided');
        }
        $handle = new Handle(null, $context, $handler);
        if ($index = array_search($handle, $this->_subscribers)) {
            return $this->_subscribers[$index];
        }
        $this->_subscribers[] = $handle;
        return $handle;
    }

    /**
     * Unsubscribe a handler
     * 
     * @param  Handle $handle 
     * @return bool Returns true if topic and handle found, and unsubscribed; returns false if handle not found
     */
    public function unsubscribe(Handle $handle)
    {
        if (false === ($index = array_search($handle, $this->_subscribers))) {
            return false;
        }
        unset($this->_subscribers[$index]);
        return true;
    }

    /**
     * Retrieve all handlers
     * 
     * @param  string $topic 
     * @return array Array of Handle objects
     */
    public function getSubscribedHandles()
    {
        return $this->_subscribers;
    }

    /**
     * Clear all handlers
     * 
     * @return void
     */
    public function clearHandles()
    {
        $this->_subscribers = array();
    }
}
