<?php
$libDir = realpath(dirname(__FILE__) . '/../library');
$curDir = realpath(dirname(__FILE__));
foreach (array($libDir, $curDir) as $dir) {
    $incPath = get_include_path();
    if (!strstr($incPath, $dir)) {
        set_include_path($dir . PATH_SEPARATOR . $incPath);
    }
    unset($incPath);
}
unset($curDir, $libDir);

spl_autoload_register(function($classname)
{
    $classname = ltrim($classname, '\\');
    $namespace = '';
    $filename  = '';
    if (false !== ($lastNsPos = strripos($classname, '\\'))) {
        $namespace = substr($classname, 0, $lastNsPos);
        $classname = substr($classname, $lastNsPos + 1);
        $filename  = str_replace('\\', '/', $namespace) . '/';
    }
    $filename .= str_replace('_', '/', $classname) . '.php';
    return include_once($filename);
});
