<?php

namespace App\Tests\integration\Adapter;

use App\Adapter\LettersGummereWebSource;
use App\Dto\ChapterDto;
use App\Service\Import\HtmlCleaner;
use App\Service\Import\NodeConverter;
use PHPUnit\Framework\TestCase;

class LettersGummereWebSourceTest extends TestCase
{
//    public function testGetChapters()
//    {
//        $adapter = $this->getEditionSource();
//
//        $this->assertCount(124,  iterator_to_array($adapter->getChapters('https://en.wikisource.org/wiki/Moral_letters_to_Lucilius/Letter_1')));
//    }
//
//    public function testFirstChapterContent()
//    {
//        $adapter = $this->getEditionSource();
//
//        $firstChapter = $adapter->getChapters('https://en.wikisource.org/wiki/Moral_letters_to_Lucilius/Letter_1')->current();
//        assert($firstChapter instanceof ChapterDto);
//        $this->assertEquals('On Saving Time', $firstChapter->getTitle());
//        $this->assertStringStartsWith('<p>Greetings from Seneca to his friend Lucilius. </p><p><b>1.</b> Continue to act thus, my dear Lucilius', $firstChapter->getContent());
//        $this->assertStringEndsWith('the amount is slight, and the quality is vile. Farewell.</p>', $firstChapter->getContent());
//        $this->assertCount(1, $firstChapter->getFootnotes());
//        $this->assertEquals('1', $firstChapter->getTocLabel());
//    }

//    public function testImportsBlockquotes()
//    {
//        $adapter = $this->getEditionSource();
//
//        $chapter = $adapter->getChapter('https://en.wikisource.org/wiki/Moral_letters_to_Lucilius/Letter_', 122);
//    }

    public function testAuthorName()
    {
        $adapter = $this->getEditionSource();
        $this->assertEquals('Richard Mott Gummere', $adapter->getEdition()->getAuthorName());

    }

    private function getEditionSource(): LettersGummereWebSource
    {
        return new LettersGummereWebSource(
            new NodeConverter(),
            new HtmlCleaner()
        );
    }
}
