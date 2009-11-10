<?php
namespace phly\mvc\action;

interface HelperBrokerInterface
{
    public function hasHelper($helper);
    public function getHelper($helper);
    public function addHelper($helper);
}
