<?php

namespace Vroum\Utils;

class Arrays {
    /**
     * Map an array using its keys and its values.
     *
     * @param callable $f The binary function to apply on each ($key, $value) pair.
     *                    It must return a list `[$newKey, $newValue]`.
     * @param array<mixed, mixed> $arr The array to map
     *
     * @return array<mixed, mixed> The array containing the result of applying the function to each tuple.
     * */
    public static function map_with_key(callable $f, array $arr): array {
        $res = [];

        foreach ($arr as $key => $value) {
            list($newKey, $newValue) = $f($key, $value);
            $res[$newKey] = $newValue;
        }

        return $res;
    }


    /**
     * Checks whether any element in an array satisfies a predicate.
     *
     * This function also short-circuits, meaning that it will stop at the first element satisfying the predicate.
     *
     * @param array<int|string, mixed> $array the array of value in which to test the predicate
     *
     * @param callable $predicate the predicate to test on all elements
     *
     * @return bool `TRUE` if at leats one element satisfies the predicate, `FALSE` otherwise
     *
     * @author Ghilain Bergeron
     * */
    function array_any($array, $predicate) {
        return array_reduce($array, function ($acc, $e) use ($predicate) {
            return $acc || $predicate($e);
        }, FALSE);
    }

    /**
     * TODO: doc
     * */
    function filter_map($array, $predicate, $mapper) {
        $res = [];
        foreach ($array as $v) {
            if ($predicate($v))
                $res[] = $mapper($v);
        }
        return $res;
    }
}

?>
