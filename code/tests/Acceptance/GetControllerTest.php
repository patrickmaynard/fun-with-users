<?php

declare(strict_types=1);

namespace App\Tests\Acceptance;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GetControllerTest extends WebTestCase
{
    public function testTestControllerReturnsSuccess(): void
    {
        $client = static::createClient();
        $client->request('GET', '/basic-test');

        self::assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testTestControllerReturnsValidDatabaseRecords(): void
    {
        $client = static::createClient();
        $client->request('GET', '/basic-test');
        $content = json_decode(
            $client->getResponse()->getContent(),
            true
        );

        self::assertIsArray($content);
        self::assertArrayHasKey('id', $content[0]);
        self::assertArrayHasKey('name', $content[0]);
        self::assertEquals(2, count($content));
        self::assertEquals('Test entity one', $content[0]['name']);
        self::assertEquals('Test entity two', $content[1]['name']);
    }
}
