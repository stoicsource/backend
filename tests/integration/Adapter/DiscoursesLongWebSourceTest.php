<?php

namespace App\Tests\integration\Adapter;

use App\Adapter\DiscoursesLongWebSource;
use App\Dto\ChapterDto;
use App\Service\Import\HtmlCleaner;
use App\Service\Import\NodeConverter;
use PHPUnit\Framework\TestCase;

class DiscoursesLongWebSourceTest extends TestCase
{
//    public function testGetChapters()
//    {
//        $adapter = $this->getEditionSource();
//
//        $this->assertCount(95,  iterator_to_array($adapter->getChapters('https://standardebooks.org/ebooks/epictetus/discourses/george-long/text/single-page')));
//    }
//
//    public function testFirstChapterContent()
//    {
//        $adapter = $this->getEditionSource();
//
//        $firstChapter = $adapter->getChapters('https://standardebooks.org/ebooks/epictetus/discourses/george-long/text/single-page')->current();
//        assert($firstChapter instanceof ChapterDto);
//        $this->assertEquals('Of the Things Which Are in Our Power, and Not in Our Power', $firstChapter->getTitle());
//        $this->assertStringStartsWith('<p>Of all the faculties (except that which I shall soon mention)', $firstChapter->getContent());
//        $this->assertStringEndsWith('Like a man who gives up<sup data-footnote-reference="15">15</sup> what belongs to another.</p>', $firstChapter->getContent());
//        $this->assertCount(15, $firstChapter->getFootnotes());
//        $this->assertEquals('1.1', $firstChapter->getTocLabel());
//    }

    public function testAuthorName()
    {
        $adapter = $this->getEditionSource();
        $this->assertEquals('George Long', $adapter->getEdition()->getAuthorName());

    }

    private function getEditionSource(): DiscoursesLongWebSource
    {
        return new DiscoursesLongWebSource(
            new NodeConverter(),
            new HtmlCleaner()
        );
    }
}
