<?php declare(strict_types=1);

namespace App\Tests\Acceptance;

use GuzzleHttp\Exception\GuzzleException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Dotenv\Dotenv;

class ReadUserTest extends AbstractUserTest
{
    const LIST_USERS_RELATIVE_URL = '/api/users';
    const READ_USER_RELATIVE_URL = '/api/users/superuser';

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

        self::assertEquals(200, $secondResponse->getStatusCode());
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


    public function testReadUserWithAdminSucceeds(): void
    {
        $firstResponseObj = $this->getSuperUserAuthenticationResponseObject();
        $urlTwo = $_ENV['HOST_STRING'].self::READ_USER_RELATIVE_URL;
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

        self::assertEquals(200, $secondResponse->getStatusCode());
        self::assertIsArray($secondResponseArray);
        self::assertGreaterThan(18, $secondResponseArray);
        self::assertEquals(
            'superuser',
            $secondResponseArray['username']
        );
        self::assertEquals(
            'superuser@example.com',
            $secondResponseArray['email']
        );
        self::assertEquals(
            [0 => 'ROLE_SUPER_ADMIN', 1 => 'ROLE_USER'],
            $secondResponseArray['roles']
        );
        self::assertEquals(
            true,
            $secondResponseArray['accountNonExpired']
        );
        self::assertEquals(
            true,
            $secondResponseArray['accountNonLocked']
        );
        self::assertEquals(
            true,
            $secondResponseArray['credentialsNonExpired']
        );
        //dd($secondResponseArray);
    }



    function testGetAllUsersWithNonAdminFails(): void
    {
        $firstResponseObj = $this->getNormalUserAuthenticationResponseObject();

        $urlTwo = $_ENV['HOST_STRING'] . self::LIST_USERS_RELATIVE_URL;
        $client = new \GuzzleHttp\Client();
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
    }



    function testReadUserWithNonAdminFails(): void
    {
        $firstResponseObj = $this->getNormalUserAuthenticationResponseObject();

        $urlTwo = $_ENV['HOST_STRING'] . self::READ_USER_RELATIVE_URL;
        $client = new \GuzzleHttp\Client();
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
    }
}