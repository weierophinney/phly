<?php
$libDir = dirname(__FILE__) . '/../library';
if (file_exists($libDir) && is_dir($libDir)) {
    $incPath = get_include_path();
    if (!strstr($incPath, $libDir)) {
        set_include_path($libDir . PATH_SEPARATOR . $incPath);
    }
    unset($incPath);
}
unset($libDir);

spl_autoload_register(function($classname)
{
    $namespace = '';
    $filename  = '';
    if (false !== ($lastNsPos = strripos($classname, '\\'))) {
        $namespace = substr($classname, 0, $lastNsPos);
        $classname = substr($classname, $lastNsPos + 1);
        $filename  = str_replace('\\', '/', $namespace) . '/';
    }
    $filename .= str_replace('_', '/', $classname) . '.php';
    return require_once($filename);
});

