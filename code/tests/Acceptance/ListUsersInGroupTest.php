<?php declare(strict_types=1);

namespace App\Tests;

use App\Tests\Acceptance\AbstractUserTest;

class ListUsersInGroupTest extends AbstractUserTest
{
    const TEST_GROUP_LIST_URL = '/api/user-group/test-group-one/users';
    const TEST_GROUP_ADD_USER_URL = '/api/user-group/test-group-one/normaluser';

    public function testListUsersInGroupEmpty() : void
    {
        $firstResponseObj = $this->getSuperUserAuthenticationResponseObject();
        $urlTwo = $_ENV['HOST_STRING'].self::TEST_GROUP_LIST_URL;
        $client = new \GuzzleHttp\Client();
        $secondResponse = $client->request('GET', $urlTwo, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $firstResponseObj->token
            ]
        ]);
        self::assertEquals(
            '[]', $secondResponse->getBody()->getContents()
        );
    }

    public function testListUsersInGroupFilled() : void
    {
        $firstResponseObj = $this->getSuperUserAuthenticationResponseObject();
        $urlTwo = $_ENV['HOST_STRING'].self::TEST_GROUP_ADD_USER_URL;
        $client = new \GuzzleHttp\Client();
        $secondResponse = $client->request('LINK', $urlTwo, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $firstResponseObj->token
            ]
        ]);
        $urlThree = $_ENV['HOST_STRING'].self::TEST_GROUP_LIST_URL;
        $thirdResponse = $client->request('GET', $urlThree, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $firstResponseObj->token
            ]
        ]);
        $thirdResponseArray = json_decode(
            $thirdResponse->getBody()->getContents(), true
        );
        self::assertEquals(
            200, $thirdResponse->getStatusCode()
        );
        self::assertEquals(
            'normaluser',
            $thirdResponseArray[0]['username']
        );
        self::assertEquals(
            'normaluser@example.com',
            $thirdResponseArray[0]['email']
        );
        self::assertEquals(
            'test-group-one',
            $thirdResponseArray[0]['groups'][0]['name']
        );


        //Clean up
        $secondResponse = $client->request('UNLINK', $urlTwo, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $firstResponseObj->token
            ]
        ]);
    }
}