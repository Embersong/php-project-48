<?php

namespace Differ\Differ;

use Exception;

use function Differ\Parsers\parse;
use function Differ\Formatters\format;
use function Functional\sort;

/**
 * @throws Exception
 */
function genDiff(string $path1, string $path2, string $format = 'stylish'): string
{
    ['type' => $type1, 'data' => $rawData1] = getFileData($path1);
    ['type' => $type2, 'data' => $rawData2] = getFileData($path2);

    $data1 = parse($type1, $rawData1);
    $data2 = parse($type2, $rawData2);
    $diffTree = findDiff($data1, $data2);

    return format(
        [
            'type' => 'root',
            'children' => $diffTree,
        ],
        $format
    );
}

/**
 * @throws Exception
 */
function getFileData(string $path): array
{
    if (!file_exists($path)) {
        throw new Exception("Invalid file path: {$path}");
    }

    $type = pathinfo($path)['extension'] ?? '';
    $data = file_get_contents($path);
    return ['type' => $type, 'data' => $data];
}

function findDiff(object $file1, object $file2): array
{
    $uniqueKeys = array_unique(
        array_merge(
            array_keys(get_object_vars($file1)),
            array_keys(get_object_vars($file2))
        )
    );

    $sortedKeys = sort(
        $uniqueKeys,
        function ($first, $second) {
            return $first <=> $second;
        }
    );

    $res = array_map(
        function (string $key) use ($file1, $file2) {
            $value1 = $file1->$key ?? null;
            $value2 = $file2->$key ?? null;

            if (!property_exists($file2, $key)) {
                return [
                    'key' => $key,
                    'type' => 'deleted',
                    'value' => $value1,
                ];
            }

            if (!property_exists($file1, $key)) {
                return [
                    'key' => $key,
                    'type' => 'added',
                    'value' => $value2,
                ];
            }

            if (is_object($value1) && is_object($value2)) {
                return [
                    'key' => $key,
                    'type' => 'nested',
                    'children' => findDiff($value1, $value2),
                ];
            }

            if ($value1 !== $value2) {
                return [
                    'key' => $key,
                    'type' => 'changed',
                    'value1' => $value1,
                    'value2' => $value2,
                ];
            }

            return [
                'key' => $key,
                'type' => 'unchanged',
                'value' => $value1,
            ];
        },
        $sortedKeys
    );

    return $res;
}
