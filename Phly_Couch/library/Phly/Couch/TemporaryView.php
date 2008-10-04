<?php

// TODO: Probably temporary views can also have the notion of "language"?
class Phly_Couch_TemporaryView extends Phly_Couch_View
{
    protected $_map = null;

    protected $_reduce = null;

    /**
     * Construct new temporary view.
     *
     * @param string      $map
     * @param string|null $reduce
     * @param Phly_Couch  $database
     */
    public function __construct($map, $reduce=null, $database=null)
    {
        $this->setMap($map);
        $this->setReduce($reduce);
        if($database instanceof Phly_Couch) {
            $this->setDatabase($database);
        }
    }

    /**
     * Set map function of temporary view.
     *
     * @param  string $map
     * @return Phly_Couch_TemporaryView
     */
    public function setMap($map)
    {
        if(is_string($map)) {
            $this->_map = str_replace(array("\r", "\n"), '', $map);
        }
        return $this;
    }

    /**
     * Set reduce function of temporary view or null to unset.
     *
     * @param  string|null $reduce
     * @return Phly_Couch_TemporaryView
     */
    public function setReduce($reduce)
    {
        if(is_string($reduce) || $reduce === null) {
            $this->_reduce = str_replace(array("\r", "\n"), "", $reduce);
        }
        return $this;
    }

    /**
     * Get current "map" function of this temporary view.
     *
     * @return string
     */
    public function getMap()
    {
        return $this->_map;
    }

    /**
     * Get current "reduce" function of this temporary view.
     *
     * Can return null since "reduce" function is optional.
     *
     * @return string|null
     */
    public function getReduce()
    {
        return $this->_reduce;
    }

    /**
     * Move the current temporary view object as permanent view into a given design document.
     *
     * @param string $designDocumentName
     * @param string $viewName
     * @return Phly_Couch_View
     */
    public function moveToPermanentView($designDocumentName, $viewName)
    {
        // Try to fetch existing design document
        try {
            $designDoc = $this->getDatabase()->docOpen($designDocumentName);
        } catch(Phly_Couch_Exception $e) {
            $designDoc = null;
        }
        // TODO: check for same language

        if($designDoc === null) {
            $designDoc = new Phly_Couch_DesignDocument(array('_id' => $designDocumentName));
        }
        return $designDoc->addView($viewName, $this->getMap(), $this->getReduce());
    }

    /**
     * Prepare the URI and send the request
     *
     * @param  string $path
     * @param  string $method
     * @param  null|array $queryParams
     * @return Zend_Http_Response
     */
    protected function _prepareAndSend($queryParams = null)
    {
        $reduce = $this->getReduce();
        if(strlen($reduce) > 0) {
            $tempViewJson = array("map" => $this->getMap(), "reduce" => $reduce);
        } else {
            $tempViewJson = array("map" => $this->getMap());
        }
        $tempViewJson = Zend_Json::encode($tempViewJson);

        $path = $this->getDatabase()->getDb() . '/_temp_view';
        return $this->getDatabase()->getConnection()->send($path, 'POST', $queryParams, $tempViewJson);
    }
}