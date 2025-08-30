<?php

namespace Differ\Formatters\Plain;

use function Funct\Collection\flattenAll;
use function Funct\Collection\get;

function stringify(mixed $value): string
{
    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }

    if (is_null($value)) {
        return 'null';
    }

    if (is_string($value)) {
        return "'$value'";
    }

    if (is_object($value) || is_array($value)) {
        return '[complex value]';
    }

    return (string)$value;
}

function iter(array $node, string $ancestry = ''): array
{
    $children = get($node, 'children');
    $key = get($node, 'key');
    $propertyName = "{$ancestry}{$key}";

    switch ($node['type']) {
        case 'root':
            $mapped = array_map(
                fn($child) => iter($child),
                $children
            );
            return flattenAll($mapped);
        case 'nested':
            return array_map(
                fn($child) => iter($child, "{$ancestry}{$node['key']}."),
                $children
            );
        case 'added':
            return [sprintf("Property '%s' was added with value: %s", $propertyName, stringify($node['value']))];
        case 'deleted':
            return [sprintf("Property '%s' was removed", $propertyName)];
        case 'changed':
            return [sprintf(
                "Property '%s' was updated. From %s to %s",
                $propertyName,
                stringify($node['value1']),
                stringify($node['value2'])
            )];
        case 'unchanged':
            return [];
        default:
            throw new \Exception("Unknown type: {$node['type']}");
    }
}


function render(array $tree): string
{
    $lines = iter($tree);
    return implode("\n", $lines);
}
