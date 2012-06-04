<?php

require_once __DIR__ . '/../vendor/symfony/Symfony/Component/ClassLoader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->register();
$loader->registerNamespace('PHPDOC', __DIR__ . '/../lib/phpopendoc');

