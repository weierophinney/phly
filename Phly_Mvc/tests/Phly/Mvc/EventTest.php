<?php

class Phly_Mvc_EventTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->event = new Phly_Mvc_Event();
    }

    public function testEventAllowsPropertyAccess()
    {
        $this->event->foo = "bar";
        $this->assertEquals("bar", $this->event->foo);
    }

    public function testEventAllowsArrayAccess()
    {
        $this->event['foo'] = 'bar';
        $this->assertEquals('bar', $this->event['foo']);
    }

    public function testPropertyAndArrayAccessIsEquivalent()
    {
        $this->event->foo = "bar";
        $this->assertEquals('bar', $this->event['foo']);
        $this->event['bar'] = 'baz';
        $this->assertEquals('baz', $this->event->bar);
    }

    public function testArrayItemsPassedToEventConstructorMayBeAccessedAsProperties()
    {
        $event = new Phly_Mvc_Event(array('foo' => 'bar'));
        $this->assertEquals('bar', $event->foo, var_export($event, 1));
    }

    public function testArrayItemsPassedToEventConstructorMayBeAccessedAsArrayMembers()
    {
        $event = new Phly_Mvc_Event(array('foo' => 'bar'));
        $this->assertEquals('bar', $event['foo']);
    }
}
