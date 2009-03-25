<?php
class Phly_Mvc_Event extends ArrayObject
{
    public function __construct($array = null)
    {
        if (null === $array) {
            $array = array();
        }
        parent::__construct($array, ArrayObject::ARRAY_AS_PROPS);
    }
}
