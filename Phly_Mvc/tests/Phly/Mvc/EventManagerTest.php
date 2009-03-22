<?php

class Phly_Mvc_EventManagerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->eventManager = new Phly_Mvc_EventManager();
    }

    /**
     * @expectedException Phly_Mvc_Exception
     */
    public function testConstructorShouldThrowExceptionForInvalidArgument()
    {
        $em = new Phly_Mvc_EventManager(new stdClass());
    }

    public function testConstructorShouldRegisterAutoloader()
    {
        $autoloader = $this->eventManager->getAutoloader();
        $this->assertTrue($autoloader instanceof Zend_Loader_Autoloader);
    }

    public function testConstructorShouldRegisterPhlyNamespaceWithAutoloader()
    {
        $namespaces = $this->eventManager->getAutoloader()->getRegisteredNamespaces();
        $this->assertContains('Phly_', $namespaces);
    }

    public function testEnvironmentShouldBeNullByDefault()
    {
        $this->assertNull($this->eventManager->getEnvironment());
    }

    public function testPassingEnvironmentToConstructorShouldSetEnvironment()
    {
        $em = new Phly_Mvc_EventManager(array(), 'testing');
        $this->assertEquals('testing', $em->getEnvironment());
    }

    public function testOptionsArrayPassedToConstructorShouldRecordOptions()
    {
        $options = array('foo' => 'bar');
        $em = new Phly_Mvc_EventManager($options);
        $test = $em->getOptions();
        $this->assertSame($options, $test);
    }

    public function testZendConfigPassedToConstructorShouldRecordOptions()
    {
        $options = array('foo' => 'bar');
        $config  = new Zend_Config($options);
        $em = new Phly_Mvc_EventManager($config);
        $test = $em->getOptions();
        $this->assertSame($options, $test);
    }

    public function testIniZendConfigFilePathPassedToConstructorShouldRecordOptions()
    {
        $em = new Phly_Mvc_EventManager(dirname(__FILE__) . '/_files/config.ini', 'testing');
        $test = $em->getOptions();
        $this->assertEquals(array('foo' => 'bar'), $test);
    }

    public function testXmlZendConfigFilePathPassedToConstructorShouldRecordOptions()
    {
        $em = new Phly_Mvc_EventManager(dirname(__FILE__) . '/_files/config.xml', 'testing');
        $test = $em->getOptions();
        $this->assertEquals(array('foo' => 'bar'), $test);
    }

    public function testIncZendConfigFilePathPassedToConstructorShouldRecordOptions()
    {
        $em = new Phly_Mvc_EventManager(dirname(__FILE__) . '/_files/config.inc');
        $test = $em->getOptions();
        $this->assertEquals(array('foo' => 'bar'), $test);
    }

    public function testPhpZendConfigFilePathPassedToConstructorShouldRecordOptions()
    {
        $em = new Phly_Mvc_EventManager(dirname(__FILE__) . '/_files/config.php');
        $test = $em->getOptions();
        $this->assertEquals(array('foo' => 'bar'), $test);
    }

    public function testEventObjectLazyLoadsByDefault()
    {
        $event = $this->eventManager->getEvent();
        $this->assertTrue($event instanceof Phly_Mvc_Event);
    }
}
