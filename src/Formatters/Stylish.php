<?php

namespace Differ\Formatters\Stylish;

use function Funct\Collection\get;

function render(array $tree): string
{
    return iter($tree);
}

//рекурсивное преобразование каждой ноды массива абстрактных различий в текстовое представление в Stylish
function iter(array $node, int $depth = 1): string
{
    $children = get($node, 'children');
    $indent = buildIndent($depth);
    $value1 = get($node, 'value1');
    $value2 = get($node, 'value2');
    $value = get($node, 'value');
    $formattedValue2 = stringify($value2, $depth);
    $formattedValue1 = stringify($value1, $depth);
    $formattedValue = stringify($value, $depth);

    switch ($node['type']) {
        case 'root':
            $mapped = array_map(
                fn($child) => iter($child, $depth),
                $children
            );
            $result = implode("\n", $mapped);
            return "{\n{$result}\n}";
        case 'unchanged':
            return "{$indent}  {$node['key']}: {$formattedValue}";
        case 'changed':
            $lines = [
                "{$indent}- {$node['key']}: {$formattedValue1}",
                "{$indent}+ {$node['key']}: {$formattedValue2}"
            ];
            return implode("\n", $lines);
        case 'added':
            return "{$indent}+ {$node['key']}: {$formattedValue}";
        case 'deleted':
            return "{$indent}- {$node['key']}: {$formattedValue}";
        case 'nested':
            $mapped = array_map(
                fn($child) => iter($child, $depth + 1),
                $children
            );
            $result = implode("\n", $mapped);
            return "{$indent}  {$node['key']}: {\n{$result}\n{$indent}  }";
        default:
            throw new \Exception("Unknown type: {$node['type']}");
    }
}

function stringify(mixed $value, int $depth): string
{

    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }

    if (is_null($value)) {
        return 'null';
    }

    if (is_array($value)) {
        return implode(' ', $value);
    }

    if (!is_object($value)) {
        return (string)$value;
    }

    $closeBracketIndent = buildIndent($depth);
    $keys = array_keys(get_object_vars($value));
    $data = array_map(
        function ($key) use ($value, $depth): string {
            $dataIndent = buildIndent($depth + 1);
            $formattedValue = stringify($value->$key, $depth + 1);
            return "{$dataIndent}  {$key}: {$formattedValue}";
        },
        $keys
    );
    $string = implode("\n", $data);
    $result = "{\n{$string}\n{$closeBracketIndent}  }";
    return $result;
}

function buildIndent(int $depth, int $spacesCount = 4): string
{
    return str_repeat(' ', $depth * 4 - 2);
}
