<?php

class Phly_Couch_View implements Iterator, Countable
{
    protected $_viewName;

    // TODO: This is really the Document Id, integrate both
    protected $_internalViewName;

    protected $_fetchedView = false;

    protected $_rows = array();

    protected $_count = null;

    protected $_database = null;

    public function __construct($viewName, Phly_Couch $database)
    {
        // TODO: A view is a special document, that is, use the document parent to save view information.
        // TODO: It sucks to have View iteration and view editing in the same class, @see getViewDocument()
        $this->_viewName = substr("_design/", "", $viewName);

        if($viewName !== "_all_docs") {
            if(!strpos($viewName, "_design/")) {
                $viewName = "_design/".$viewName;
            }
        }
        $this->_internalViewName = $viewName;

        $this->_database = $database;
    }

    /**
     * Return document of the view
     *
     * @return Phly_Couch_Document
     */
    public function getViewDocument()
    {
        return $this->_database->docOpen($this->_internalViewName);
    }

    /**
     * Query View for results. Use Params for sorting and other options.
     *
     * @param array $params
     * @throws Phly_Couch_Exception
     * @return Phly_Couch_View
     */
    public function query($params)
    {
        // TODO: Connection Class can do most of this already
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
        return $this;
    }

    /**
     * Couch Views return a key field that is primarily designed for sorting by.
     *
     * @return boolean
     */
    public function sortByKey()
    {
        // TODO: Sort View Rows by Couch returned key names
    }

    /**
     * Return the complete View Json Response as an array
     *
     * @return array
     */
    public function toArray()
    {
        if($this->_fetchedView === false) {
            $this->query(array());
        }

        return array('total_rows' => $this->_count, 'offset' => $this->_offset, 'rows' => $this->_rows);
    }

    /**
     * Return number of view results
     *
     * @return int
     */
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