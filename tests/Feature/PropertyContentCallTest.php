<?php

namespace Tests\Feature;use Modules\API\ContentAPI\ExpediaSupplier\PropertyContentCall;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class PropertyContentCallTest extends TestCase
{
   /*  public function testStreamMethod()
    {
        // Создаем заглушку (mock) для GuzzleHttp\Client
        $mockHandler = new MockHandler([
            new Response(200, [], '{"property_id": 1, "name": "Property 1"}'),
            new Response(200, [], '[]'), // Пустой ответ для завершения цикла
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $client = new Client(['handler' => $handlerStack]);

        // Создаем экземпляр PropertyContentCall с фиктивными данными
        $property = [
            'language' => 'en-US',
            'supplySource' => 'expedia',
            'countryCodes' => ['US'],
            'categoryIdExcludes' => [],
            'propertyRatingMin' => 3.3,
            'propertyRatingMax' => 5.0,
        ];

        $propertyContentCall = new PropertyContentCall($client, $property);

        // Вызываем метод stream()
        $results = $propertyContentCall->stream();

        // Проверяем, что результат не пустой и соответствует ожидаемой структуре
        $this->assertNotEmpty($results);
        $this->assertEquals(2, count($results)); // 2 запроса (1 успешный, 1 пустой)
    }

    public function testSizeMethod()
    {
        // Создаем заглушку (mock) для GuzzleHttp\Client
        $mockHandler = new MockHandler([
            new Response(200, [], '{"Pagination-Total-Results": 10}'), // Задаем размер
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $client = new Client(['handler' => $handlerStack]);

        // Создаем экземпляр PropertyContentCall с фиктивными данными
        $property = [
            'language' => 'en-US',
            'supplySource' => 'expedia',
            'countryCodes' => ['US'],
            'categoryIdExcludes' => [],
            'propertyRatingMin' => 3.3,
            'propertyRatingMax' => 5.0,
        ];

        $propertyContentCall = new PropertyContentCall($client, $property);

        // Вызываем метод size()
        $size = $propertyContentCall->size();

        // Проверяем, что размер соответствует ожидаемому
        $this->assertEquals(10, $size);
    } */
}
