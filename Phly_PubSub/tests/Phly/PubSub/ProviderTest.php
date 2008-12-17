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

// Call Phly_PubSub_ProviderTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "Phly_PubSub_ProviderTest::main");
}

/**
 * Test helper
 */
require_once dirname(__FILE__) . '/../../TestHelper.php';

/**
 * Phly_PubSub
 */
require_once 'Phly/PubSub/Provider.php';

/**
 * @category   Phly
 * @package    Phly_PubSub
 * @subpackage Test
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    New BSD {@link http://www.opensource.org/licenses/bsd-license.php}
 */
class Phly_PubSub_ProviderTest extends PHPUnit_Framework_TestCase
{
    public static function main()
    {
        $suite  = new PHPUnit_Framework_TestSuite("Phly_PubSub_ProviderTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    public function setUp()
    {
        if (isset($this->message)) {
            unset($this->message);
        }
        $this->provider = new Phly_PubSub_Provider;
    }

    public function tearDown()
    {
    }

    public function testSubscribeShouldReturnHandle()
    {
        $handle = $this->provider->subscribe('test', $this, __METHOD__);
        $this->assertTrue($handle instanceof Phly_PubSub_Handle);
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

    public function handleTestTopic($message)
    {
        $this->message = $message;
    }
}

// Call Phly_PubSub_ProviderTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "Phly_PubSub_ProviderTest::main") {
    Phly_PubSub_ProviderTest::main();
}
