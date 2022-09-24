<?php

namespace App\Tests\Service\Import;

use App\Entity\Content;
use App\Entity\Import\ExtractedChapter;
use App\Repository\BasicFootnoteRepository;
use App\Service\Import\ChapterConverter;
use App\Service\Import\FootnoteReferenceCollector;
use App\Service\Import\NodeConverter;
use PHPUnit\Framework\TestCase;

class ChapterConverterTest extends TestCase
{
    public function testConvertsTitle()
    {
        $extractedChapter = $this->getExtractedChapter('test title', '');

        $converter = new ChapterConverter(new NodeConverter());
        $contentEntity = $converter->convert($extractedChapter);
        assert($contentEntity instanceof Content);
        $this->assertEquals('<p>test title</p>', $contentEntity->getTitle());
    }

    public function testConvertsContent()
    {
        $extractedChapter = $this->getExtractedChapter('', 'test content');

        $converter = new ChapterConverter(new NodeConverter());
        $contentEntity = $converter->convert($extractedChapter);
        assert($contentEntity instanceof Content);

        $this->assertEquals('<p>test content</p>', $contentEntity->getContent());
    }

    public function testReplacesNoteTags()
    {
        $extractedChapter = $this->getExtractedChapter(
            '<p>test <a data-ref="1">1</a> title</p>',
            '<p>test <a data-ref="2">2</a> content</p>',
        'a',
            'data-ref'
        );

        $footnoteRepo = $this->getFootnoteRepo([1 => 'test', 2 => 'test']);
        $extractedChapter->extractFootnotes(new FootnoteReferenceCollector(), $footnoteRepo);

        $converter = new ChapterConverter(new NodeConverter());
        $converter->setTargetNoteTag('sup');
        $converter->setTargetNoteAttribute('data-note-reference');
        $contentEntity = $converter->convert($extractedChapter);

        $this->assertEquals('<p>test <sup data-note-reference="1">1</sup> title</p>', $contentEntity->getTitle());
        $this->assertEquals('<p>test <sup data-note-reference="2">2</sup> content</p>', $contentEntity->getContent());
    }

    public function testRenumbersNotes()
    {
        $extractedChapter = $this->getExtractedChapter(
            '<p>test <a data-ref="8">8</a> title</p>',
            '<p>test <a data-ref="12">12</a> content</p>',
        'a',
            'data-ref'
        );

        $footnoteRepo = $this->getFootnoteRepo([8 => 'test', 12 => 'test']);
        $extractedChapter->extractFootnotes(new FootnoteReferenceCollector(), $footnoteRepo);

        $converter = new ChapterConverter(new NodeConverter());
        $converter->setTargetNoteTag('sup');
        $converter->setTargetNoteAttribute('data-note-reference');
        $contentEntity = $converter->convert($extractedChapter);

        $this->assertEquals('<p>test <sup data-note-reference="1">1</sup> title</p>', $contentEntity->getTitle());
        $this->assertEquals('<p>test <sup data-note-reference="2">2</sup> content</p>', $contentEntity->getContent());
    }

    public function testExportsNotes()
    {
        $extractedChapter = $this->getExtractedChapter(
            '<p>test <a data-ref="8">8</a> title</p>',
            '<p>test <a data-ref="12">12</a> content</p>',
        'a',
            'data-ref'
        );

        $footnoteRepo = $this->getFootnoteRepo([8 => 'test', 12 => 'test']);
        $extractedChapter->extractFootnotes(new FootnoteReferenceCollector(), $footnoteRepo);

        $converter = new ChapterConverter(new NodeConverter());
        $converter->setTargetNoteTag('sup');
        $converter->setTargetNoteAttribute('data-note-reference');
        $contentEntity = $converter->convert($extractedChapter);

        $this->assertNotEmpty($contentEntity->getNotes());
    }

    private function getExtractedChapter($title, $content, $footnoteTag = 'irrelevant',$footnoteAttribute = 'irrelevant'): ExtractedChapter
    {
        $extractedChapter = new ExtractedChapter();
        $extractedChapter->setTitle($title);
        $extractedChapter->setContent($content);
        $extractedChapter->setFootnoteTag($footnoteTag);
        $extractedChapter->setFootnoteAttribute($footnoteAttribute);
        return $extractedChapter;
    }

    private function getFootnoteRepo(array $notes = null): BasicFootnoteRepository
    {
        $footnoteRepo = new BasicFootnoteRepository();
        if ($notes) {
            foreach ($notes as $index => $noteContent) {
                $footnoteRepo->addNote($index, $noteContent);
            }
        }
        return $footnoteRepo;
    }
}
