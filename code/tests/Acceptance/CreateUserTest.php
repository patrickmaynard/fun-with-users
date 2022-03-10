<?php declare(strict_types=1);

namespace App\Tests\Acceptance;

use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Dotenv\Dotenv;

class CreateUserTest extends AbstractUserTest
{
    const CREATE_USER_RELATIVE_URL = '/api/user';

    public function testCreateUserWithAdminSucceeds(): void
    {
        $firstResponseObj = $this->getSuperUserAuthenticationResponseObject();
        $urlTwo = $_ENV['HOST_STRING'].self::CREATE_USER_RELATIVE_URL;
        $client = new \GuzzleHttp\Client();
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

    public function testCreateUserWithoutUsernameFails(): void
    {
        $firstResponseObj = $this->getSuperUserAuthenticationResponseObject();
        $urlTwo = $_ENV['HOST_STRING'].self::CREATE_USER_RELATIVE_URL;
        $client = new \GuzzleHttp\Client();
        try {
            $secondResponse = $client->request(
                'POST',
                $urlTwo,
                [
                    'body' => '{
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
            $secondResponseObj = json_decode(
                $secondResponse->getBody()->getContents()
            );
            $statusCode = $secondResponse->getStatusCode();
        } catch (GuzzleException $e) {
            $statusCode = $e->getCode();
        }
        self::assertEquals(400, $statusCode);
    }

    public function testCreateUserWithOutEmailFails(): void
    {
        $firstResponseObj = $this->getSuperUserAuthenticationResponseObject();
        $urlTwo = $_ENV['HOST_STRING'].self::CREATE_USER_RELATIVE_URL;
        $client = new \GuzzleHttp\Client();
        try {
            $secondResponse = $client->request('POST', $urlTwo, [
                'body' => '{
                "userName":"aliceuser",
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
            $statusCode = $secondResponse->getStatusCode();
        } catch (GuzzleException $e) {
            $statusCode = $e->getCode();
        }
        self::assertEquals(400, $statusCode);
    }

    public function testCreateUserWithBadEmailFails(): void
    {
        $firstResponseObj = $this->getSuperUserAuthenticationResponseObject();
        $urlTwo = $_ENV['HOST_STRING'].self::CREATE_USER_RELATIVE_URL;
        $client = new \GuzzleHttp\Client();
        try {
            $secondResponse = $client->request(
                'POST',
                $urlTwo,
                [
                    'body' => '{
                    "username":"timuser"
                    "email":"timuser@example",
                    "plainPassword":"password",
                    "enabled":true
                }',
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer '.$firstResponseObj->token,
                    ],
                ]
            );
            $secondResponseObj = json_decode(
                $secondResponse->getBody()->getContents()
            );
            $statusCode = $secondResponse->getStatusCode();
        } catch (GuzzleException $e) {
            $statusCode = $e->getCode();
        }
        self::assertEquals(400, $statusCode);
    }

    public function testCreateUserWithOutPasswordFails(): void
    {
        $firstResponseObj = $this->getSuperUserAuthenticationResponseObject();
        $urlTwo = $_ENV['HOST_STRING'].self::CREATE_USER_RELATIVE_URL;
        $client = new \GuzzleHttp\Client();
        try {
            $secondResponse = $client->request('POST', $urlTwo, [
                'body' => '{
                "userName":"januser",
                "email":"januser@example.com"
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
            $statusCode = $secondResponse->getStatusCode();
        } catch (GuzzleException $e) {
            $statusCode = $e->getCode();
        }
        self::assertEquals(400, $statusCode);
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
            $statusCode = $e->getCode();
        }
        self::assertEquals(403, $statusCode);
    }
}
