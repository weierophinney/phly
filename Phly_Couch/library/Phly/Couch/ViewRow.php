<?php

class Phly_Couch_ViewRow extends Phly_Couch_Element
{
    /**
     * Id of the document that lead to emitting this view row.
     *
     * @var string|integer
     */
    protected $_id = null;

    /**
     * Emitted key of the view row.
     *
     * @var null|string|integer|array
     */
    protected $_key = null;

    /**
     * Emitted data of the view row.
     *
     * @var array
     */
    protected $_data = array();

    /**
     * Couch Database reference
     *
     * @var Phly_Couch
     */
    protected $_database;

    /**
     * Construct new View Result Row
     *
     * @param array      $data
     * @param Phly_Couch $database
     */
    public function __construct(array $data, $database=null)
    {
        $this->_data = $data['value'];
        $this->_key  = $data['key'];
        $this->_id   = $data['id'];

        if($database instanceof Phly_Couch) {
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

    /**
     * Check if a value exists in the view row result data.
     *
     * @param  string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->_data[$name]);
    }

    /**
     * Handle unset of the read only view row by exception.
     *
     * @param string $name
     * @throws Phly_Couch_Exception always
     */
    public function __unset($name)
    {
        throw new Phly_Couch_Exception("View Rows are read-only. Retrieve the corresponding document to be able to edit fields.");
    }

    /**
     * Handle setting of read only view row by exception.
     *
     * @param unknown_type $name
     * @param unknown_type $value
     */
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
    public function fetchDocument()
    {
        return $this->getDatabase()->docOpen($this->getId());
    }
}