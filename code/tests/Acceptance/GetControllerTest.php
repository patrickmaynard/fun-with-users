<?php

declare(strict_types=1);

namespace App\Tests\Acceptance;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GetControllerTest extends WebTestCase
{
    public function testTestController_returns_Success(): void
    {
        $client = static::createClient();
        $client->request('GET', '/basic-test');

        self::assertEquals(200, $client->getResponse()->getStatusCode());
        self::assertStringContainsString(
            "The content has been loaded",
            $client->getResponse()->getContent()
        );
    }
}
