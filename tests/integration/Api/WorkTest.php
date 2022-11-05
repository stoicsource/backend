<?php

namespace App\Tests\integration\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class WorkTest extends ApiTestCase
{
    public function testGetSpecificChapter(): void
    {
        $response = static::createClient()->request('GET', '/api/v2/works',
            ['headers' => ['Accept' => 'application/json']]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json; charset=utf-8');
        $this->assertResponseHeaderSame('cache-control', 'max-age=86400, public');

        $this->assertJsonContains([
            [
                'id' => 1,
                'name' => 'The Enchirideon',
                'editions' => [],
                'urlSlug' => 'enchirideon',
                'author' => '/api/v2/authors/218',
            ]
        ]);

        $this->assertStringNotContainsString('tocEntries', $response->getContent());
    }

}