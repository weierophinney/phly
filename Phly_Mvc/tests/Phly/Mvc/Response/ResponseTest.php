<?php

class Phly_Mvc_Response_ResponseTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->response = new Phly_Mvc_Response_Response();
    }

    public function testEventIsNullByDefault()
    {
        $this->assertNull($this->response->getEvent());
    }

    public function testEventMayBeSet()
    {
        $e = new Phly_Mvc_Event();
        $this->response->setEvent($e);
        $this->assertSame($e, $this->response->getEvent());
    }

    public function testNoViewsAreRegisteredByDefault()
    {
        $views = $this->response->getViews();
        $this->assertTrue(empty($views));
    }

    public function testAddingViewRegistersAsViewVarsPair()
    {
        $vars = array('foo' => 'bar');
        $this->response->addView('foo/bar', $vars);
        $test = $this->response->getViewVars('foo/bar');
        $this->assertSame($vars, $test);
    }

    public function testAllowsRemovingRegisteredViews()
    {
        $vars = array('foo' => 'bar');
        $this->response->addView('foo/bar', $vars);
        $this->response->removeView('foo/bar');
        $this->assertFalse($this->response->hasView('foo/bar'));
    }

    public function testAllowsClearingAllRegisteredViews()
    {
        $this->response->addView('foo/bar', array())
                       ->addView('bar/baz', array());
        $this->response->clearViews();
        $views = $this->response->getViews();
        $this->assertTrue(empty($views));
    }

    public function testNoMetadataRegisteredByDefault()
    {
        $metadata = $this->response->getMetadata();
        $this->assertTrue(empty($metadata));
    }

    public function testAllowsRegisteringArbitraryMetadata()
    {
        $this->response->addMetadata('foo', 'bar');
        $this->assertContains('bar', $this->response->getMetadata('foo'));
    }

    public function testAppendsDataToExistingMetadataKeys()
    {
        $this->response->addMetadata('foo', 'bar')
                       ->addMetadata('foo', 'baz');
        $test = $this->response->getMetadata('foo');
        $this->assertEquals(array('bar', 'baz'), $test);
    }

    public function testPassingNoArgumentsToGetMetadataReturnsAllMetadata()
    {
        $this->response->addMetadata('foo', 'bar')
                       ->addMetadata('bar', 'baz');
        $expected = array('foo' => array('bar'), 'bar' => array('baz'));
        $test     = $this->response->getMetadata();
        $this->assertEquals($expected, $test);
    }

    public function testCanRemoveMetadataKeys()
    {
        $this->response->addMetadata('foo', 'bar');
        $this->response->removeMetadata('foo');
        $this->assertFalse($this->response->hasMetadata('foo'));
    }

    public function testSetMetadataOverwritesExistingMetadataValues()
    {
        $this->response->addMetadata('foo', 'bar');
        $this->response->setMetadata('foo', 'baz');
        $this->assertContains('baz', $this->response->getMetadata('foo'));
        $this->assertNotContains('bar', $this->response->getMetadata('foo'));
    }

    public function testCanClearAllMetadata()
    {
        $this->response->addMetadata('foo', 'bar')
                       ->addMetadata('bar', 'baz');
        $this->response->clearMetadata();
        $test = $this->response->getMetadata();
        $this->assertTrue(empty($test));
    }

    public function testLayoutIsNullByDefault()
    {
        $this->assertNull($this->response->getLayout());
    }

    public function testCanSetLayout()
    {
        $this->response->setLayout('layouts/site');
        $this->assertEquals('layouts/site', $this->response->getLayout());
    }

    public function testZendViewRendererIsDefaultRenderer()
    {
        $renderer = $this->response->getRenderer();
        $this->assertTrue($renderer instanceof Phly_Mvc_Response_Renderer_ZendView);
    }

    public function testMaySetAlternateRendererUsingStringName()
    {
        $original = $this->response->getRenderer();
        $this->response->setRenderer('Phly_Mvc_Response_Renderer_ZendView');
        $test     = $this->response->getRenderer();
        $this->assertNotSame($original, $test);
        $this->assertTrue($test instanceof Phly_Mvc_Response_Renderer_ZendView);
    }

    public function testMaySetAlternateRendererUsingConcreteClass()
    {
        $original = $this->response->getRenderer();
        $test     = new Phly_Mvc_Response_Renderer_ZendView();
        $this->response->setRenderer($test);
        $this->assertNotSame($original, $test);
    }
}
