<?php declare(strict_types=1);

namespace App\Tests\Acceptance;

use GuzzleHttp\Exception\GuzzleException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Dotenv\Dotenv;

abstract class AbstractUserTest extends WebTestCase
{
    const LOGIN_RELATIVE_URL = '/api/login_check';

    /**
     * @return object
     * @throws GuzzleException
     */
    protected function getSuperUserAuthenticationResponseObject(): object
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

    /**
     * TODO: Move this method to an abstract parent class.
     *
     * @return object
     * @throws GuzzleException
     */
    protected function getNormalUserAuthenticationResponseObject(): object
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
                'body' => '{"username":"normaluser","password":"password"}',
                'headers' => ['Content-Type' => 'application/json']
            ]
        );
        $firstResponseObj = json_decode(
            $firstResponse->getBody()->getContents()
        );

        return $firstResponseObj;
    }
}