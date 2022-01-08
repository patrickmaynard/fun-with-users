<?php declare(strict_types=1);

namespace App\Tests\Acceptance;

use GuzzleHttp\Exception\GuzzleException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Dotenv\Dotenv;

class ReadUserTest extends WebTestCase
{
    const LOGIN_RELATIVE_URL = '/api/login_check';
    const LIST_USERS_RELATIVE_URL = '/api/users';

    public function testGetAllUsersWithAdminSucceeds(): void
    {
        $firstResponseObj = $this->getSuperUserAuthenticationResponseObject();
        $urlTwo = $_ENV['HOST_STRING'].self::LIST_USERS_RELATIVE_URL;
        $client = new \GuzzleHttp\Client();
        $secondResponse = $client->request('GET', $urlTwo, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $firstResponseObj->token
            ]
        ]);

        $secondResponseArray = json_decode(
            $secondResponse->getBody()->getContents(),
            true
        );

        self::assertEquals(201, $secondResponse->getStatusCode());
        self::assertIsArray($secondResponseArray);
        self::assertCount(3, $secondResponseArray);
        self::assertEquals(
            'bobuser',
            $secondResponseArray[2]['username']
        );
        self::assertEquals(
            'bobuser@example.com',
            $secondResponseArray[2]['email']
        );
        self::assertEquals(
            [0 => 'ROLE_USER'],
            $secondResponseArray[2]['roles']
        );
        self::assertEquals(
            true,
            $secondResponseArray[2]['accountNonExpired']
        );
        self::assertEquals(
            true,
            $secondResponseArray[2]['accountNonLocked']
        );
        self::assertEquals(
            true,
            $secondResponseArray[2]['credentialsNonExpired']
        );
        //dd($secondResponseArray);

    }

    function testGetAllUsersWithNonAdminFails(): void
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
        $urlTwo = $_ENV['HOST_STRING'] . self::LIST_USERS_RELATIVE_URL;
        $firstResponseObj = json_decode(
            $firstResponse->getBody()->getContents()
        );
        try {
            $secondResponse = $client->request(
                'GET',
                $urlTwo,
                [
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
        //TODO: Build the endpoint to show all users and the test.

        self::assertTrue(true);
    }

    /**
     * TODO: Move this method to an abstract parent class.
     *       (It's currently redefined in both this and CreateUserTest.)
     *
     * @return object
     * @throws GuzzleException
     */
    private function getSuperUserAuthenticationResponseObject(): object
    {
        $dotEnv = new Dotenv();
        $dotEnv->overload(__DIR__.'/../../.env');
        if (file_exists(__DIR__.'/../../.env.local')) {
            $dotEnv->overload(__DIR__.'/../../.env.local');
        }
        $dotEnv->overload(__DIR__.'/../../.env.test');
        $urlOne = $_ENV['HOST_STRING'].self::LOGIN_RELATIVE_URL;
        $client = new \GuzzleHttp\Client();
        $firstResponse = $client->request(
            'POST',
            $urlOne,
            [
                'body' => '{"username":"superuser","password":"password"}',
                'headers' => ['Content-Type' => 'application/json']
            ]
        );
        $firstResponseObj = json_decode(
            $firstResponse->getBody()->getContents()
        );

        return $firstResponseObj;
    }
}