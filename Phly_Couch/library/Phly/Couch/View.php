<?php

class Phly_Couch_View extends Phly_Couch_Document implements Iterator, Countable
{
    protected $_viewName;

    protected $_internalViewName;

    protected $_fetchedView = false;

    protected $_rows = array();

    protected $_count = null;

    protected $_database = null;

    public function __construct($viewName, Phly_Couch $database)
    {
        // TODO: A view is a special document, that is, use the document parent to save view information.
        $this->_viewName = substr("_design/", "", $viewName);

        if($viewName !== "_all_docs") {
            if(!strpos($viewName, "_design/")) {
                $viewName = "_design/".$viewName;
            }
        }
        $this->_internalViewName = $viewName;

        $this->_database = $database;
    }

    public function query($params)
    {
        $response = $this->_prepareAndSend($this->_database->getDb() . '/' . $this->_internalViewName, 'GET', $queryParams);
        if (!$response->isSuccessful()) {
            require_once 'Phly/Couch/Exception.php';
            throw new Phly_Couch_Exception(sprintf('Failed querying database "%s"; received response code "%s"', $db, (string) $response->getStatus()));
        }

        $body = Zend_Json::decode($response->getBody());

        if(isset($body['total_rows']) && isset($body['rows']) && isset($body['offset'])) {
            $this->_count = $body['total_rows'];
            $this->_rows = $body['rows'];
            $this->_offset = $body['offset'];
            $this->_fetchedView = true;
        } else {
            throw new Phly_Couch_Exception("CouchDb Response is not a valid view result.");
        }
    }

    public function toArray()
    {
        if($this->_fetchedView === false) {
            $this->query(array());
        }

        return array('total_rows' => $this->_count, 'offset' => $this->_offset, 'rows' => $this->_rows);
    }

    public function count()
    {
        if($this->_fetchedView === false) {
            $this->query(array());
        }

        return $this->_count;
    }

    public function current()
    {
        if($this->_fetchedView === false) {
            $this->query(array());
        }

        $doc = current($this->_rows);
        if($doc == false) {
            return false;
        } else {
            // TODO: Maybe check for viewrow instance and replace instead of creating new all over?
            return new Phly_Couch_ViewRow($doc, $this->_database);
        }
    }

    public function key()
    {
        if($this->_fetchedView === false) {
            $this->query(array());
        }

        return key($this->_rows);
    }

    public function next()
    {
        if($this->_fetchedView === false) {
            $this->query(array());
        }

        return next($this->_rows);
    }

    public function rewind()
    {
        if($this->_fetchedView === false) {
            $this->query(array());
        }

        return reset($this->_rows);
    }

    public function valid()
    {
        if($this->_fetchedView === false) {
            $this->query(array());
        }

        return $this->current() !== false;
    }

    /**
     * Prepare the URI and send the request
     *
     * @param  string $path
     * @param  string $method
     * @param  null|array $queryParams
     * @return Zend_Http_Response
     */
    protected function _prepareAndSend($path, $method, array $queryParams = null)
    {
        return $this->_database->getConnection()->send($path, $method, $queryParams);
    }
}