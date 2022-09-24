<?php

namespace App\Tests\Entity\Import;

use App\Entity\Import\ExtractedChapter;
use App\Repository\BasicFootnoteRepository;
use App\Service\Import\FootnoteReferenceCollector;
use PHPUnit\Framework\TestCase;

class ExtractedChapterTest extends TestCase
{
    public function testCanSetTitle()
    {
        $chapter = new ExtractedChapter();
        $chapter->setTitle('test');
        $this->assertEquals('test', $chapter->getTitle());
    }

    public function testCanSetContent()
    {
        $chapter = new ExtractedChapter();
        $chapter->setContent('test');
        $this->assertEquals('test', $chapter->getContent());
    }

    public function testExtractFootnotes()
    {
        $chapter = new ExtractedChapter();
        $chapter->setContent('<p>test<a data-footnote="3">3</a></p>');
        $chapter->setFootnoteTag('a');
        $chapter->setFootnoteAttribute('data-footnote');

        $collector = new FootnoteReferenceCollector();
        $footnoteRepo = new BasicFootnoteRepository();
        $footnoteRepo->addNote('3', 'test note');

        $chapter->extractFootnotes($collector, $footnoteRepo);

        $this->assertCount(1, $chapter->getFootnotes());
        $this->assertArrayHasKey(3, $chapter->getFootnotes());
        $this->assertEquals('test note', $chapter->getFootnotes()[3]);
    }
}
