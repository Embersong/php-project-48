<?php

namespace Differ\Differ;

use Exception;

use function Differ\Parsers\parse;
use function Differ\Formatters\format;
use function Functional\sort;

/**
 * @throws Exception
 */
function genDiff(string $file1, string $file2, string $format = 'stylish'): string
{
    //Получили данные и тип из файлов в виде текста
    ['type' => $type1, 'data' => $stringData1] = parseFile($file1);
    ['type' => $type2, 'data' => $stringData2] = parseFile($file2);

    //В зависимости от типа преобразовали данные в strClass
    $data1 = parse($type1, $stringData1);
    $data2 = parse($type2, $stringData2);

    //Получаем абстрактное дерево различий
    $diffTree = findDiff($data1, $data2);

    //Вызываем render формирующий по дереву текстовое представление
    //Достраиваем структуру добавляя ключ корня дерева
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
function parseFile(string $file): array
{
    if (!file_exists($file)) {
        throw new Exception("Invalid file path: {$file}");
    }
    //Определяем тип данных по его расширению
    $type = pathinfo($file)['extension'] ?? '';
    $data = file_get_contents($file);
    return ['type' => $type, 'data' => $data];
}

//теперь построитель отличий возвращает массив абстракций с изменениями данных
function findDiff(object $file1, object $file2): array
{
    //Получили массив уникальных ключей путем преобразования объектов с данными
    //в массив и его слияния
    $uniqueKeys = array_unique(
        array_merge(
            array_keys(get_object_vars($file1)),
            array_keys(get_object_vars($file2))
        )
    );

    //сделали не мутабельную сортировку данных
    $sortedArray = sort(
        $uniqueKeys,
        function ($first, $second) {
            return $first <=> $second;
        }
    );

    //Обходим массив $sortedArray, В $key индексы 0,1,2...
    //По $key извлекаем из stdObject значения для сравнения
    $res = array_map(
        function (string $key) use ($file1, $file2) {
            $value1 = $file1->$key ?? null;
            $value2 = $file2->$key ?? null;

            //Сравниваем значения
            //Если во втором файле нет такого свойства
            if (!property_exists($file2, $key)) {
                return [
                    'key' => $key,
                    'type' => 'deleted',
                    'value' => $value1,
                ];
            }

            //Если в первом файле нет такого свойства
            if (!property_exists($file1, $key)) {
                return [
                    'key' => $key,
                    'type' => 'added',
                    'value' => $value2,
                ];
            }

            //В случает если вместо данных подструктура-объект, делам рекурсивный вызов
            if (is_object($value1) && is_object($value2)) {
                // Формируем children ноду рекусивно
                return [
                    'key' => $key,
                    'type' => 'nested',
                    'children' => findDiff($value1, $value2),
                ];
            }

            //Помечаем что данные изменились
            if ($value1 !== $value2) {
                return [
                    'key' => $key,
                    'type' => 'changed',
                    'value1' => $value1,
                    'value2' => $value2,
                ];
            }

            //Все совпало и данные без изменений
            return [
                'key' => $key,
                'type' => 'unchanged',
                'value' => $value1,
            ];
        },
        $sortedArray
    );

    return $res;
}
