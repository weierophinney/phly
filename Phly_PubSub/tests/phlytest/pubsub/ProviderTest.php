<?php
/**
 * Phly - PHp LibrarY
 * 
 * @category   Phly
 * @package    Phly_PubSub
 * @subpackage Test
 * @copyright  Copyright (C) 2008 - Present, Matthew Weier O'Phinney
 * @author     Matthew Weier O'Phinney <mweierophinney@gmail.com> 
 * @license    New BSD {@link http://www.opensource.org/licenses/bsd-license.php}
 */

namespace phlytest\pubsub;
use phly\pubsub\Provider as Provider;
use phly\pubsub\Handle as Handle;
use \PHPUnit_Framework_TestCase;

/**
 * @category   Phly
 * @package    Phly_PubSub
 * @subpackage Test
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    New BSD {@link http://www.opensource.org/licenses/bsd-license.php}
 */
class ProviderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (isset($this->message)) {
            unset($this->message);
        }
        $this->provider = new Provider;
    }

    public function tearDown()
    {
    }

    public function testSubscribeShouldReturnHandle()
    {
        $handle = $this->provider->subscribe('test', $this, __METHOD__);
        $this->assertTrue($handle instanceof Handle);
    }

    public function testSubscribeShouldAddHandleToTopic()
    {
        $handle = $this->provider->subscribe('test', $this, __METHOD__);
        $handles = $this->provider->getSubscribedHandles('test');
        $this->assertEquals(1, count($handles));
        $this->assertContains($handle, $handles);
    }

    public function testSubscribeShouldAddTopicIfItDoesNotExist()
    {
        $topics = $this->provider->getTopics();
        $this->assertTrue(empty($topics), var_export($topics, 1));
        $handle = $this->provider->subscribe('test', $this, __METHOD__);
        $topics = $this->provider->getTopics();
        $this->assertFalse(empty($topics));
        $this->assertContains('test', $topics);
    }

    public function testUnsubscribeShouldRemoveHandleFromTopic()
    {
        $handle = $this->provider->subscribe('test', $this, __METHOD__);
        $handles = $this->provider->getSubscribedHandles('test');
        $this->assertContains($handle, $handles);
        $this->provider->unsubscribe($handle);
        $handles = $this->provider->getSubscribedHandles('test');
        $this->assertNotContains($handle, $handles);
    }

    public function testUnsubscribeShouldReturnFalseIfTopicDoesNotExist()
    {
        $handle = $this->provider->subscribe('test', $this, __METHOD__);
        $this->provider->clearHandles('test');
        $this->assertFalse($this->provider->unsubscribe($handle));
    }

    public function testUnsubscribeShouldReturnFalseIfHandleDoesNotExist()
    {
        $handle1 = $this->provider->subscribe('test', $this, __METHOD__);
        $this->provider->clearHandles('test');
        $handle2 = $this->provider->subscribe('test', $this, 'handleTestTopic');
        $this->assertFalse($this->provider->unsubscribe($handle1));
    }

    public function testRetrievingSubscribedHandlesShouldReturnEmptyArrayWhenTopicDoesNotExist()
    {
        $handles = $this->provider->getSubscribedHandles('test');
        $this->assertTrue(empty($handles));
    }

    public function testPublishShouldNotifySubscribedHandlers()
    {
        $handle = $this->provider->subscribe('test', $this, 'handleTestTopic');
        $this->provider->publish('test', 'test message');
        $this->assertEquals('test message', $this->message);
    }

    public function testPublishShouldReturnTheReturnValueOfTheLastInvokedSubscriber()
    {
        $this->provider->subscribe('string.transform', 'trim');
        $this->provider->subscribe('string.transform', 'str_rot13');
        $value = $this->provider->publish('string.transform', ' foo ');
        $this->assertEquals(\str_rot13(' foo '), $value);
    }

    public function testPublishUntilShouldReturnAsSoonAsCallbackReturnsTrue()
    {
        $this->provider->subscribe('foo.bar', 'strpos');
        $this->provider->subscribe('foo.bar', 'strstr');
        $value = $this->provider->publishUntil(
            array($this, 'evaluateStringCallback'), 
            'foo.bar',
            'foo', 'f'
        );
        $this->assertSame(0, $value);
    }

    public function testFilterShouldPassReturnValueOfEachSubscriberToNextSubscriber()
    {
        $this->provider->subscribe('string.transform', 'trim');
        $this->provider->subscribe('string.transform', 'str_rot13');
        $value = $this->provider->filter('string.transform', ' foo ');
        $this->assertEquals(\str_rot13('foo'), $value);
    }

    public function testFilterShouldAllowMultipleArgumentsButFilterOnlyFirst()
    {
        $this->provider->subscribe('filter.test', $this, 'filterTestCallback1');
        $this->provider->subscribe('filter.test', $this, 'filterTestCallback2');
        $obj = (object) array('foo' => 'bar', 'bar' => 'baz');
        $value = $this->provider->filter('filter.test', '', $obj);
        $this->assertEquals('foo:bar;bar:baz;', $value);
        $this->assertEquals((object) array('foo' => 'bar', 'bar' => 'baz'), $obj);
    }

    public function handleTestTopic($message)
    {
        $this->message = $message;
    }

    public function evaluateStringCallback($value)
    {
        return (!$value);
    }

    public function filterTestCallback1($string, $object)
    {
        if (isset($object->foo)) {
            $string .= 'foo:' . $object->foo . ';';
        }
        return $string;
    }

    public function filterTestCallback2($string, $object)
    {
        if (isset($object->bar)) {
            $string .= 'bar:' . $object->bar . ';';
        }
        return $string;
    }
}
