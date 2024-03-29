<?php

declare(strict_types = 1);

namespace Forceedge01\BDDStaticAnalyser\Processor;

class ArrayProcessor
{
    public static function applySeveritiesFilter(array $outcomes, array $severities): array
    {
        foreach ($outcomes as $index => $outcome) {
            if (! in_array($outcome->severity, $severities)) {
                unset($outcomes[$index]);
            }
        }

        return $outcomes;
    }

    public static function sortByFile(array $outcomes): array
    {
        // Sort by file.
        $sorted = [];
        foreach ($outcomes as $items) {
            $sorted[$items->file][] = $items;
        }

        return $sorted;
    }

    public static function sortByRule(array $outcomes): array
    {
        // Sort by file.
        $sorted = [];
        foreach ($outcomes as $items) {
            $sorted[$items->getRuleShortName()][] = $items;
        }

        return $sorted;
    }

    public static function sortInternalArrayBy(array $sorted, string $column, int $order)
    {
        // Sort by severity, lineNumber
        foreach ($sorted as $index => $items) {
            $sorted[$index] = self::sortArray($column, $items, $order);
        }

        return $sorted;
    }

    public static function cleanArray($array)
    {
        return array_values(array_filter($array));
    }

    public static function reIndexArray($array)
    {
        return array_values($array);
    }

    private static function sortArray(string $column, array $items, $sortOrder): array
    {
        array_multisort(array_column($items, $column), $sortOrder, $items);

        return $items;
    }

    public static function getContentBetween(string $startRegex, string $endRegex, array $content): array
    {
        $start = $end = null;
        foreach ($content as $index => $line) {
            // Starting line.
            if (!$start && preg_match($startRegex, $line)) {
                $start = $index + 1;
                continue;
            }

            if (preg_match($endRegex, $line)) {
                $end = $index;
                break;
            }
        }

        // Return the content.
        return array_slice($content, $start, $end - $start);
    }

    public static function getContentMatching(string $regex, array $content): ?string
    {
        $start = $end = null;
        foreach ($content as $index => $line) {
            if (preg_match($regex, $line)) {
                return $line;
            }
        }

        return null;
    }

    public static function getIndexMatching(string $regex, array $content): ?int
    {
        foreach ($content as $index => $line) {
            if (preg_match($regex, $line)) {
                return $index;
            }
        }

        return null;
    }

    public static function implodeWithKeys(array $array, string $glueChar, string $separatorChar): string
    {
        $string = '';
        foreach ($array as $index => $item) {
            $string .= "{$index}{$glueChar}{$item}{$separatorChar}";
        }

        return trim($string, $separatorChar);
    }
}
