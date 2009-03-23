<?php

class Phly_Mvc_PubSubProviderTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->pubSub     = new Phly_Mvc_PubSubProvider();
        $this->subscriber = false;
        $this->response   = false;
    }

    public function subscriber()
    {
        $this->subscriber = true;
    }

    public function callResponseSubscriber()
    {
        $this->pubSub->publish('mvc.response');
    }

    public function responseSubscriber()
    {
        $this->response = true;
    }

    public function testPublishingTopicSetsLastTopic()
    {
        $this->pubSub->subscribe('foo', $this, 'subscriber');
        $this->pubSub->publish('foo');
        $this->assertEquals('foo', $this->pubSub->getLastTopic());
    }

    public function testPublishingTopicShouldShortCircuitIfResponseTopicIsPublishedBySubscriber()
    {
        $this->pubSub->subscribe('foo', $this, 'callResponseSubscriber');
        $this->pubSub->subscribe('foo', $this, 'subscriber');
        $this->pubSub->subscribe('mvc.response', $this, 'responseSubscriber');
        $this->pubSub->publish('foo');
        $this->assertTrue($this->response);
        $this->assertFalse($this->subscriber);
    }
}
