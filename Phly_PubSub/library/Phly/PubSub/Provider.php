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

/**
 * Phly_PubSub_Provider: per-instance Publish-Subscribe system
 *
 * Use Phly_PubSub_Provider when you want to create a per-instance plugin 
 * system for your objects.
 * 
 * @package Phly_PubSub
 * @version $Id: $
 */
class Phly_PubSub_Provider
{
    /**
     * Subscribed topics and their handles
     */
    protected $_topics = array();

    /**
     * Publish to all handlers for a given topic
     * 
     * @param  string $topic 
     * @param  mixed $argv All arguments besides the topic are passed as arguments to the handler
     * @return void
     */
    public function publish($topic, $argv = null)
    {
        if (empty($this->_topics[$topic])) {
            return;
        }

        $return = null;
        $argv   = func_get_args();
        array_shift($argv);
        foreach ($this->_topics[$topic] as $handle) {
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
     * @param  string $topic 
     * @param  mixed $argv All arguments besides the topic are passed as arguments to the handler
     * @return mixed
     * @throws Phly_PubSub_InvalidCallbackException if invalid callback provided
     */
    public function publishUntil($callback, $topic, $argv = null)
    {
        if (!is_callable($callback)) {
            throw new Phly_PubSub_InvalidCallbackException('Invalid filter callback provided');
        }

        if (empty($this->_topics[$topic])) {
            return;
        }

        $return = null;
        $argv   = func_get_args();
        $argv   = array_slice($argv, 2);
        foreach ($this->_topics[$topic] as $handle) {
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
     * Notifies subscribers to the topic and passes the single value provided
     * as an argument. Each subsequent subscriber is passed the return value
     * of the previous subscriber, and the value of the last subscriber is 
     * returned.
     * 
     * @param  string $topic 
     * @param  mixed $value 
     * @return mixed
     */
    public function filter($topic, $value)
    {
        if (empty($this->_topics[$topic])) {
            return;
        }

        foreach ($this->_topics[$topic] as $handle) {
            $value = $handle->call(array($value));
        }
        return $value;
    }

    /**
     * Subscribe to a topic
     * 
     * @param  string $topic 
     * @param  string|object $context Function name, class name, or object instance
     * @param  null|string $handler If $context is a class or object, the name of the method to call
     * @return Phly_PubSub_Handle Pub-Sub handle (to allow later unsubscribe)
     */
    public function subscribe($topic, $context, $handler = null)
    {
        if (empty($this->_topics[$topic])) {
            $this->_topics[$topic] = array();
        }
        $handle = new Phly_PubSub_Handle($topic, $context, $handler);
        if ($index = array_search($handle, $this->_topics[$topic])) {
            return $this->_topics[$topic][$index];
        }
        $this->_topics[$topic][] = $handle;
        return $handle;
    }

    /**
     * Unsubscribe a handler from a topic 
     * 
     * @param  Phly_PubSub_Handle $handle 
     * @return bool Returns true if topic and handle found, and unsubscribed; returns false if either topic or handle not found
     */
    public function unsubscribe(Phly_PubSub_Handle $handle)
    {
        $topic = $handle->getTopic();
        if (empty($this->_topics[$topic])) {
            return false;
        }
        if (false === ($index = array_search($handle, $this->_topics[$topic]))) {
            return false;
        }
        unset($this->_topics[$topic][$index]);
        return true;
    }

    /**
     * Retrieve all registered topics
     * 
     * @return array
     */
    public function getTopics()
    {
        return array_keys($this->_topics);
    }

    /**
     * Retrieve all handlers for a given topic
     * 
     * @param  string $topic 
     * @return array Array of Phly_PubSub_Handle objects
     */
    public function getSubscribedHandles($topic)
    {
        if (empty($this->_topics[$topic])) {
            return array();
        }
        return $this->_topics[$topic];
    }

    /**
     * Clear all handlers for a given topic
     * 
     * @param  string $topic 
     * @return void
     */
    public function clearHandles($topic)
    {
        if (!empty($this->_topics[$topic])) {
            unset($this->_topics[$topic]);
        }
    }
}
