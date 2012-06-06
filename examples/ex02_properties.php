<?php
/**
 * This example is a low-level look at how properties can be defined for
 * various elements. The syntax here can be used for any element properties
 * within the library.
 */

require __DIR__ . '/autoload.php';

use PHPDOC\Property\Properties;

// Properties can be set in various ways... Use which ever is easiest for you

$prop = new Properties(array('new' => 'via new'));              // via constructor
$prop->set('set', 'via set()');                                 // via set
$prop->setCallMagic('via __call()');                            // via setter (magic)
$prop['ArrayMagic'] = 'via array[]';                            // via array access (magic)

print_r($prop->all());
