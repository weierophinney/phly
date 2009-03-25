<?php

class Phly_Mvc_Request_RequestTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->request = new Phly_Mvc_Request_Request();
    }

    public function testUsesServerSuperGlobalByDefault()
    {
        $this->assertSame($_SERVER, $this->request->getServer());
    }

    public function testServerSuperGlobalIsNotOverwritten()
    {
        $this->request->getServer();
        $this->assertFalse(empty($_SERVER));
    }

    public function testUsesEnvSuperGlobalByDefault()
    {
        $this->assertSame($_ENV, $this->request->getEnv());
    }

    public function testEnvSuperGlobalIsNotOverwritten()
    {
        if (empty($_ENV)) {
            $this->markTestSkipped('ENV is empty at start of test');
        }
        $this->request->getEnv();
        $this->assertFalse(empty($_ENV));
    }

    public function testCanSpecifyDataForServer()
    {
        $data = array('foo' => 'bar');
        $this->request->setServer($data);
        $this->assertSame($data, $this->request->getServer());
    }

    public function testCanSpecifyDataForEnv()
    {
        $data = array('foo' => 'bar');
        $this->request->setEnv($data);
        $this->assertSame($data, $this->request->getEnv());
    }

    public function testCanRetrieveIndividualItemsFromServer()
    {
        $data = array('foo' => 'bar');
        $this->request->setServer($data);
        $this->assertEquals('bar', $this->request->getServer('foo'));
    }

    public function testCanRetrieveIndividualItemsFromEnv()
    {
        $data = array('foo' => 'bar');
        $this->request->setEnv($data);
        $this->assertEquals('bar', $this->request->getEnv('foo'));
    }

    public function testRetrievingIndividualItemFromServerUsesPassedDefaultIfItemDoesNotExist()
    {
        $this->assertFalse($this->request->getServer('foo', false));
    }

    public function testRetrievingIndividualItemFromEnvUsesPassedDefaultIfItemDoesNotExist()
    {
        $this->assertFalse($this->request->getEnv('foo', false));
    }

    public function testSetOptionsProxiesToInternalMutators()
    {
        $data = array(
            'server' => array('foo' => 'bar')
        );
        $this->request->setOptions($data);
        $this->assertSame($data['server'], $this->request->getServer());
    }

    public function testConstructorPassesOptionsToSetOptions()
    {
        $data = array(
            'server' => array('foo' => 'bar')
        );
        $request = new Phly_Mvc_Request_Request($data);
        $this->assertSame($data['server'], $request->getServer());
    }
}
