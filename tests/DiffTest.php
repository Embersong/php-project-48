<?php

namespace Converter\Phpunit\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

function getFixtureFullPath($fixtureName): string
{
    $parts = [__DIR__, 'fixtures', $fixtureName];
    return realpath(implode('/', $parts));
}

class DiffTest extends TestCase
{

    #[DataProvider('formatProvider')]
    public function testDefault($format): void
    {
        $filepath1 = getFixtureFullPath("file1.{$format}");
        $filepath2 = getFixtureFullPath("file2.{$format}");

        $pathToResult = getFixtureFullPath('expected-stylish.txt');
        $this->assertStringEqualsFile($pathToResult, genDiff($filepath1, $filepath2));
    }

    #[DataProvider('formatProvider')]
    public function testStylish($format): void
    {
        $filepath1 = getFixtureFullPath("file1.{$format}");
        $filepath2 = getFixtureFullPath("file2.{$format}");

        $pathToResult = getFixtureFullPath('expected-stylish.txt');
        $this->assertStringEqualsFile($pathToResult, genDiff($filepath1, $filepath2, 'stylish'));
    }

    #[DataProvider('formatProvider')]
    public function testPlain($format): void
    {
        $filepath1 = getFixtureFullPath("file1.{$format}");
        $filepath2 = getFixtureFullPath("file2.{$format}");

        $pathToResult = getFixtureFullPath('expected-plain.txt');
        $this->assertStringEqualsFile($pathToResult, genDiff($filepath1, $filepath2, 'plain'));
    }

    #[DataProvider('formatProvider')]
    public function testJson($format): void
    {
        $filepath1 = getFixtureFullPath("file1.{$format}");
        $filepath2 = getFixtureFullPath("file2.{$format}");

        $pathToResult = getFixtureFullPath('expected-json.txt');
        $this->assertStringEqualsFile($pathToResult, genDiff($filepath1, $filepath2, 'json'));
        //$data = genDiff($filepath1, $filepath2, 'json');
        //json_decode($data, null, 512, JSON_THROW_ON_ERROR);
        //$this->assertTrue(true);
    }

    public static function formatProvider(): array
    {
        return [
            ['json'],
            ['yaml']
        ];
    }
}