<?php
/**
 * Phly - PHp LibrarY
 * 
 * @category   Phly
 * @package    Phly_PubSub
 * @subpackage Test
 * @copyright  Copyright (C) 2008 - Present, Matthew Weier O'Phinney
 * @author     Matthew Weier O'Phinney <mweierophinney@gmail.com> 
 * @license   New BSD {@link http://mwop.net/license}
 */

// Call Phly_PubSubTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "Phly_PubSubTest::main");
}

/**
 * Test helper
 */
require_once dirname(__FILE__) . '/../TestHelper.php';

/**
 * Phly_PubSub
 */
require_once 'Phly/PubSub.php';

/**
 * @category   Phly
 * @package    Phly_PubSub
 * @subpackage Test
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    New BSD {@link http://www.opensource.org/licenses/bsd-license.php}
 */
class Phly_PubSubTest extends PHPUnit_Framework_TestCase
{
    public static function main()
    {
        $suite  = new PHPUnit_Framework_TestSuite("Phly_PubSubTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
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
        $topics = Phly_PubSub::getTopics();
        foreach ($topics as $topic) {
            Phly_PubSub::clearHandles($topic);
        }
    }

    public function testSubscribeShouldReturnHandle()
    {
        $handle = Phly_PubSub::subscribe('test', $this, __METHOD__);
        $this->assertTrue($handle instanceof Phly_PubSub_Handle);
    }

    public function testSubscribeShouldAddHandleToTopic()
    {
        $handle = Phly_PubSub::subscribe('test', $this, __METHOD__);
        $handles = Phly_PubSub::getSubscribedHandles('test');
        $this->assertEquals(1, count($handles));
        $this->assertContains($handle, $handles);
    }

    public function testSubscribeShouldAddTopicIfItDoesNotExist()
    {
        $topics = Phly_PubSub::getTopics();
        $this->assertTrue(empty($topics), var_export($topics, 1));
        $handle = Phly_PubSub::subscribe('test', $this, __METHOD__);
        $topics = Phly_PubSub::getTopics();
        $this->assertFalse(empty($topics));
        $this->assertContains('test', $topics);
    }

    public function testUnsubscribeShouldRemoveHandleFromTopic()
    {
        $handle = Phly_PubSub::subscribe('test', $this, __METHOD__);
        $handles = Phly_PubSub::getSubscribedHandles('test');
        $this->assertContains($handle, $handles);
        Phly_PubSub::unsubscribe($handle);
        $handles = Phly_PubSub::getSubscribedHandles('test');
        $this->assertNotContains($handle, $handles);
    }

    public function testUnsubscribeShouldReturnFalseIfTopicDoesNotExist()
    {
        $handle = Phly_PubSub::subscribe('test', $this, __METHOD__);
        Phly_PubSub::clearHandles('test');
        $this->assertFalse(Phly_PubSub::unsubscribe($handle));
    }

    public function testUnsubscribeShouldReturnFalseIfHandleDoesNotExist()
    {
        $handle1 = Phly_PubSub::subscribe('test', $this, __METHOD__);
        Phly_PubSub::clearHandles('test');
        $handle2 = Phly_PubSub::subscribe('test', $this, 'handleTestTopic');
        $this->assertFalse(Phly_PubSub::unsubscribe($handle1));
    }

    public function testRetrievingSubscribedHandlesShouldReturnEmptyArrayWhenTopicDoesNotExist()
    {
        $handles = Phly_PubSub::getSubscribedHandles('test');
        $this->assertTrue(empty($handles));
    }

    public function testPublishShouldNotifySubscribedHandlers()
    {
        $handle = Phly_PubSub::subscribe('test', $this, 'handleTestTopic');
        Phly_PubSub::publish('test', 'test message');
        $this->assertEquals('test message', $this->message);
    }

    public function handleTestTopic($message)
    {
        $this->message = $message;
    }
}

// Call Phly_PubSubTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "Phly_PubSubTest::main") {
    Phly_PubSubTest::main();
}
