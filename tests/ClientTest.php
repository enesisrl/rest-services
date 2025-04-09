<?php

namespace Enesisrl\RestServices\Tests;

use Enesisrl\RestServices\Client;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    private $client;
    private $mockHandler;

    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $httpClient = new HttpClient(['handler' => $handlerStack]);

        $this->client = new Client();
        // Inietta il client HTTP mockato
        $this->setPrivateProperty($this->client, 'client', $httpClient);
    }

    public function testSetDebugChangesBaseUri()
    {
        $this->client->setDebug(true);
        $this->assertEquals('https://rest2.enesi.vm', $this->getPrivateProperty($this->client, 'baseUri'));

        $this->client->setDebug(false);
        $this->assertEquals('https://rest2.ene.si', $this->getPrivateProperty($this->client, 'baseUri'));
    }

    public function testLoginSuccess()
    {
        // Mock prima risposta con WWW-Authenticate header
        $this->mockHandler->append(
            new GuzzleResponse(401, [
                'WWW-Authenticate' => ['Digest realm="test", nonce="123"']
            ])
        );

        // Mock seconda risposta con successo
        $this->mockHandler->append(
            new GuzzleResponse(200, [], '{"status": "success"}')
        );

        $response = $this->client->login('user', 'pass');
        $this->assertEquals('success', $response->status);
    }

    private function setPrivateProperty($object, $propertyName, $value)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

    private function getPrivateProperty($object, $propertyName)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        return $property->getValue($object);
    }
}