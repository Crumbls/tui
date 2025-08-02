<?php
// Pest helper functions for accessing protected/private properties
function get_private_property($object, $property) {
    $ref = new ReflectionProperty($object, $property);
    $ref->setAccessible(true);
    return $ref->getValue($object);
}

function set_private_property($object, $property, $value) {
    $ref = new ReflectionProperty($object, $property);
    $ref->setAccessible(true);
    $ref->setValue($object, $value);
}
