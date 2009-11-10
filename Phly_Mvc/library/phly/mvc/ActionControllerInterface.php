<?php
namespace phly\mvc;

interface ActionControllerInterface
{
    public function __invoke(EventInterface $e);

    public function setHelperBroker(action\HelperBrokerInterface $broker);
    public function getHelperBroker();
}
