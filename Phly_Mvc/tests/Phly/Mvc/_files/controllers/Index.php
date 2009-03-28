<?php

class PhlyTest_Controller_Index
{
    public function __construct(Phly_Mvc_Event $e)
    {
        $this->event = $e;
    }

    public function indexAction()
    {
        return 'default action triggered';
    }
}
