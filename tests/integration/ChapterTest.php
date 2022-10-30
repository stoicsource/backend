<?php

namespace App\Tests\integration;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class ChapterTest extends ApiTestCase
{
    public function testGetSpecificChapter(): void
    {
        static::createClient()->request('GET', '/api/v2/chapters?edition[]=92&tocEntry[]=1054',
            ['headers' => ['Accept' => 'application/json']]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json; charset=utf-8');
        $this->assertResponseHeaderSame('cache-control', 'max-age=86400, public');

        $this->assertJsonContains([
            [
                'id' => 12901,
                'tocEntry' => '/api/v2/toc_entries/1054',
                'edition' => '/api/v2/editions/92',
                'content' => 'See facts as they really are, distinguishing their matter, cause, relation.',
                'notes' => NULL,
                'title' => NULL,
                'contentType' => 'text',
                'notesFormat' => 'text'
            ]
        ]);

    }

}