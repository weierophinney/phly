<?php
/**
 * Phly - PHp LibrarY
 * 
 * @category  Phly
 * @package   \phly\PubSub
 * @copyright Copyright (C) 2008 - Present, Matthew Weier O'Phinney
 * @author    Matthew Weier O'Phinney <mweierophinney@gmail.com> 
 * @license   New BSD {@link http://www.opensource.org/licenses/bsd-license.php}
 */

namespace phly;
use \phly\pubsub\Provider as Provider;
use \phly\pubsub\Handle as Handle;

/**
 * \phly\PubSub: Publish-Subscribe system for PHP
 * 
 * @package \phly\PubSub
 * @version $Id: $
 */
class PubSub
{
    /**
     * @var Provider
     */
    protected static $_instance;

    /**
     * Retrieve PubSub provider instance
     * 
     * @return Provider
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::setInstance(new Provider());
        }
        return self::$_instance;
    }

    /**
     * Set PubSub provider instance
     * 
     * @param  Provider $provider 
     * @return void
     */
    public static function setInstance(Provider $provider)
    {
        self::$_instance = $provider;
    }

    /**
     * Publish to all handlers for a given topic
     * 
     * @param  string $topic 
     * @param  mixed $args All arguments besides the topic are passed as arguments to the handler
     * @return void
     */
    public static function publish($topic, $args = null)
    {
        $provider = self::getInstance();
        return $provider->publish($topic, $args);
    }

    /**
     * Subscribe to a topic
     * 
     * @param  string $topic 
     * @param  string|object $context Function name, class name, or object instance
     * @param  null|string $handler If $context is a class or object, the name of the method to call
     * @return Phly_PubSub_Handle Pub-Sub handle (to allow later unsubscribe)
     */
    public static function subscribe($topic, $context, $handler = null)
    {
        $provider = self::getInstance();
        return $provider->subscribe($topic, $context, $handler);
    }

    /**
     * Unsubscribe a handler from a topic 
     * 
     * @param  Phly_PubSub_Handle $handle 
     * @return bool Returns true if topic and handle found, and unsubscribed; returns false if either topic or handle not found
     */
    public static function unsubscribe(Handle $handle)
    {
        $provider = self::getInstance();
        return $provider->unsubscribe($handle);
    }

    /**
     * Retrieve all registered topics
     * 
     * @return array
     */
    public static function getTopics()
    {
        $provider = self::getInstance();
        return $provider->getTopics();
    }

    /**
     * Retrieve all handlers for a given topic
     * 
     * @param  string $topic 
     * @return array Array of Handle objects
     */
    public static function getSubscribedHandles($topic)
    {
        $provider = self::getInstance();
        return $provider->getSubscribedHandles($topic);
    }

    /**
     * Clear all handlers for a given topic
     * 
     * @param  string $topic 
     * @return void
     */
    public static function clearHandles($topic)
    {
        $provider = self::getInstance();
        return $provider->clearHandles($topic);
    }
}
