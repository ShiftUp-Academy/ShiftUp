<?php
require 'vendor/autoload.php';

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Collection;

function check_method($class, $method) {
    try {
        $refl = new ReflectionMethod($class, $method);
        $type = $refl->getParameters()[0]->getType();
        echo "Class: $class, Method: $method, Parameter 1 Type: " . ($type ? $type->__toString() : 'None') . "\n";
    } catch (Throwable $e) {
        echo "Error in $class: " . $e->getMessage() . "\n";
    }
}

check_method(QueryBuilder::class, 'where');
check_method(EloquentBuilder::class, 'where');
check_method(Collection::class, 'where');
