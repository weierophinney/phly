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

    public function testEventManagerIsUndefinedByDefault()
    {
        $this->assertNull($this->event->getEventManager());
    }

    public function testEventManagerIsMutable()
    {
        $em = new Phly_Mvc_EventManager();
        $this->event->setEventManager($em);
        $this->assertSame($em, $this->event->getEventManager());
    }

    /**
     * @expectedException Phly_Mvc_Exception
     */
    public function testUsingPropertyOverloadingToSetEventManagerEnforcesType()
    {
        $this->event->eventManager = 'foo';
    }

    public function testRequestIsUndefinedByDefault()
    {
        $this->assertNull($this->event->getRequest());
    }

    public function testRequestIsMutable()
    {
        $em = new Phly_Mvc_Request_Request();
        $this->event->setRequest($em);
        $this->assertSame($em, $this->event->getRequest());
    }

    /**
     * @expectedException Phly_Mvc_Exception
     */
    public function testUsingPropertyOverloadingToSetRequestEnforcesType()
    {
        $this->event->request = 'foo';
    }

    public function testResponseIsUndefinedByDefault()
    {
        $this->assertNull($this->event->getResponse());
    }

    public function testResponseIsMutable()
    {
        $em = new Phly_Mvc_Response_Response();
        $this->event->setResponse($em);
        $this->assertSame($em, $this->event->getResponse());
    }

    /**
     * @expectedException Phly_Mvc_Exception
     */
    public function testUsingPropertyOverloadingToSetResponseEnforcesType()
    {
        $this->event->response = 'foo';
    }
}
