<?php

class Phly_Mvc_Dispatcher_IncludePathTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->autoloader = new Zend_Loader_Autoloader_Resource(array(
            'basePath' => dirname(__FILE__) . '../_files/',
            'namespace' => 'PhlyTest',
            'resourceTypes' => array(
                'controller' => array(
                    'path'      => 'controllers',
                    'namespace' => 'Controller',
                ),
            ),
        ));
        Zend_Loader_Autoloader::getInstance()->pushAutoloader($this->autoloader);

        $this->dispatcher = new Phly_Mvc_Dispatcher_IncludePath();
        $loader = $this->dispatcher->getPluginLoader();
        $loader->addPrefixPath('PhlyTest_Controller', dirname(__FILE__) . '/../_files/controllers');
    }

    public function tearDown()
    {
        Zend_Loader_Autoloader::getInstance()->removeAutoloader($this->autoloader);
    }

    public function testResolvingControllerClassShouldCreateWordsFromDotSeparatedStrings()
    {
        $class = $this->dispatcher->getControllerClass('foo.bar');
        $this->assertEquals('FooBar', $class);
    }

    public function testResolvingControllerClassShouldCreateWordsFromDashSeparatedStrings()
    {
        $class = $this->dispatcher->getControllerClass('foo-bar');
        $this->assertEquals('FooBar', $class);
    }

    public function testResolvingControllerClassShouldCreateWordsFromDotAndDashSeparatedStrings()
    {
        $class = $this->dispatcher->getControllerClass('foo-bar.baz');
        $this->assertEquals('FooBarBaz', $class);
    }

    public function testResolvingControllerClassShouldReplaceSlashesWithUnderscores()
    {
        $class = $this->dispatcher->getControllerClass('foo/bar');
        $this->assertEquals('Foo_Bar', $class);
    }

    public function testResolvingControllerClassShouldHandleMixtureOfDotsDashesAndSlashesProperly()
    {
        $class = $this->dispatcher->getControllerClass('foo.bar/baz-bat');
        $this->assertEquals('FooBar_BazBat', $class);
    }

    public function testResolvingActionMethodShouldCreateWordsFromDotSeparatedStrings()
    {
        $method = $this->dispatcher->getActionMethod('foo.bar');
        $this->assertEquals('FooBarAction', $method);
    }

    public function testResolvingActionMethodShouldCreateWordsFromDashSeparatedStrings()
    {
        $method = $this->dispatcher->getActionMethod('foo-bar');
        $this->assertEquals('FooBarAction', $method);
    }

    public function testResolvingActionMethodShouldCreateWordsFromDotAndDashSeparatedStrings()
    {
        $method = $this->dispatcher->getActionMethod('foo-bar.baz');
        $this->assertEquals('FooBarBazAction', $method);
    }

    public function testShouldLazyLoadPluginLoader()
    {
        $loader = $this->dispatcher->getPluginLoader();
        $this->assertTrue($loader instanceof Zend_Loader_PluginLoader);
    }

    public function testShouldAllowSpecifyingCustomPluginLoader()
    {
        $custom = new Zend_Loader_PluginLoader();
        $this->dispatcher->setPluginLoader($custom);
        $this->assertSame($custom, $this->dispatcher->getPluginLoader());
    }

    public function testLoadClassShouldResolveShortControllerNameToClass()
    {
        $class = $this->dispatcher->loadClass('index');
        $this->assertEquals('PhlyTest_Controller_Index', $class);
    }

    public function testDispatchShouldExecuteDefaultControllerAndActionInAbsenceOfValues()
    {
        $e = new Phly_Mvc_Event();
        $return = $this->dispatcher->dispatch($e);
        $this->assertEquals('default action triggered', $return);
    }

    public function testDispatchShouldUseControllerAndActionFromEventWhenPresent()
    {
        $e = new Phly_Mvc_Event();
        $e->controller = 'foo.bar';
        $e->action     = 'baz.bat';
        $return = $this->dispatcher->dispatch($e);
        $this->assertEquals('PhlyTest_Controller_FooBar::bazBatAction()', $return);
    }
}
