<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Modules\API\ContentAPI\ExpediaSupplier\ParallelFileMaker;
use ReflectionClass;

class ParallelFileMakerTest extends TestCase
{
/*
    protected function invokePrivateMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }
    public function testRun()
    {
        // Создаем объект ParallelFileMaker
        $fileMaker = new ParallelFileMaker();

        // Вызываем метод run()
        $fileMaker->run();

        // Проверяем, что файлы были созданы и не пусты
        $this->assertFileExists('output.jsonl.gz');
        $this->assertGreaterThan(0, filesize('output.jsonl.gz'));
    }
 
    public function testDivideUpCalls()
    {
        // Создаем объект ParallelFileMaker
        $fileMaker = new ParallelFileMaker();

        // Вызываем приватный метод divideUpCalls() с тестовыми данными
        $result = $fileMaker->invokePrivateMethod($fileMaker, 'divideUpCalls');

        // Проверяем, что результат является массивом и содержит ожидаемое количество элементов
        $this->assertIsArray($result);
        $this->assertCount(count(ParallelFileMaker::COUNTRIES), $result);
    }

    public function testCombineStreams()
    {
        // Создаем объект ParallelFileMaker
        $fileMaker = new ParallelFileMaker();

        // Создаем тестовые массивы с данными
        $stream1 = [1, 2, 3];
        $stream2 = [4, 5, 6];
        $stream3 = [7, 8, 9];

        // Вызываем приватный метод combineStreams() с тестовыми данными
        $result = $fileMaker->invokePrivateMethod($fileMaker, 'combineStreams', [$stream1, $stream2, $stream3]);

        // Проверяем, что результат является массивом и содержит ожидаемое количество элементов
        $this->assertIsArray($result);
        $this->assertCount(count($stream1) + count($stream2) + count($stream3), $result);
    }

    public function testCreateFileWriter()
    {
        // Создаем объект ParallelFileMaker
        $fileMaker = new ParallelFileMaker();

        // Вызываем приватный метод createFileWriter() с тестовым путем
        $outputFileWriter = $fileMaker->invokePrivateMethod($fileMaker, 'createFileWriter', ['test.jsonl.gz']);

        // Проверяем, что файловый ресурс успешно создан
        $this->assertIsResource($outputFileWriter);
        $this->assertEquals('GLOB', get_resource_type($outputFileWriter));

        // Закрываем файловый ресурс
        fclose($outputFileWriter);

        // Убеждаемся, что файл успешно закрыт
        $this->assertFalse(is_resource($outputFileWriter));
    } */
}
