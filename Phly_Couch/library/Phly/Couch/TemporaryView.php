<?php

// TODO: Probably temporary views can also have the notion of "language"?
class Phly_Couch_TemporaryView extends Phly_Couch_View
{
    protected $_map = null;

    protected $_reduce = null;

    public function __construct($map, $reduce=null, Phly_Couch $database=null)
    {
        $this->setMap($map);
        $this->setReduce($reduce);
        $this->setDatabase($database);
    }

    public function setMap($map)
    {
        if(is_string($map)) {
            $this->_map = str_replace(array("\r", "\n"), '', $map);
        }
        return $this;
    }

    public function setReduce($reduce)
    {
        if(is_string($reduce) || $reduce === null) {
            $this->_reduce = str_replace(array("\r", "\n"), "", $reduce);
        }
        return $this;
    }

    public function getMap()
    {
        return $this->_map;
    }

    public function getReduce()
    {
        return $this->_reduce;
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