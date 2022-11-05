<?php

namespace App\Tests\unit\Entity;

use App\Dto\ChapterDto;
use App\Entity\Chapter;
use PHPUnit\Framework\TestCase;

class ChapterTest extends TestCase
{

    public function testFromDto()
    {
        $source = new ChapterDto(
            "The Chapter's title",
            "The content to be found in this chapter",
            "4.12"
        );

        $chapter = Chapter::fromDto($source);

        $this->assertEquals($source->getTitle(), $chapter->getTitle());
        $this->assertEquals($source->getContent(), $chapter->getContent());
//        $this->assertEquals($source->getTocLabel(), $chapter->getTocEntry()->getLabel());
    }
}
