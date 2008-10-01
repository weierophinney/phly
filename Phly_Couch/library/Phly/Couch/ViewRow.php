<?php

class Phly_Couch_ViewRow
{
    protected $_id = null;

    protected $_key = null;

    protected $_data = array();

    protected $_database;

    public function __construct($data, Phly_Couch $database=null)
    {
        $this->_data = $data['value'];
        $this->_key  = $data['key'];
        $this->_id   = $data['id'];

        if($database !== null) {
            $this->setDatabase($database);
        }
    }

    /**
     * Get Id that is distributed with all view rows. It belongs to its underlying document.
     *
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Get the sortable key that is distributed with all view rows.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->_key;
    }

    /**
     * Get value data from the view row.
     *
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Access single values of the view row data.
     *
     * @param string $name
     * @return string|integer|array
     */
    public function __get($name)
    {
        return $this->_data[$name];
    }

    public function __isset($name)
    {
        return isset($this->_data[$name]);
    }

    public function __unset($name)
    {
        throw new Phly_Couch_Exception("View Rows are read-only. Retrieve the corresponding document to be able to edit fields.");
    }

    public function __set($name, $value)
    {
        throw new Phly_Couch_Exception("View Rows are read-only. Retrieve the corresponding document to be able to edit fields.");
    }

    /**
     * Return corresponding document to this view row
     *
     * @throws Phly_Couch_Exception
     * @return Phly_Couch_Document
     */
    public function getDocument()
    {
        return $this->getDatabase()->docOpen($this->getId());
    }

    /**
     * Set Database this View Row belongs to.
     *
     * @param Phly_Couch $database
     * @return Phly_Couch_ViewRow
     */
    public function setDatabase(Phly_Couch $database)
    {
        $this->_database = $database;
        return $this;
    }

    public function getDatabase()
    {
        if($this->_database === null) {
            throw new Phly_Couch_Exception("ViewRow is not associated with a database.");
        }
        return $this->_database;
    }
}