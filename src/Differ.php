<?php

namespace Differ\Differ;

use function Functional\sort;
use \Exception;

function genDiff(string $file1, string $file2): string
{
    $contentFile1 = parseFile($file1);
    $contentFile2 = parseFile($file2);

    return findDiff($contentFile1, $contentFile2);
}

function parseFile(string $file): array
{
    if (!file_exists($file)) {
        throw new Exception("Invalid file path: {$file}");
    }
    $content = file_get_contents($file);
    return json_decode($content, true);
}

function findDiff(array $file1, array $file2): string
{
    $uniqueKeys = array_unique(array_merge(array_keys($file1), array_keys($file2)));
    $sortedArray = sort($uniqueKeys, function ($first, $second) {
        return $first <=> $second;
    });

    $res = array_map(function ($key) use ($file1, $file2) {
        $value1 = boolToString($file1[$key] ?? null);
        $value2 = boolToString($file2[$key] ?? null);

        if (!array_key_exists($key, $file1)) {
            return "+ {$key}: {$value2}";
        }

        if (!array_key_exists($key, $file2)) {
            return "- {$key}: {$value1}";
        }

        if ($value1 === $value2) {
            return "  {$key}: {$value1}";
        }

        return "- {$key}: {$value1}" . PHP_EOL . "+ {$key}: {$value2}";
    }, $sortedArray);
    return implode(PHP_EOL, $res) . PHP_EOL;
}

function boolToString(mixed $string): mixed
{
    if (is_bool($string)) {
        return $string ? 'true' : 'false';
    }

    return $string;
}
