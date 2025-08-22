<?php

namespace Differ\Parsers;

use Exception;
use Symfony\Component\Yaml\Yaml;

function parse(string $type, string $data): object
{
    return match ($type) {
        'yml', 'yaml' => Yaml::parse($data, Yaml::PARSE_OBJECT_FOR_MAP),
        'json' => json_decode($data),
        default => throw new Exception("Unknown format: '$type'"),
    };
}
