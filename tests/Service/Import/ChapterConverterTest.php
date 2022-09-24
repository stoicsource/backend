<?php

namespace App\Tests\Service\Import;

use App\Entity\Content;
use App\Entity\Import\ExtractedChapter;
use App\Repository\BasicFootnoteRepository;
use App\Service\Import\ChapterConverter;
use App\Service\Import\FootnoteReferenceCollector;
use App\Service\Import\HtmlCleaner;
use App\Service\Import\NodeConverter;
use PHPUnit\Framework\TestCase;

class ChapterConverterTest extends TestCase
{
    public function testConvertsTitle()
    {
        $extractedChapter = $this->getExtractedChapter('test title', '');

        $converter = $this->getChapterConverter();
        $contentEntity = $converter->convert($extractedChapter);
        assert($contentEntity instanceof Content);
        $this->assertEquals('test title', $contentEntity->getTitle());
    }

    public function testConvertsContent()
    {
        $extractedChapter = $this->getExtractedChapter('', 'test content');

        $converter = $this->getChapterConverter();
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

        $converter = $this->getChapterConverter();
        $converter->setTargetNoteTag('sup');
        $converter->setTargetNoteAttribute('data-footnote-reference');
        $contentEntity = $converter->convert($extractedChapter);

        $this->assertEquals('test <sup data-footnote-reference="1">1</sup> title', $contentEntity->getTitle());
        $this->assertEquals('<p>test <sup data-footnote-reference="2">2</sup> content</p>', $contentEntity->getContent());
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

        $converter = $this->getChapterConverter();
        $converter->setTargetNoteTag('sup');
        $converter->setTargetNoteAttribute('data-footnote-reference');
        $contentEntity = $converter->convert($extractedChapter);

        $this->assertEquals('test <sup data-footnote-reference="1">1</sup> title', $contentEntity->getTitle());
        $this->assertEquals('<p>test <sup data-footnote-reference="2">2</sup> content</p>', $contentEntity->getContent());
    }

    public function testExportsNotes()
    {
        $extractedChapter = $this->getExtractedChapter(
            '<p>test <a data-ref="8">8</a> title</p>',
            '<p>test <a data-ref="12">12</a> content</p>',
        'a',
            'data-ref'
        );

        $footnoteRepo = $this->getFootnoteRepo([8 => 'test first', 12 => 'test second']);
        $extractedChapter->extractFootnotes(new FootnoteReferenceCollector(), $footnoteRepo);

        $converter = $this->getChapterConverter();
        $converter->setTargetNoteTag('sup');
        $converter->setTargetNoteAttribute('data-note-reference');
        $contentEntity = $converter->convert($extractedChapter);

        $this->assertNotEmpty($contentEntity->getNotes());
        $expectedNotes = json_encode([
            [
                'id' => 1,
                'content' => 'test first'
            ],
            [
                'id' => 2,
                'content' => 'test second'
            ]
        ]);
        $this->assertEquals($expectedNotes, $contentEntity->getNotes());
    }

    public function testRemovesUnwantedTags()
    {
        $extractedChapter = $this->getExtractedChapter('<h4>test</h4>', '<p>test <small>content</small></p>');

        $converter = $this->getChapterConverter();
        $contentEntity = $converter->convert($extractedChapter);

        $this->assertEquals('test', $contentEntity->getTitle());
        $this->assertEquals('<p>test content</p>', $contentEntity->getContent());
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

    private function getChapterConverter(): ChapterConverter
    {
        $htmlCleaner = new HtmlCleaner();
        $chapterConverter = new ChapterConverter(new NodeConverter(), $htmlCleaner);
        $chapterConverter->setAllowedTagsAndAttributesTitle(['sup' => 'data-footnote-reference']);
        $chapterConverter->setAllowedTagsAndAttributesContent(Content::ALLOWED_HTML_TAGS_AND_ATTRIBUTES);
        return $chapterConverter;
    }
}
