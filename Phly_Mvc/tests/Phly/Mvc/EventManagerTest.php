<?php

class Phly_Mvc_EventManagerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->eventManager = new Phly_Mvc_EventManager();

        $this->request  = false;
        $this->routing  = false;
        $this->action   = false;
        $this->response = false;
        $this->error    = false;
        $this->order    = array();
        $this->args     = array();
    }

    public function prepareProvider()
    {
        $pubSub = new Phly_Mvc_PubSubProvider();
        $pubSub->subscribe('mvc.request', $this, 'mvcRequest');
        $pubSub->subscribe('mvc.routing', $this, 'mvcRouting');
        $pubSub->subscribe('mvc.action', $this, 'mvcAction');
        $pubSub->subscribe('mvc.response', $this, 'mvcResponse');
        $pubSub->subscribe('mvc.error', $this, 'mvcError');
        $this->eventManager->setPubSubProvider($pubSub);
    }

    public function mvcRequest()
    {
        $this->request = true;
        $this->order[] = 'mvc.request';
        $this->args['mvc.request'] = func_get_args();
    }

    public function mvcRouting()
    {
        $this->routing = true;
        $this->order[] = 'mvc.routing';
        $this->args['mvc.routing'] = func_get_args();
    }

    public function mvcAction()
    {
        $this->action = true;
        $this->order[] = 'mvc.action';
        $this->args['mvc.action'] = func_get_args();
    }

    public function mvcResponse()
    {
        $this->response = true;
        $this->order[] = 'mvc.response';
        $this->args['mvc.response'] = func_get_args();
    }

    public function mvcError()
    {
        $this->error = true;
        $this->order[] = 'mvc.error';
        $this->args['mvc.error'] = func_get_args();
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

    /**
     * @expectedException Phly_Mvc_Exception
     */
    public function testPassingInvalidConfigStringToConstructorShouldThrowException()
    {
        $em = new Phly_Mvc_EventManager(dirname(__FILE__) . '/_files/nonexistent.badSuffix');
    }

    public function testEventObjectLazyLoadsByDefault()
    {
        $event = $this->eventManager->getEvent();
        $this->assertTrue($event instanceof Phly_Mvc_Event);
    }

    public function testDefaultEventsAreRegistered()
    {
        $events = $this->eventManager->getTopics();
        $this->assertEquals(array(
            'mvc.request',
            'mvc.routing',
            'mvc.action',
            'mvc.response',
            'mvc.error',
        ), $events);
    }

    public function testRegisteredEventsShouldMatchPubSubTopics()
    {
        $events = $this->eventManager->getTopics();
        $topics = $this->eventManager->getPubSubProvider()->getTopics();
        $this->assertSame($topics, $events);
    }

    public function testHandleShouldNotTriggerErrorEventInAbsenceOfException()
    {
        $this->prepareProvider();
        $this->eventManager->handle();
        $this->assertFalse($this->error);
    }

    public function testHandleShouldTriggerEventsInOrder()
    {
        $events = $this->eventManager->getTopics();
        array_pop($events);
        $this->prepareProvider();
        $this->eventManager->handle();
        $this->assertEquals($events, $this->order);
    }

    public function testHandleShouldPassEventWhenPublishing()
    {
        $events = $this->eventManager->getTopics();
        $event  = $this->eventManager->getEvent();
        array_pop($events);
        $this->prepareProvider();
        $this->eventManager->handle();
        foreach ($events as $e) {
            $this->assertContains($event, $this->args[$e]);
        }
    }
}
