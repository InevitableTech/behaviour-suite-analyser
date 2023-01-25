<?php

namespace Forceedge01\BDDStaticAnalyser\Processor;

class ArrayProcessor {
    public static function applySeveritiesFilter(array $outcomes, array $severities): array {
        foreach ($outcomes as $index => $outcome) {
            if (! in_array($outcome->severity, $severities)) {
                unset($outcomes[$index]);
            }
        }

        return $outcomes;
    }

    public static function sortByFile(array $outcomes): array {
        // Sort by file.
        $sorted = [];
        foreach ($outcomes as $items) {
            $sorted[$items->file][] = $items;
        }

        return $sorted;
    }

    public static function sortInternalArrayBy(array $sorted, string $column, int $order) {
        // Sort by severity, lineNumber
        foreach ($sorted as $file => $items) {
            $sorted[$file] = self::sortArray($column, $items, $order);
        }

        return $sorted;
    }

    private static function sortArray(string $column, array $items, $sortOrder): array {
        array_multisort(array_column($items, $column), $sortOrder, $items);

        return $items;
    }
}