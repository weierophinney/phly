<?php
require_once 'Zend/Json.php';

class Phly_Couch
{
    /**
     * @var Zend_Http_Client HTTP client used for accessing server
     */
    protected $_client;

    /**
     * @var string Database on which operations are performed
     */
    protected $_db;

    /**
     * @var Zend_Http_Client Default HTTP client to use for CouchDB access
     */
    protected static $_defaultClient;

    /**
     * @var string Database host; defaults to 127.0.0.1
     */
    protected $_host = '127.0.0.1';

    /**
     * @var int Database host port; defaults to 5984
     */
    protected $_port = 5984;

    /**
     * Constructor
     * 
     * @param  null|string|array|Zend_Config $info Database name, or array/config of options
     * @return void
     */
    public function __construct($info = null) 
    {
        if (null !== $info) {
            if (is_array($info)) {
                $this->setOptions($info);
            } elseif ($info instanceof Zend_Config) {
                $this->setConfig($info);
            } elseif (is_string($info)) {
                $this->setDb($info);
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
        return $this->_db;
    }

    /**
     * Set database host
     * 
     * @param  string $host 
     * @return Phly_Couch
     */
    public function setHost($host)
    {
        $this->_host = $host;
        return $this;
    }

    /**
     * Retrieve database host
     * 
     * @return string
     */
    public function getHost()
    {
        return $this->_host;
    }
    
    /**
     * Set database host port
     * 
     * @param  int $port 
     * @return Phly_Couch
     */
    public function setPort($port)
    {
        $this->_port = (int) $port;
        return $this;
    }

    /**
     * Retrieve database host port
     * 
     * @return int
     */
    public function getPort()
    {
        return $this->_port;
    }

    // HTTP client

    /**
     * Set HTTP client
     * 
     * @param  Zend_Http_Client $client 
     * @return Phly_Couch
     */
    public function setHttpClient(Zend_Http_Client $client)
    {
        $this->_client = $client;
        return $this;
    }

    /**
     * Set default HTTP client
     * 
     * @param  Zend_Http_Client $client 
     * @return void
     */
    public static function setDefaultHttpClient(Zend_Http_Client $client)
    {
        self::$_defaultClient = $client;
    }

    /**
     * Get current HTTP client
     * 
     * @return Zend_Http_Client
     */
    public function getHttpClient()
    {
        if (null === $this->_client) {
            $client = self::getDefaultHttpClient();
            if (null === $client) {
                require_once 'Zend/Http/Client.php';
                $client = new Zend_Http_Client;
            }
            $this->setHttpClient($client);
        }
        return $this->_client;
    }

    /**
     * Retrieve default HTTP client
     * 
     * @return null|Zend_Http_Client
     */
    public static function getDefaultHttpClient()
    {
        return self::$_defaultClient;
    }

    // API METHODS

    // Server API methods

    /**
     * Get server information
     * 
     * @return Phly_Couch_Result
     */
    public function serverInfo()
    {
        $this->_prepareUri('');
        $response = $this->_prepareAndSend('', 'GET');
        if (!$response->isSuccessful()) {
            require_once 'Phly/Couch/Exception.php';
            throw new Phly_Couch_Exception(sprintf('Failed retrieving server information; received response code "%s"', (string) $response->getStatus()));
        }

        require_once 'Phly/Couch/Result.php';
        return new Phly_Couch_Result($response);
    }

    /**
     * Get list of all databases
     * 
     * @return Phly_Couch_Result
     */
    public function allDbs()
    {
        $response = $this->_prepareAndSend('_all_dbs', 'GET');
        if (!$response->isSuccessful()) {
            require_once 'Phly/Couch/Exception.php';
            throw new Phly_Couch_Exception(sprintf('Failed retrieving database list; received response code "%s"', (string) $response->getStatus()));
        }
        require_once 'Phly/Couch/Result.php';
        return new Phly_Couch_Result($response);
    }

    // Database API methods

    /**
     * Compact a database
     * 
     * @param  null|string $db 
     * @return Phly_Couch_Result
     * @throws Phly_Couch_Exception when fails or no database specified
     */
    public function dbCompact($db = null)
    {
        $db = $this->_verifyDb($db);
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
     * Create database
     * 
     * @param  string $db 
     * @return Phly_Couch_Result
     * @throws Phly_Couch_Exception when fails or invalid database name
     */
    public function dbCreate($db = null)
    {
        $db = $this->_verifyDb($db);
        $response = $this->_prepareAndSend($db, 'PUT');
        if (!$response->isSuccessful()) {
            require_once 'Phly/Couch/Exception.php';
            throw new Phly_Couch_Exception(sprintf('Failed creating database "%s"; received response code "%s"', $db, (string) $response->getStatus()));
        }
        require_once 'Phly/Couch/Result.php';
        return new Phly_Couch_Result($response);
    }

    /**
     * Drop database
     * 
     * @param  string $db 
     * @return Phly_Couch_Result
     * @throws Phly_Couch_Exception when fails
     */
    public function dbDrop($db = null)
    {
        $db = $this->_verifyDb($db);
        $response = $this->_prepareAndSend($db, 'DELETE');
        if (!$response->isSuccessful()) {
            require_once 'Phly/Couch/Exception.php';
            throw new Phly_Couch_Exception(sprintf('Failed dropping database "%s"; received response code "%s"', $db, (string) $response->getStatus()));
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
    public function dbInfo($db = null)
    {
        $db = $this->_verifyDb($db);
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
        $db = null;
        if (is_array($options) && array_key_exists('db', $options)) {
            $db = $options['db'];
            unset($options['db']);
        }
        $db = $this->_verifyDb($db);

        $response = $this->_prepareAndSend($db . '/_all_docs', 'GET', $options);
        if (!$response->isSuccessful()) {
            require_once 'Phly/Couch/Exception.php';
            throw new Phly_Couch_Exception(sprintf('Failed querying database "%s"; received response code "%s"', $db, (string) $response->getStatus()));
        }

        require_once 'Phly/Couch/DocumentSet.php';
        return new Phly_Couch_DocumentSet($response->getBody());
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
        $db = null;
        if (is_array($options) && array_key_exists('db', $options)) {
            $db = $options['db'];
            unset($options['db']);
        }
        $db = $this->_verifyDb($db);

        $response = $this->_prepareAndSend($db . '/' . $id, 'GET', $options);

        require_once 'Phly/Couch/Document.php';
        return new Phly_Couch_Document($response->getBody());
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
        $db     = $this->_verifyDb($db);
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
        $this->getHttpClient()->setRawData($document->toJson());
        $response = $this->_prepareAndSend($path, $method);
        $status   = $response->getStatus();
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
        $db = null;
        if (is_array($options) && array_key_exists('db', $options)) {
            $db = $options['db'];
            unset($options['db']);
        }
        $db = $this->_verifyDb($db);

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
        $db = null;
        if (is_array($options) && array_key_exists('db', $options)) {
            $db = $options['db'];
            unset($options['db']);
        }
        $db = $this->_verifyDb($db);

        if (is_array($documents)) {
            require_once 'Phly/Couch/DocumentSet.php';
            $documents = new Phly_Couch_DocumentSet($documents);
        } elseif (!$documents instanceof Phly_Couch_DocumentSet) {
            require_once 'Phly/Couch/Exception.php';
            throw new Phly_Couch_Exception('Invalid document set provided to bulk save operation');
        }

        $this->getHttpClient()->setRawData($documents->toJson());
        $response = $this->_prepareAndSend($db . '/_bulk_docs', 'POST', $options);
        if (!$response->isSuccessful()) {
            require_once 'Phly/Couch/Exception.php';
            throw new Phly_Couch_Exception(sprintf('Failed deleting document with id "%s" from database "%s"; received response code "%s"', $id, $db, (string) $response->getStatus()));
        }

        require_once 'Phly/Couch/Result.php';
        return new Phly_Couch_Result($response);
    }

    /**
     * Retrieve a view
     *
     * @param  string $name
     * @param  null|array $options 
     * @return Phly_Couch_DocumentSet
     * @throws Phly_Couch_Exception on failure or bad db
     */
    public function view($name, array $options = null)
    {
        $db = null;
        if (is_array($options) && array_key_exists('db', $options)) {
            $db = $options['db'];
            unset($options['db']);
        }
        $db = $this->_verifyDb($db);

        $response = $this->_prepareAndSend($db . '/design/_view/'.$name, 'GET', $options);
        if (!$response->isSuccessful()) {
            require_once 'Phly/Couch/Exception.php';
            throw new Phly_Couch_Exception(sprintf('Failed querying database "%s"; received response code "%s"', $db, (string) $response->getStatus()));
        }

        require_once 'Phly/Couch/DocumentSet.php';
        return new Phly_Couch_DocumentSet($response->getBody());
    }

    // Helper methods

    /**
     * Prepare the URI
     * 
     * @param  string $path 
     * @param  null|array $queryParams 
     * @return void
     */
    protected function _prepareUri($path, array $queryParams = null)
    {
        $client = $this->getHttpClient();
        $uri    = 'http://' . $this->getHost() . ':' . $this->getPort() . '/' . $path;

        $client->setUri($uri);
        if (null !== $queryParams) {
            foreach ($queryParams as $key => $value) {
                $queryParams[$key] = json_encode($value);
            }
            $client->setParameterGet($queryParams);
        }
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
        $client = $this->getHttpClient();
        $this->_prepareUri($path, $queryParams);
        $response = $client->request($method);
        $client->resetParameters();
        return $response;
    }

    /**
     * Verify database parameter
     * 
     * @param  mixed $db 
     * @return string
     * @throws Phly_Couch_Exception for invalid database
     */
    protected function _verifyDb($db)
    {
        if (null === $db) {
            $db = $this->getDb();
            if (null === $db) {
                require_once 'Phly/Couch/Exception.php';
                throw new Phly_Couch_Exception('Must specify a database to query');
            }
        } else {
            $this->setDb($db);
        }
        return $db;
    }
}
