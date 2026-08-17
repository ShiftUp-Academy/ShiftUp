<?php
require 'vendor/autoload.php';

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

function check_method($class, $method) {
    try {
        $refl = new ReflectionMethod($class, $method);
        $type = $refl->getParameters()[0]->getType();
        echo "Class: $class, Parameter: " . (string)$type . "\n";
    } catch (Throwable $e) {
        echo "Error in $class: " . $e->getMessage() . "\n";
    }
}

check_method(QueryBuilder::class, 'where');
check_method(EloquentBuilder::class, 'where');
check_method(\Illuminate\Collections\Collection::class, 'where');
