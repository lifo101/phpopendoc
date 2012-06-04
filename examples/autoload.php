<?php

/**
 * This is not an example script.
 * This is the autoloader (bootstrap) file used by each example in order to
 * setup the environment.
 */

require_once __DIR__ . '/../vendor/symfony/Symfony/Component/ClassLoader/UniversalClassLoader.php';
//require_once __DIR__ . '../vendor/Symfony/Component/ClassLoader/ApcUniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;
//use Symfony\Component\ClassLoader\ApcUniversalClassLoader;

$loader = new UniversalClassLoader();
//$loader = new ApcUniversalClassLoader('phpdoc.ex.');
$loader->register();
$loader->registerNamespace('PHPDOC', __DIR__ . '/../lib/phpopendoc');

