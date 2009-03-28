<?php
class PhlyTest_Controller_FooBar
{
    public function __construct(Phly_Mvc_Event $e)
    {
        $this->event = $e;
    }

    public function bazBatAction()
    {
        return __METHOD__ . '()';
    }
}
