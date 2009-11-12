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

// Call \phlytest\pubsub\HandleTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "\phlytest\pubsub\HandleTest::main");
}

/**
 * Test helper
 */
require_once dirname(__FILE__) . '/../../TestHelper.php';

use phly\pubsub\Handle as Handle;
use \PHPUnit_Framework_TestCase;

/**
 * @category   Phly
 * @package    Phly_PubSub
 * @subpackage Test
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    New BSD {@link http://www.opensource.org/licenses/bsd-license.php}
 */
class HandleTest extends \PHPUnit_Framework_TestCase
{
    public static function main()
    {
        $suite  = new \PHPUnit_Framework_TestSuite(__CLASS__);
        $result = \PHPUnit_TextUI_TestRunner::run($suite);
    }

    public function setUp()
    {
        if (isset($this->args)) {
            unset($this->args);
        }
    }

    public function testGetTopicShouldReturnTopic()
    {
        $handle = new Handle('foo', 'rand');
        $this->assertEquals('foo', $handle->getTopic());
    }

    public function testCallbackShouldBeStringIfNoHandlerPassedToConstructor()
    {
        $handle = new Handle('foo', 'rand');
        $this->assertSame('rand', $handle->getCallback());
    }

    public function testCallbackShouldBeArrayIfHandlerPassedToConstructor()
    {
        $handle = new Handle('foo', '\phlytest\pubsub\HandleTest_ObjectCallback', 'test');
        $this->assertSame(array('\phlytest\pubsub\HandleTest_ObjectCallback', 'test'), $handle->getCallback());
    }

    public function testCallShouldInvokeCallbackWithSuppliedArguments()
    {
        $handle = new Handle('foo', $this, 'handleCall');
        $args   = array('foo', 'bar', 'baz');
        $handle->call($args);
        $this->assertSame($args, $this->args);
    }

    /**
     * @expectedException \phly\pubsub\InvalidCallbackException
     */
    public function testPassingInvalidCallbackShouldRaiseInvalidCallbackException()
    {
        $handle = new Handle('foo', 'boguscallback');
    }

    public function testCallShouldReturnTheReturnValueOfTheCallback()
    {
        $handle = new Handle('foo', '\phlytest\pubsub\HandleTest_ObjectCallback', 'test');
        $this->assertEquals('bar', $handle->call(array()));
    }

    public function handleCall()
    {
        $this->args = func_get_args();
    }
}

class HandleTest_ObjectCallback
{
    public static function test()
    {
        return 'bar';
    }
}

// Call \phlytest\pubsub\HandleTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "\phlytest\pubsub\HandleTest::main") {
    HandleTest::main();
}
