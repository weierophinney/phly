<?php

class Phly_Couch_Element
{
    /**
     * Couch Database
     *
     * @var Phly_Couch
     */
    protected $_database = null;

    /**
     * Set a database connection for this CouchDB element
     *
     * @param Phly_Couch $database
     * @return Phly_Couch_Element
     */
    public function setDatabase(Phly_Couch $database)
    {
        $this->_database = $database;
        return $this;
    }

    /**
     * Get the current database connection of this CouchDB element
     *
     * @return Phly_Couch
     */
    public function getDatabase()
    {
        if($this->_database === null) {
            throw new Phly_Couch_Exception("No database connection issset in '".get_class($this)."'");
        }
        return $this->_database;
    }
}