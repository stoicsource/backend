<?php

namespace App\Tests\integration\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class TocEntryTest extends ApiTestCase
{
    public function testGetSpecificChapter(): void
    {
        $response = static::createClient()->request('GET', '/api/v2/toc_entries?work=2',
            ['headers' => ['Accept' => 'application/json']]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json; charset=utf-8');
        $this->assertResponseHeaderSame('cache-control', 'max-age=86400, public');

        $this->assertJsonContains([
            [
                'id' => 567,
                'work' => '/api/v2/works/2',
                'label' => '1.01',
                'sortOrder' => 567,
            ]
        ]);

        $this->assertStringNotContainsString('chapters', $response->getContent());
    }

}