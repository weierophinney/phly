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
        $handle = new Handle('foo', '\phlytest\pubsub\handlers\ObjectCallback', 'test');
        $this->assertSame(array('\phlytest\pubsub\handlers\ObjectCallback', 'test'), $handle->getCallback());
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
    public function testPassingInvalidCallbackShouldRaiseInvalidCallbackExceptionDuringCall()
    {
        $handle = new Handle('Invokable', 'boguscallback');
        $handle->call();
    }

    public function testCallShouldReturnTheReturnValueOfTheCallback()
    {
        $handle = new Handle('foo', '\phlytest\pubsub\handlers\ObjectCallback', 'test');
        if (!is_callable(array('\phlytest\pubsub\handlers\ObjectCallback', 'test'))) {
            echo "\nClass exists? " . var_export(class_exists('\phlytest\pubsub\handlers\ObjectCallback'), 1) . "\n";
            echo "Include path: " . get_include_path() . "\n";
        }
        $this->assertEquals('bar', $handle->call(array()));
    }

    public function testStringCallbackResolvingToClassNameShouldCallViaInvoke()
    {
        $handle = new Handle('foo', '\phlytest\pubsub\handlers\Invokable');
        $this->assertEquals('__invoke', $handle->call(), var_export($handle->getCallback(), 1));
    }

    /**
     * @expectedException \phly\pubsub\InvalidCallbackException
     */
    public function testStringCallbackReferringToClassWithoutDefinedInvokeShouldRaiseException()
    {
        $handle = new Handle('foo', '\phlytest\pubsub\handlers\InstanceMethod');
        $handle->call();
    }

    public function testCallbackConsistingOfStringContextWithNonStaticMethodShouldInstantiateContext()
    {
        $handle = new Handle('foo', 'phlytest\pubsub\handlers\InstanceMethod', 'callable');
        $this->assertEquals('callable', $handle->call());
    }

    public function testCallbackToClassImplementingOverloadingShouldSucceed()
    {
        $handle = new Handle('foo', '\phlytest\pubsub\handlers\Overloadable', 'foo');
        $this->assertEquals('foo', $handle->call());
    }

    public function handleCall()
    {
        $this->args = func_get_args();
    }
}
