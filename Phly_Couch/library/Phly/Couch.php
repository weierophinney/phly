<?php
require_once 'Zend/Json.php';

class Phly_Couch
{
    /**
     * @var string Database on which operations are performed
     */
    protected $_db;

    /**
     * @var Phly_Couch_Connection
     */
    protected $_connection;

    /**
     * Constructor
     *
     * @param  null|string|array|Zend_Config $info Database name, or array/config of options
     * @return void
     */
    public function __construct($options = null)
    {
        if (null !== $options) {
            if (is_array($options)) {
                $this->setOptions($options);
            } elseif ($options instanceof Zend_Config) {
                $this->setConfig($options);
            } elseif (is_string($info)) {
                $this->setDb($options);
            }
        }
    }

    /**
     * Set connection options
     *
     * @param  array $options
     * @return Phly_Couch
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    /**
     * Set connection options from Zend_Config object
     *
     * @param  Zend_Config $config
     * @return Phly_Couch
     */
    public function setConfig(Zend_Config $config)
    {
        return $this->setOptions($config->toArray());
    }

    /**
     * Set Database on which to perform operations
     *
     * @param  string $db
     * @return Phly_Couch
     * @throws Phly_Couch_Exception for invalid DB name
     */
    public function setDb($db)
    {
        if (!preg_match('/^[a-z][a-z0-9_$()+-\/]+$/', $db)) {
            require_once 'Phly/Couch/Exception.php';
            throw new Phly_Couch_Exception(sprintf('Invalid database specified: "%s"', htmlentities($db)));
        }
        $this->_db = $db;
        return $this;
    }

    /**
     * Retrieve current database name
     *
     * @return string|null
     */
    public function getDb()
    {
        if($this->_db === null) {
            throw new Phly_Couch_Exception("No Database was given!");
        }

        return $this->_db;
    }

    /**
     * Set a connection for this database
     *
     * @param Phly_Couch_Connection|array $connection
     */
    public function setConnection($connection)
    {
        if($connection instanceof Phly_Couch_Connection) {
            $this->_connection = $connection;
        } else if(is_array($connection)) {
            $this->_connection = new Phly_Couch_Connection($connection);
        } else {
            throw new Phly_Couch_Exception("Invalid connectin data given for database!");
        }
    }

    /**
     * Get connection of this database
     *
     * @throws Phly_Couch_Exception
     * @return Phly_Couch_Connection
     */
    public function getConnection()
    {
        if($this->_connection === null) {
            $this->_connection = Phly_Couch_Connection::getDefaultConnection();
        }
        return $this->_connection;
    }

    // API METHODS

    // Database API methods

    /**
     * Compact a database
     *
     * @param  null|string $db
     * @r-eturn Phly_Couch_Result
     * @throws Phly_Couch_Exception when fails or no database specified
     */
    public function compact()
    {
        $db = $this->getDb();
        $response = $this->_prepareAndSend($db . '/_compact', 'POST');
        $status = $response->getStatus();
        if (202 !== $status) {
            require_once 'Phly/Couch/Exception.php';
            throw new Phly_Couch_Exception(sprintf('Failed compacting database "%s": received response code "%s"', $db, (string) $response->getStatus()));
        }
        require_once 'Phly/Couch/Result.php';
        return new Phly_Couch_Result($response);
    }

    /**
     * Get database info
     *
     * @param  string $db
     * @return Phly_Couch_Result
     * @throws Phly_Couch_Exception when fails
     */
    public function info()
    {
        $db = $this->getDb();

        $response = $this->_prepareAndSend($db, 'GET');
        if (!$response->isSuccessful()) {
            require_once 'Phly/Couch/Exception.php';
            throw new Phly_Couch_Exception(sprintf('Failed querying database "%s"; received response code "%s"', $db, (string) $response->getStatus()));
        }
        require_once 'Phly/Couch/Result.php';
        return new Phly_Couch_Result($response);
    }

    // Document API methods

    /**
     * Retrieve all documents for a give database
     *
     * @param  null|array $options Query options
     * @return Phly_Couch_DocumentSet
     * @throws Phly_Couch_Exception on failure or bad db
     */
    public function allDocs(array $options = null)
    {
        return $this->getView('_all_docs');
    }

    /**
     * Get rows from a view
     *
     * @param  string $viewName including the design document name
     * @param  array  $queryParams
     * @return Phly_Couch_ViewSet
     */
    public function getView($viewName, array $queryParams=array())
    {
        $view = new Phly_Couch_View($viewName, $this);
        if(count($queryParams) > 0) {
            $view->query($queryParams);
        }

        return $view;
    }

    public function getTemporaryView($map, $reduce=null, array $queryParams=array())
    {
        $view = new Phly_Couch_TemporaryView($map, $reduce, $this);
        if(count($queryParams) > 0) {
            $view->query($queryParams);
        }

        return $view;
    }

    /**
     * Open a document
     *
     * @param  string $id
     * @param  null|array $options
     * @return Phly_Couch_Document
     * @throws Phly_Couch_Exception on failure
     * @todo   handle unsuccessful call
     */
    public function docOpen($id, array $options = null)
    {
        $db = $this->getDb($db);

        $response = $this->_prepareAndSend($db . '/' . $id, 'GET', $options);

        // TODO: What about empty result?

        require_once 'Phly/Couch/Document.php';
        if(strpos($id, '_design/') === 0) {
            return new Phly_Couch_DesignDocument($response->getBody(), $this);
        } else {
            return new Phly_Couch_Document($response->getBody(), $this);
        }
    }

    /**
     * Save a document
     *
     * @param  string|array|Phly_Couch_Document $document
     * @param  null|string $id
     * @param  null|string $db
     * @return Phly_Couch_Result
     * @throws Phly_Couch_Exception on failure
     */
    public function docSave($document, $id = null, $db = null)
    {
        $db     = $this->getDb($db);
        $path   = $db . '/';
        $method = 'POST';
        if (null !== $id) {
            $method = 'PUT';
            $path  .= $id;
        }

        if (is_string($document)) {
            if ('{' != substr($document, 0, 1)) {
                require_once 'Phly/Couch/Exception.php';
                throw new Phly_Couch_Exception('Invalid document provided');
            }
            require_once 'Phly/Couch/Document.php';
            $document = new Phly_Couch_Document($document);
        } elseif (is_array($document)) {
            require_once 'Phly/Couch/Document.php';
            $document = new Phly_Couch_Document($document);
        } elseif (!$document instanceof Phly_Couch_Document) {
            require_once 'Phly/Couch/Exception.php';
            throw new Phly_Couch_Exception('Invalid document provided');
        }

        if (null !== $document->getRevision()) {
            if ((null === $id) && (null === ($id = $document->getId()))) {
                require_once 'Phly/Couch/Exception.php';
                throw new Phly_Couch_Exception('Document updates require a document id; none provided');
            }
            $method = 'PUT';
        }

        try {
            $this->getConnection()->getHttpClient()->setRawData($document->toJson());
            $response = $this->_prepareAndSend($path, $method);
            $status   = $response->getStatus();
        } catch(Phly_Couch_Connection_Response_Exception $e) {
            $status = $e->getHttpResponse()->getStatus();
        }

        switch ($status) {
            case 412:
                require_once 'Phly/Couch/Exception.php';
                throw new Phly_Couch_Exception(sprintf('Document with the specified document id "%s" already exists', $id));
                break;
            case 409:
                require_once 'Phly/Couch/Exception.php';
                throw new Phly_Couch_Exception(sprintf('Document with document id "%s" does not contain the revision "%s"', $id, $data['_rev']));
                break;
            case 201:
            default:
                require_once 'Phly/Couch/Result.php';
                return new Phly_Couch_Result($response);
                break;
        }

        // TODO: Make changes to the document class, revision and key for example
    }

    /**
     * Remove a document
     *
     * @param  string $id
     * @param  array $options
     * @return Phly_Couch_Result
     * @throws Phly_Couch_Exception on failed call
     */
    public function docRemove($id, array $options = null)
    {
        $db = $this->getDb();

        $response = $this->_prepareAndSend($db . '/' . $id, 'DELETE', $options);
        if (!$response->isSuccessful()) {
            require_once 'Phly/Couch/Exception.php';
            throw new Phly_Couch_Exception(sprintf('Failed deleting document with id "%s" from database "%s"; received response code "%s"', $id, $db, (string) $response->getStatus()));
        }

        require_once 'Phly/Couch/Result.php';
        return new Phly_Couch_Result($response);
    }

    /**
     * Bulk save many documents at once
     *
     * @param  array|Phly_Couch_DocumentSet $documents
     * @param  array $options
     * @return Phly_Couch_Result
     * @throws Phly_Couch_Exception on failed save
     */
    public function docBulkSave($documents, array $options = null)
    {
        $db = $this->getDb($db);

        if (is_array($documents)) {
            require_once 'Phly/Couch/DocumentSet.php';
            $documents = new Phly_Couch_DocumentSet($documents);
        } elseif (!$documents instanceof Phly_Couch_DocumentSet) {
            require_once 'Phly/Couch/Exception.php';
            throw new Phly_Couch_Exception('Invalid document set provided to bulk save operation');
        }

        $this->getConnection()->getHttpClient()->setRawData($documents->toJson());
        $response = $this->_prepareAndSend($db . '/_bulk_docs', 'POST', $options);
        if (!$response->isSuccessful()) {
            require_once 'Phly/Couch/Exception.php';
            throw new Phly_Couch_Exception(sprintf('Failed deleting document with id "%s" from database "%s"; received response code "%s"', $id, $db, (string) $response->getStatus()));
        }

        require_once 'Phly/Couch/Result.php';
        return new Phly_Couch_Result($response);
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
        return $this->getConnection()->send($path, $method, $queryParams);
    }
}
