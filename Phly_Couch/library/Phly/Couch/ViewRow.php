<?php

class Phly_Couch_ViewRow
{
    protected $_id = null;

    protected $_key = null;

    protected $_data = array();

    protected $_database;

    public function __construct($data, $database)
    {
        $this->_data = $data['value'];
        $this->_key  = $data['key'];
        $this->_id   = $data['id'];

        $this->_database = $database;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function getKey()
    {
        return $this->_key;
    }

    public function getData()
    {
        return $this->_data;
    }

    public function __get($name)
    {
        return $this->_data[$name];
    }

    public function getDocument()
    {
        return $this->_database->docOpen($this->getId());
    }
}