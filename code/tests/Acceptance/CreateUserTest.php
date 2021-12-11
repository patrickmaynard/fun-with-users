<?php declare(strict_types=1);

namespace App\Tests\Acceptance;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Sonata\UserBundle\Model\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Dotenv\Dotenv;

class CreateUserTest extends WebTestCase
{
    const LOGIN_RELATIVE_URL = '/api/login_check';
    const CREATE_USER_RELATIVE_URL = '/api/user';

    public function testCreateUserWithAdminSucceeds(): void
    {
        $dotEnv = new Dotenv();
        $dotEnv->overload(__DIR__ . '/../../.env');
        if (file_exists(__DIR__ . '/../../.env.local')) {
            $dotEnv->overload(__DIR__ . '/../../.env.local');
        }
        $dotEnv->overload(__DIR__.'/../../.env.test');
        $urlOne = $_ENV['HOST_STRING'] . self::LOGIN_RELATIVE_URL;
        $client = new \GuzzleHttp\Client();
        $firstResponse = $client->request('POST', $urlOne, [
            'body' => '{"username":"superuser","password":"password"}',
            'headers' => ['Content-Type' => 'application/json']
        ]);
        $urlTwo = $_ENV['HOST_STRING'] . self::CREATE_USER_RELATIVE_URL;
        $firstResponseObj = json_decode(
            $firstResponse->getBody()->getContents()
        );
        $secondResponse = $client->request('POST', $urlTwo, [
            'body' => '{
                "userName":"bobuser",
                "email":"bobuser@example.com",
                "plainPassword":"password",
                "enabled":true
            }',
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $firstResponseObj->token
            ]
        ]);

        $secondResponseObj = json_decode(
            $secondResponse->getBody()->getContents()
        );

        self::assertEquals(201, $secondResponse->getStatusCode());
        self::assertEquals('bobuser', $secondResponseObj->username);
        self::assertEquals(
            'bobuser@example.com',
            $secondResponseObj->email
        );
    }

    public function testCreateUserWithNonAdminFails(): void
    {
        $dotEnv = new Dotenv();
        $dotEnv->overload(__DIR__ . '/../../.env');
        if (file_exists(__DIR__ . '/../../.env.local')) {
            $dotEnv->overload(__DIR__ . '/../../.env.local');
        }
        $dotEnv->overload(__DIR__.'/../../.env.test');
        $urlOne = $_ENV['HOST_STRING'] . self::LOGIN_RELATIVE_URL;
        $client = new \GuzzleHttp\Client();
        $firstResponse = $client->request('POST', $urlOne, [
            'body' => '{"username":"normaluser","password":"password"}',
            'headers' => ['Content-Type' => 'application/json']
        ]);
        $urlTwo = $_ENV['HOST_STRING'] . self::CREATE_USER_RELATIVE_URL;
        $firstResponseObj = json_decode(
            $firstResponse->getBody()->getContents()
        );
        try {
            $secondResponse = $client->request(
                'POST',
                $urlTwo,
                [
                    'body' => '{
                    "userName":"bobuser",
                    "email":"bobuser@example.com",
                    "plainPassword":"password",
                    "enabled":true
                }',
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer '.$firstResponseObj->token,
                    ],
                ]
            );
            $statusCode = $secondResponse->getStatusCode();
        } catch (GuzzleException $e) {
            if ($e->getCode() === 403) {
                $statusCode = 403;
            } else {
                $statusCode = 'Something else - investigate';
            }
        }
        self::assertEquals(403, $statusCode);
    }

}
