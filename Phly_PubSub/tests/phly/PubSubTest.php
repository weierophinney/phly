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

namespace phly\test;
use \phly\PubSub as PubSub;
use \phly\pubsub\Handle as Handle;

// Call Phly_PubSubTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "PubSubTest::main");
}

/**
 * Test helper
 */
require_once dirname(__FILE__) . '/../TestHelper.php';

/**
 * @category   Phly
 * @package    Phly_PubSub
 * @subpackage Test
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    New BSD {@link http://www.opensource.org/licenses/bsd-license.php}
 */
class PubSubTest extends \PHPUnit_Framework_TestCase
{
    public static function main()
    {
        $suite  = new \PHPUnit_Framework_TestSuite("PubSubTest");
        $result = \PHPUnit_TextUI_TestRunner::run($suite);
    }

    public function setUp()
    {
        if (isset($this->message)) {
            unset($this->message);
        }
        $this->clearAllTopics();
    }

    public function tearDown()
    {
        $this->clearAllTopics();
    }

    public function clearAllTopics()
    {
        $topics = PubSub::getTopics();
        foreach ($topics as $topic) {
            PubSub::clearHandles($topic);
        }
    }

    public function testSubscribeShouldReturnHandle()
    {
        $handle = PubSub::subscribe('test', $this, __METHOD__);
        $this->assertTrue($handle instanceof Handle);
    }

    public function testSubscribeShouldAddHandleToTopic()
    {
        $handle = PubSub::subscribe('test', $this, __METHOD__);
        $handles = PubSub::getSubscribedHandles('test');
        $this->assertEquals(1, count($handles));
        $this->assertContains($handle, $handles);
    }

    public function testSubscribeShouldAddTopicIfItDoesNotExist()
    {
        $topics = PubSub::getTopics();
        $this->assertTrue(empty($topics), var_export($topics, 1));
        $handle = PubSub::subscribe('test', $this, __METHOD__);
        $topics = PubSub::getTopics();
        $this->assertFalse(empty($topics));
        $this->assertContains('test', $topics);
    }

    public function testUnsubscribeShouldRemoveHandleFromTopic()
    {
        $handle = PubSub::subscribe('test', $this, __METHOD__);
        $handles = PubSub::getSubscribedHandles('test');
        $this->assertContains($handle, $handles);
        PubSub::unsubscribe($handle);
        $handles = PubSub::getSubscribedHandles('test');
        $this->assertNotContains($handle, $handles);
    }

    public function testUnsubscribeShouldReturnFalseIfTopicDoesNotExist()
    {
        $handle = PubSub::subscribe('test', $this, __METHOD__);
        PubSub::clearHandles('test');
        $this->assertFalse(PubSub::unsubscribe($handle));
    }

    public function testUnsubscribeShouldReturnFalseIfHandleDoesNotExist()
    {
        $handle1 = PubSub::subscribe('test', $this, __METHOD__);
        PubSub::clearHandles('test');
        $handle2 = PubSub::subscribe('test', $this, 'handleTestTopic');
        $this->assertFalse(PubSub::unsubscribe($handle1));
    }

    public function testRetrievingSubscribedHandlesShouldReturnEmptyArrayWhenTopicDoesNotExist()
    {
        $handles = PubSub::getSubscribedHandles('test');
        $this->assertTrue(empty($handles));
    }

    public function testPublishShouldNotifySubscribedHandlers()
    {
        $handle = PubSub::subscribe('test', $this, 'handleTestTopic');
        PubSub::publish('test', 'test message');
        $this->assertEquals('test message', $this->message);
    }

    public function handleTestTopic($message)
    {
        $this->message = $message;
    }
}

// Call Phly_PubSubTest::main() if this source file is executed directly.
if (\PHPUnit_MAIN_METHOD == "PubSubTest::main") {
    PubSubTest::main();
}
