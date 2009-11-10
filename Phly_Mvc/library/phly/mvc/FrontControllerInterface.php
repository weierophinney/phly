<?php
namespace phly\mvc;
use phly\pubsub\Provider as Provider;

interface FrontControllerInterface
{
    public function setPubSub(Provider $pubsub);
    public function getPubSub();
    public function handle(EventInterface $e = null);
}
