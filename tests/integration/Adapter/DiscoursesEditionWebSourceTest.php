<?php

namespace App\Tests\integration\Adapter;

use App\Adapter\DiscoursesEditionWebSource;
use App\Dto\ChapterDto;
use App\Service\Import\HtmlCleaner;
use App\Service\Import\NodeConverter;
use PHPUnit\Framework\TestCase;

class DiscoursesEditionWebSourceTest extends TestCase
{
    /*
     * tests to write:
     * - chapter has footnotes
     * - footnotes are numbered from 1 to n
     */

    public function testGetChapters()
    {
        $adapter = $this->getEditionSource();

        $this->assertCount(95,  iterator_to_array($adapter->getChapters('https://standardebooks.org/ebooks/epictetus/discourses/george-long/text/single-page')));
    }

    public function testFirstChapterContent()
    {
        $adapter = $this->getEditionSource();

        $firstChapter = $adapter->getChapters('https://standardebooks.org/ebooks/epictetus/discourses/george-long/text/single-page')->current();
        assert($firstChapter instanceof ChapterDto);
        $this->assertEquals('Of the Things Which Are in Our Power, and Not in Our Power', $firstChapter->getTitle());
        $this->assertStringStartsWith('<p>Of all the faculties (except that which I shall soon mention)', $firstChapter->getContent());
        $this->assertStringEndsWith('Like a man who gives up<sup data-footnote-reference="15">15</sup> what belongs to another.</p>', $firstChapter->getContent());
        $this->assertEquals('1.1', $firstChapter->getTocLabel());
    }

//    public function testExportsNotes()
//    {
//        $extractedChapter = $this->getChapterDto(
//            '<p>test <a data-ref="8">8</a> title</p>',
//            '<p>test <a data-ref="12">12</a> content</p>',
//            'a',
//            'data-ref'
//        );
//
//        $footnoteRepo = $this->getFootnoteRepo([8 => 'test first', 12 => 'test second']);
//        $extractedChapter->extractFootnotes(new FootnoteReferenceCollector(), $footnoteRepo);
//
//        $converter = $this->getChapterConverter();
//        $converter->setTargetNoteTag('sup');
//        $converter->setTargetNoteAttribute('data-note-reference');
//        $contentEntity = $converter->convert($extractedChapter);
//
//        $this->assertNotEmpty($contentEntity->getNotes());
//        $expectedNotes = json_encode([
//            [
//                'id' => 1,
//                'content' => 'test first'
//            ],
//            [
//                'id' => 2,
//                'content' => 'test second'
//            ]
//        ]);
//        $this->assertEquals($expectedNotes, $contentEntity->getNotes());
//    }

//    public function testReplacesNoteTags()
//    {
//        $extractedChapter = $this->getChapterDto(
//            '<p>test <a data-ref="1">1</a> title</p>',
//            '<p>test <a data-ref="2">2</a> content</p>',
//            'a',
//            'data-ref'
//        );
//
//        $footnoteRepo = $this->getFootnoteRepo([1 => 'test', 2 => 'test']);
//        $extractedChapter->extractFootnotes(new FootnoteReferenceCollector(), $footnoteRepo);
//
//        $converter = $this->getChapterConverter();
//        $converter->setTargetNoteTag('sup');
//        $converter->setTargetNoteAttribute('data-footnote-reference');
//        $contentEntity = $converter->convert($extractedChapter);
//
//        $this->assertEquals('test <sup data-footnote-reference="1">1</sup> title', $contentEntity->getTitle());
//        $this->assertEquals('<p>test <sup data-footnote-reference="2">2</sup> content</p>', $contentEntity->getContent());
//    }
//
//    public function testRenumbersNotes()
//    {
//        $extractedChapter = $this->getChapterDto(
//            '<p>test <a data-ref="8">8</a> title</p>',
//            '<p>test <a data-ref="12">12</a> content</p>',
//            'a',
//            'data-ref'
//        );
//
//        $footnoteRepo = $this->getFootnoteRepo([8 => 'test', 12 => 'test']);
//        $extractedChapter->extractFootnotes(new FootnoteReferenceCollector(), $footnoteRepo);
//
//        $converter = $this->getChapterConverter();
//        $converter->setTargetNoteTag('sup');
//        $converter->setTargetNoteAttribute('data-footnote-reference');
//        $contentEntity = $converter->convert($extractedChapter);
//
//        $this->assertEquals('test <sup data-footnote-reference="1">1</sup> title', $contentEntity->getTitle());
//        $this->assertEquals('<p>test <sup data-footnote-reference="2">2</sup> content</p>', $contentEntity->getContent());
//    }

    private function getEditionSource(): DiscoursesEditionWebSource
    {
        return new DiscoursesEditionWebSource(
            new NodeConverter(),
            new HtmlCleaner()
        );
    }
}
