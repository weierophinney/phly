<?php

class Phly_Couch_DesignDocument extends Phly_Couch_Document
{
    public function getLanguage()
    {
        return $this->_data['language'];
    }

    public function setLanguage($lang)
    {
        if(preg_match('/([a-zA-Z0-9-_]+)/', $lang)) {
            $this->_data['language'] = (string)$lang;
        }
        return $this;
    }

    public function getViewDefinitions()
    {
        return $this->_data['views'];
    }

    public function addView($name, $map, $reduce=null)
    {
        if(isset($this->_data['views'][$name])) {
            throw new Phly_Couch_Exception(sprintf("There is already a view '%s' in the design document '%s'.", $name, $this->getId()));
        }
        $this->setView($name, $map, $reduce);
    }

    public function setView($name, $map, $reduce=null)
    {
        if($reduce === null) {
            $mapReduce = array('map' => $map);
        } else {
            $mapReduce = array('map' => $map, 'reduce' => $reduce);
        }
        $this->_data['views'][$name] = $mapReduce;
    }

    /**
     * Query one of the views
     *
     * @param unknown_type $viewName
     * @param array $queryParams
     * @return unknown
     */
    public function fetchView($viewName, array $queryParams=array())
    {
        if(!isset($this->_data['views'][$viewName])) {
            throw new Phly_Couch_Exception(sprintf("Design document '%s' has no view with the name '%s'", $this->getId(), $viewName));
        }

        $view = new Phly_Couch_View($this->getId()."/".$viewName, $this->getDatabase());
        if(count($queryParams) > 0) {
            $view->query($queryParams);
        }
        return $view;
    }

    public function __set($name, $value)
    {
        if(!in_array($name, array('_id', '_rev', 'views', 'language'))) {
            throw new Phly_Couch_Exception("Design documents are special docuements that allow only the '_id', '_rev', 'language' or 'views' data in it");
        }
        if($name == "views" && !is_array($value)) {
            throw new Phly_Couch_Exception("The key 'views' in a design document has to be an array.");
        }
        parent::__set($name, $value);
    }
}