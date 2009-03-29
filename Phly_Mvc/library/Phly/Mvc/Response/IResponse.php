<?php

interface Phly_Mvc_Response_IResponse
{
    public function send(Phly_Mvc_Event $e);

    public function setEvent(Phly_Mvc_Event $e);

    public function getEvent();

    public function addView($name, $vars = null);

    public function getViews();

    public function removeView($name);

    public function clearViews();

    public function addMetadata($name, $value);

    public function getMetadata($name = null);

    public function removeMetadata($name);

    public function clearMetadata($name);

    public function setLayout($name);

    public function getLayout();

    public function setRenderer($rendererOrClass);

    public function getRenderer();
}
