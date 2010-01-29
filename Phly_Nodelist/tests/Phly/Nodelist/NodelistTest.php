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
        $o  = new stdClass;
        $nl->each(function($v, $k) use ($o) {
            $o->$k = $v;
        });
        foreach ($nl as $k => $v) {
            $this->assertEquals($v, $o->$k);
        }
    }
}
