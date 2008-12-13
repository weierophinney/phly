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

// Call Phly_PubSub_HandleTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "Phly_PubSub_HandleTest::main");
}

/**
 * Test helper
 */
require_once dirname(__FILE__) . '/../../TestHelper.php';

/**
 * Phly_PubSub_Handle
 */
require_once 'Phly/PubSub/Handle.php';

/**
 * @category   Phly
 * @package    Phly_PubSub
 * @subpackage Test
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    New BSD {@link http://www.opensource.org/licenses/bsd-license.php}
 */
class Phly_PubSub_HandleTest extends PHPUnit_Framework_TestCase
{
    public static function main()
    {
        $suite  = new PHPUnit_Framework_TestSuite("Phly_PubSub_HandleTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    public function setUp()
    {
        if (isset($this->args)) {
            unset($this->args);
        }
    }

    public function testGetTopicShouldReturnTopic()
    {
        $handle = new Phly_PubSub_Handle('foo', 'bar');
        $this->assertEquals('foo', $handle->getTopic());
    }

    public function testCallbackShouldBeStringIfNoHandlerPassedToConstructor()
    {
        $handle = new Phly_PubSub_Handle('foo', 'bar');
        $this->assertSame('bar', $handle->getCallback());
    }

    public function testCallbackShouldBeArrayIfHandlerPassedToConstructor()
    {
        $handle = new Phly_PubSub_Handle('foo', 'bar', 'baz');
        $this->assertSame(array('bar', 'baz'), $handle->getCallback());
    }

    public function testCallShouldInvokeCallbackWithSuppliedArguments()
    {
        $handle = new Phly_PubSub_Handle('foo', $this, 'handleCall');
        $args   = array('foo', 'bar', 'baz');
        $handle->call($args);
        $this->assertSame($args, $this->args);
    }

    public function handleCall()
    {
        $this->args = func_get_args();
    }
}

// Call Phly_PubSub_HandleTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "Phly_PubSub_HandleTest::main") {
    Phly_PubSub_HandleTest::main();
}
