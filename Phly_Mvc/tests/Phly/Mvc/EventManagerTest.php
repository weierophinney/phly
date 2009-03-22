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
        $this->markTestIncomplete();
    }

    public function testXmlZendConfigFilePathPassedToConstructorShouldRecordOptions()
    {
        $this->markTestIncomplete();
    }

    public function testIncZendConfigFilePathPassedToConstructorShouldRecordOptions()
    {
        $this->markTestIncomplete();
    }

    public function testPhpZendConfigFilePathPassedToConstructorShouldRecordOptions()
    {
        $this->markTestIncomplete();
    }
}
