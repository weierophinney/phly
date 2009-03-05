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
 * Phly_PubSub: Publish-Subscribe system for PHP
 * 
 * @package Phly_PubSub
 * @version $Id: $
 */
class Phly_PubSub
{
    /**
     * @var Phly_PubSub_Provider
     */
    protected static $_instance;

    /**
     * Retrieve PubSub provider instance
     * 
     * @return Phly_PubSub_Provider
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::setInstance(new Phly_PubSub_Provider());
        }
        return self::$_instance;
    }

    /**
     * Set PubSub provider instance
     * 
     * @param  Phly_PubSub_Provider $provider 
     * @return void
     */
    public static function setInstance(Phly_PubSub_Provider $provider)
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
    public static function unsubscribe(Phly_PubSub_Handle $handle)
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
     * @return array Array of Phly_PubSub_Handle objects
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
