<?php
namespace phly\mvc;

class Dispatcher implements DispatcherInterface
{
    protected $_directories = array();

    public function dispatch(EventInterface $e)
    {
        $request    = $e->getRequest();
        $module     = $request->getModuleName();
        $controller = $request->getControllerName();

        if (empty($module)
            || ($module == 'default') // request object default
        ) {
            $module = 'application';
        }

        if (empty($controller)) {
            $controller = 'index';
        }

        $dirs = $this->getControllerDirectories();
        if (!in_array($module, array_keys($dirs))) {
            $e->setState('error');
            $e->addException(new InvalidModuleException());
            return;
        }

        $dir = $dirs[$module];
        $controllerSegment = $this->_normalizeClassName($controller) . 'Controller';
        $classFile = $dir . '/' . str_replace('_', '/', $controllerSegment) . '.php';
        $className = lcfirst($this->_normalizeClassName($module)) . '\\' 
                   . $controllerSegment;
        if (!file_exists($classFile)) {
            $e->setState('error');
            $e->addException(new ControllerNotFoundException());
            return;
        }

        include_once $classFile;
        if (!class_exists($className)) {
            $e->setState('error');
            $e->addException(new ControllerNotFoundException());
            return;
        }

        $controller = new $className();

        if (!is_callable($controller)) {
            $e->setState('error');
            $e->addException(new InvalidControllerException());
            return;
        }

        try {
            $controller($e);
        } catch (\Exception $except) {
            $e->setState('error');
            $e->addException($except);
        }

        return $e;
    }

    public function addControllerDirectory($dir, $module = 'application')
    {
        $this->_directories[$module] = $dir;
        return $this;
    }

    public function getControllerDirectories()
    {
        return $this->_directories;
    }

    protected function _normalizeClassName($name)
    {
        $segments = explode('_', $name);

        foreach ($segments as $key => $segment) {
            $segment = str_replace(array('.', '-'), ' ', $segment);
            $segment = ucwords($segment);
            $segment = str_replace(' ', '', $segment);
            $segments[$key] = $segment;
        }
        $controller = implode('_', $segments);
        return $controller;
    }
}
