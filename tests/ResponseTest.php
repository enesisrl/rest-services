<?php

namespace Enesisrl\RestServices\Tests;

use Enesisrl\RestServices\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    public function testCanCreateFromJson()
    {
        $json = '{"name": "test", "value": 123}';
        $response = new Response($json);

        $this->assertEquals('test', $response->name);
        $this->assertEquals(123, $response->value);
    }

    public function testCanCreateFromArray()
    {
        $data = ['name' => 'test', 'value' => 123];
        $response = new Response($data);

        $this->assertEquals('test', $response->name);
        $this->assertEquals(123, $response->value);
    }

    public function testReturnsNullForNonExistentProperty()
    {
        $response = new Response([]);

        $this->assertNull($response->nonexistent);
    }
}