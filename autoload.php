<?php
/**
 * HPHIO Utility Class autoloader
 *
 * @param string $class The fully-qualified class name.
 * @return void
 */

spl_autoload_register(function ($class) {


    // project-specific namespace prefix
    $prefix = "hphio\\util\\";

    // base directory for the namespace prefix
    $base_dir = __DIR__ . '/src/';

    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    // get the relative class name

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $class) . '.php';

//    $log = new Monolog\Logger('autoloader');
//    $log->pushHandler(new \Monolog\Handler\StreamHandler('autoloader.log', \Monolog\Logger::DEBUG));
//    $log->debug("Looking for: $file to fulfil the request for $class");

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }

});