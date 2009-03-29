<?php

class Phly_Mvc_Response_Http extends Phly_Mvc_Response_Response
{
    public function send(Phly_Mvc_Event $e)
    {
        $this->setEvent($e);
        $this->renderMetadata();

        if ($this->isRedirect()) {
            return;
        }
    }

    public function renderMetadata()
    {
        // Check for specific metadata like response code, send, and remove.
        // All others, act as if they are headers -- key/value pairs.
    }
}
