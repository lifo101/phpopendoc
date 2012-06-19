<?php
/**
 * This example is a low-level look at how properties can be defined for
 * various document elements.
 * Properties are just fancy array's with some extra magic thrown in.
 */

require __DIR__ . '/autoload.php';

use PHPDOC\Property\Properties;

// Properties can be set in various ways... Use which ever is easiest for you

$prop = new Properties(array('new' => 'via new'));              // via constructor
$prop->set('set', 'via set()');                                 // via set
$prop->setCallMagic('via __call()');                            // via setter (magic)
$prop['ArrayMagic'] = 'via array[]';                            // via array access (magic)
$prop['nested.array.key'] = 'nested value';                     // nested arrays made easy

print_r($prop->all());
