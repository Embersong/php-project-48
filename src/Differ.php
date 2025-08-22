<?php

namespace Differ\Differ;

use Exception;

use function Differ\Parsers\parse;
use function Functional\sort;

function genDiff(string $file1, string $file2): string
{
    ['type' => $type1, 'data' => $stringData1] = parseFile($file1);
    ['type' => $type2, 'data' => $stringData2] = parseFile($file2);

    $data1 = parse($type1, $stringData1);
    $data2 = parse($type2, $stringData2);

    return findDiff($data1, $data2);
}

function parseFile(string $file): array
{
    if (!file_exists($file)) {
        throw new Exception("Invalid file path: {$file}");
    }
    $type = pathinfo($file)['extension'] ?? '';
    $data = file_get_contents($file);
    return ['type' => $type, 'data' => $data];
}

function findDiff(object $file1, object $file2): string
{
    $uniqueKeys = array_unique(
        array_merge(
            array_keys(get_object_vars($file1)),
            array_keys(get_object_vars($file2))
        )
    );
    $sortedArray = sort(
        $uniqueKeys,
        function ($first, $second) {
            return $first <=> $second;
        }
    );

    $res = array_map(
        function (string $key) use ($file1, $file2) {
            $value1 = boolToString($file1->$key ?? null);
            $value2 = boolToString($file2->$key ?? null);

            if (!property_exists($file1, $key)) {
                return "  + {$key}: {$value2}";
            }

            if (!property_exists($file2, $key)) {
                return "  - {$key}: {$value1}";
            }

            if ($value1 === $value2) {
                return "    {$key}: {$value1}";
            }

            return "  - {$key}: {$value1}" . PHP_EOL . "  + {$key}: {$value2}";
        },
        $sortedArray
    );
    $result = implode("\n", $res);

    return "{\n{$result}\n}";
}

function boolToString(mixed $string): mixed
{
    if (is_bool($string)) {
        return $string ? 'true' : 'false';
    }

    return $string;
}
