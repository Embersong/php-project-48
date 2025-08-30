<?php

namespace Differ\Formatters;

function format(array $tree, string $formatName): string
{
    return match ($formatName) {
        'stylish' => Stylish\render($tree),
        'plain' => Plain\render($tree),
        default => throw new \Error("Unknown format: {$formatName}"),
    };
}
