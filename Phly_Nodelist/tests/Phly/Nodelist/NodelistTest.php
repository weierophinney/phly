<?php
namespace PhlyTest\Nodelist;

use \PHPUnit_Framework_Testcase as Testcase;
use Phly\Nodelist\Nodelist;
use \ArrayObject;
use \stdClass;

/**
 * Testcase for Nodelist
 **/
class NodelistTest extends TestCase
{
    public function testConstructorShouldAcceptStrings()
    {
        $nl = new Nodelist('foo');
        $this->assertTrue($nl instanceof Nodelist);
    }

    public function testConstructorShouldAcceptArrays()
    {
        $nl = new Nodelist(array('foo'));
        $this->assertTrue($nl instanceof Nodelist);
    }

    public function testConstructorShouldAcceptTraversableObjects()
    {
        $nl = new Nodelist(new ArrayObject(array('foo')));
        $this->assertTrue($nl instanceof Nodelist);
    }

    /**
     * @expectedException \Phly\Nodelist\InvalidListTypeException
     */
    public function testConstructorShouldThrowExceptionForInvalidList()
    {
        $nl = new Nodelist(true);
    }

    public function testUsingStringInNodelistAllowsCastingToString()
    {
        $nl = new Nodelist('foo');
        $this->assertEquals('foo', $nl->toString());
    }

    public function testCastingNonStringNodelistsToStringShouldReturnEmptyString()
    {
        $nl = new Nodelist(array('foo'));
        $this->assertEquals('', $nl->toString());
    }

    public function testShouldAllowAttachingClosuresAsMethods()
    {
        $closure = function($n) { };
        $nl = new NodeList(array());
        $nl->addMethod('doNothing', $closure);
        $methods = $nl->getMethodClosures();
        $this->assertContains('doNothing', array_keys($methods));
        $this->assertSame($closure, $methods['doNothing']);
    }

    public function testShouldAllowRetrievingListOfDefinedMethods()
    {
        $closure = function($n) { };
        $nl = new NodeList(array());
        $nl->addMethod('doNothing', $closure);
        $methods = $nl->getMethodClosures();
        $this->assertEquals(array('doNothing' => $closure), $methods);
    }

    /**
     * @expectedException \Phly\Nodelist\InvalidClosureException
     */
    public function testCallingUndefinedMethodsShouldRaiseAnException()
    {
        $nl = new NodeList(array());
        $nl->doNothing();
    }

    public function testCallingDefinedMethodShouldPassValuesToClosure()
    {
        $o  = new stdClass;
        $o->args = array();
        $nl = new NodeList(array());
        $closure = function() use ($o) {
            $o->args[] = func_get_args();
        };
        $nl->addMethod('check', $closure);
        $nl->check('foo', 'bar');
        foreach (array('foo', 'bar') as $a) {
            foreach ($o->args as $args) {
                $this->assertContains($a, $args);
            }
        }
    }

    public function testCallingDefinedMethodShouldPassNodelistToClosure()
    {
        $o  = new stdClass;
        $o->args = array();
        $nl = new NodeList(array());
        $closure = function() use ($o) {
            $o->args[] = func_get_args();
        };
        $nl->addMethod('check', $closure);
        $nl->check('foo', 'bar');
        foreach ($o->args as $args) {
            $this->assertContains($nl, $args);
        }
    }

    public function testCallingDefinedMethodShouldReturnNodelist()
    {
        $o  = new stdClass;
        $nl = new NodeList(array());
        $closure = function() { };
        $nl->addMethod('check', $closure);
        $return = $nl->check('foo', 'bar');
        $this->assertSame($nl, $return);
    }

    public function testCallingDefinedMethodShouldExecuteOnEachItemOfNodelist()
    {
        $a  = array('foo', 'bar');
        $nl = new NodeList($a);
        $closure = function($v, $k, $l) { 
            $l[$k] = strtoupper($v);
        };
        $nl->addMethod('ucase', $closure);
        $nl->ucase();
        foreach ($nl as $k => $v) {
            $this->assertEquals(strtoupper($a[$k]), $v);
        }
    }

    public function testCallingEachShouldReturnNodelist()
    {
        $nl = new NodeList(array());
        $return = $nl->each(function($v) { });
        $this->assertSame($nl, $return);
    }

    public function testToArrayShouldReturnArrayRepresentationForStrings()
    {
        $nl = new NodeList('foo');
        $a  = $nl->toArray();
        $this->assertEquals('foo', implode('', $a));
    }

    public function testToArrayShouldReturnArrayForArrays()
    {
        $a  = array('foo');
        $nl = new NodeList($a);
        $r  = $nl->toArray();
        $this->assertEquals($a, $r);
    }

    public function testToArrayShouldReturnArrayForTraversableObjects()
    {
        $a  = new ArrayObject(array('foo'));
        $nl = new NodeList($a);
        $r  = $nl->toArray();
        $this->assertEquals($a->getArrayCopy(), $r);
    }

    public function testGetOriginalShouldReturnOriginalStringValue()
    {
        $nl = new NodeList('foo');
        $this->assertEquals('foo', $nl->getOriginal());
    }

    public function testGetOriginalShouldReturnOriginalArray()
    {
        $a  = array('foo');
        $nl = new NodeList($a);
        $r  = $nl->getOriginal();
        $this->assertSame($a, $r);
    }

    public function testGetOriginalShouldReturnIdenticalObject()
    {
        $a  = new ArrayObject(array('foo'));
        $nl = new NodeList($a);
        $r  = $nl->getOriginal();
        $this->assertSame($a, $r);
    }

    public function testEachShouldCallClosureOnAllElements()
    {
        $nl = new Nodelist(array('foo' => 'bar', 'bar' => 'baz'));
        $o  = new ArrayObject;
        $nl->each(function($v) use ($o) {
            $o->append($v);
        });
        $test = $o->getArrayCopy();
        foreach ($nl as $v) {
            $this->assertContains($v, $test, "$v: " . var_export($test, 1));
        }
    }

    public function testClosurePassedToEachShouldModifyNodelistInPlace()
    {
        $a  = array('foo' => 'bar', 'bar' => 'baz');
        $nl = new Nodelist($a);
        $nl->each(function ($v, $k, $l) {
            $l[$k] = strtoupper($v);
        });
        foreach ($a as $key => $value) {
            $this->assertEquals(strtoupper($value), $nl[$key]);
        }
    }

    public function testPushWithNoKeyAppendsValueToNodelist()
    {
        $nl = new Nodelist(array('foo', 'bar'));
        $nl->push('baz');
        $this->assertEquals(array('foo', 'bar', 'baz'), $nl->toArray());
    }

    public function testPushWithKeyAddsValueToNodelistWithKey()
    {
        $nl = new Nodelist(array('foo' => 'FOO', 'bar' => 'BAR'));
        $nl->push('BAZ', 'baz');
        $this->assertSame(array('foo' => 'FOO', 'bar' => 'BAR', 'baz' => 'BAZ'), $nl->toArray());
    }

    public function testPopRemovesLastItemFromNodelist()
    {
        $nl = new Nodelist(array('foo', 'bar'));
        $r  = $nl->pop();
        $this->assertEquals(array('foo'), $nl->toArray());
        $this->assertEquals('bar', $r);
    }

    public function testPopWithKeyRemovesNamedItemFromNodelist()
    {
        $nl = new Nodelist(array('foo', 'bar'));
        $r  = $nl->pop(0);
        $this->assertEquals(array(1 => 'bar'), $nl->toArray());
        $this->assertEquals('foo', $r);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testPopWithInvalidKeyShouldRaiseException()
    {
        $nl = new Nodelist(array('foo', 'bar'));
        $r  = $nl->pop('foo');
    }

    public function testUnshiftWithNoKeyPrependsValueToNodelist()
    {
        $nl = new Nodelist(array('foo', 'bar'));
        $nl->unshift('baz');
        $this->assertEquals(array('baz', 'foo', 'bar'), $nl->toArray());
    }

    public function testWithKeyAddsValueToNodelistWithKey()
    {
        $nl = new Nodelist(array('foo' => 'FOO', 'bar' => 'BAR'));
        $nl->unshift('BAZ', 'baz');
        $this->assertSame(array('baz' => 'BAZ', 'foo' => 'FOO', 'bar' => 'BAR'), $nl->toArray());
    }

    public function testShiftRemovesFirstItemFromNodelist()
    {
        $nl = new Nodelist(array('foo', 'bar'));
        $r  = $nl->shift();
        $this->assertEquals(array('bar'), $nl->toArray());
        $this->assertEquals('foo', $r);
    }

    public function testShiftWithKeyRemovesNamedItemFromNodelist()
    {
        $nl = new Nodelist(array('foo', 'bar'));
        $r  = $nl->shift(1);
        $this->assertEquals(array(0 => 'foo'), $nl->toArray());
        $this->assertEquals('bar', $r);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testShiftWithInvalidKeyShouldRaiseException()
    {
        $nl = new Nodelist(array('foo', 'bar'));
        $r  = $nl->shift('foo');
    }
}
