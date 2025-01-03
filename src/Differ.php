<?php

namespace Differ\Differ;

function genDiff(string $file1, string $file2, string $format = 'stylish'): void
{
    $contentFile1 = parseFile($file1);
    $contentFile2 = parseFile($file2);
}

function parseFile(string $file): array
{
    if (!file_exists($file)) {
        throw new \Exception("Invalid file path: {$file}");
    }
    $content = file_get_contents($file);
    return json_decode($content, true);
}
