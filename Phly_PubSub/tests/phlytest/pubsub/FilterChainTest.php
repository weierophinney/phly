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
use phly\pubsub\FilterChain as FilterChain;
use phly\pubsub\Handle as Handle;
use \PHPUnit_Framework_TestCase;

/**
 * @category   Phly
 * @package    Phly_PubSub
 * @subpackage Test
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    New BSD {@link http://www.opensource.org/licenses/bsd-license.php}
 */
class FilterChainTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (isset($this->message)) {
            unset($this->message);
        }
        $this->filterchain = new FilterChain;
    }

    public function tearDown()
    {
    }

    public function testSubscribeShouldReturnHandle()
    {
        $handle = $this->filterchain->subscribe($this, __METHOD__);
        $this->assertTrue($handle instanceof Handle);
    }

    public function testSubscribeShouldAddHandleToSubscribers()
    {
        $handle = $this->filterchain->subscribe($this, __METHOD__);
        $handles = $this->filterchain->getSubscribedHandles();
        $this->assertEquals(1, count($handles));
        $this->assertContains($handle, $handles);
    }

    public function testUnsubscribeShouldRemoveHandleFromSubscribers()
    {
        $handle = $this->filterchain->subscribe($this, __METHOD__);
        $handles = $this->filterchain->getSubscribedHandles();
        $this->assertContains($handle, $handles);
        $this->filterchain->unsubscribe($handle);
        $handles = $this->filterchain->getSubscribedHandles();
        $this->assertNotContains($handle, $handles);
    }

    public function testUnsubscribeShouldReturnFalseIfHandleDoesNotExist()
    {
        $handle1 = $this->filterchain->subscribe($this, __METHOD__);
        $this->filterchain->clearHandles();
        $handle2 = $this->filterchain->subscribe($this, 'handleTestTopic');
        $this->assertFalse($this->filterchain->unsubscribe($handle1));
    }

    public function testRetrievingSubscribedHandlesShouldReturnEmptyArrayWhenNoSubscribersExist()
    {
        $handles = $this->filterchain->getSubscribedHandles();
        $this->assertTrue(empty($handles));
    }

    public function testPublishShouldNotifySubscribedHandlers()
    {
        $handle = $this->filterchain->subscribe($this, 'handleTestTopic');
        $this->filterchain->publish('test message');
        $this->assertEquals('test message', $this->message);
    }

    public function testPublishShouldReturnTheReturnValueOfTheLastInvokedSubscriber()
    {
        $this->filterchain->subscribe('trim');
        $this->filterchain->subscribe('str_rot13');
        $value = $this->filterchain->publish(' foo ');
        $this->assertEquals(\str_rot13(' foo '), $value);
    }

    public function testPublishUntilShouldReturnAsSoonAsCallbackReturnsTrue()
    {
        $this->filterchain->subscribe('strpos');
        $this->filterchain->subscribe('strstr');
        $value = $this->filterchain->publishUntil(
            array($this, 'evaluateStringCallback'), 
            'foo', 'f'
        );
        $this->assertSame(0, $value);
    }

    public function testFilterShouldPassReturnValueOfEachSubscriberToNextSubscriber()
    {
        $this->filterchain->subscribe('trim');
        $this->filterchain->subscribe('str_rot13');
        $value = $this->filterchain->filter(' foo ');
        $this->assertEquals(\str_rot13('foo'), $value);
    }

    public function testFilterShouldAllowMultipleArgumentsButFilterOnlyFirst()
    {
        $this->filterchain->subscribe($this, 'filterTestCallback1');
        $this->filterchain->subscribe($this, 'filterTestCallback2');
        $obj = (object) array('foo' => 'bar', 'bar' => 'baz');
        $value = $this->filterchain->filter('', $obj);
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
