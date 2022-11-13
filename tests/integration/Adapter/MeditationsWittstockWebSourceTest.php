<?php

namespace App\Tests\integration\Adapter;

use App\Adapter\LettersGummereWebSource;
use App\Adapter\MeditationsWittstockWebSource;
use App\Dto\ChapterDto;
use App\Service\Import\HtmlCleaner;
use App\Service\Import\NodeConverter;
use PHPUnit\Framework\TestCase;

class MeditationsWittstockWebSourceTest extends TestCase
{
    private $sourceUrl;

    public function setUp(): void
    {
        // $this->sourceUrl = 'https://www.projekt-gutenberg.org/antonius/selbstbe/chap001.html';
        $this->sourceUrl = 'https://www.stoicsource.com/medwitt/chap001.html';
    }

    public function testEditionInfo()
    {
        $adapter = $this->getEditionSource();
        $editionDto = $adapter->getEdition();
        $this->assertEquals('Albert Friedrich Wittstock', $editionDto->getAuthorName());
        $this->assertEquals('Des Kaisers Marcus Aurelius Antonius Selbstbetrachtungen', $editionDto->getName());
        $this->assertEquals('The Meditations', $editionDto->getWorkName());
        $this->assertEquals('1894', $editionDto->getYear());
        $this->assertEquals('deu', $editionDto->getLanguage());
        $this->assertCount(1, $editionDto->getSources());

    }

    public function testChapterCountInBook()
    {
        $adapter = $this->getEditionSource();

        $this->assertCount(17,  iterator_to_array($adapter->getBookChapters($this->sourceUrl, 1)));
        $this->assertCount(17,  iterator_to_array($adapter->getBookChapters($this->sourceUrl, 2)));
        $this->assertCount(16,  iterator_to_array($adapter->getBookChapters($this->sourceUrl, 3)));
        $this->assertCount(51,  iterator_to_array($adapter->getBookChapters($this->sourceUrl, 4)));
        $this->assertCount(36,  iterator_to_array($adapter->getBookChapters($this->sourceUrl, 5)));
        $this->assertCount(59,  iterator_to_array($adapter->getBookChapters($this->sourceUrl, 6)));
        $this->assertCount(75,  iterator_to_array($adapter->getBookChapters($this->sourceUrl, 7)));
        $this->assertCount(61,  iterator_to_array($adapter->getBookChapters($this->sourceUrl, 8)));
        $this->assertCount(42,  iterator_to_array($adapter->getBookChapters($this->sourceUrl, 9)));
        $this->assertCount(38,  iterator_to_array($adapter->getBookChapters($this->sourceUrl, 10)));
        $this->assertCount(39,  iterator_to_array($adapter->getBookChapters($this->sourceUrl, 11)));
        $this->assertCount(36,  iterator_to_array($adapter->getBookChapters($this->sourceUrl, 12)));
    }

    public function testChapterCountTotal()
    {
        $adapter = $this->getEditionSource();

        $this->assertEquals(487,   iterator_count($adapter->getChapters($this->sourceUrl)));
    }

    public function testFirstBookFirstChapterContent()
    {
        $adapter = $this->getEditionSource();

        $firstChapter = $adapter->getBookChapters($this->sourceUrl, 1)->current();
        assert($firstChapter instanceof ChapterDto);
        $this->assertEmpty($firstChapter->getTitle());
        $this->assertEquals('1.01', $firstChapter->getTocLabel());
        $this->assertEquals('<p>Mein Großvater Verus<sup data-footnote-reference="1">1</sup> gab mir das Beispiel der Milde und Gelassenheit.</p>', $firstChapter->getContent());
        $this->assertCount(1, $firstChapter->getFootnotes());
        $this->assertEquals('Der Kaiser gedenkt zuerst seines Großvaters, weil er in dessen Hause erzogen worden war. Annius Verus, ein römischer Senator, war dreimal Konsul gewesen.', $firstChapter->getFootnotes()[1]);
    }

    public function testMultipleFootnotes()
    {
        $adapter = $this->getEditionSource();

        $chapters = iterator_to_array($adapter->getBookChapters($this->sourceUrl, 1));
        $lastChapter = $chapters[count($chapters) - 1];
        assert($lastChapter instanceof ChapterDto);
        $this->assertCount(6, $lastChapter->getFootnotes());
        $this->assertEquals('wenn er seinen Adoptivbruder L. Verus meint, so ist das Urteil allzu günstig', $lastChapter->getFootnotes()[2]);
        $this->assertEquals('Vgl. No. 7, 8, 15.', $lastChapter->getFootnotes()[5]);
    }

    private function getEditionSource(): MeditationsWittstockWebSource
    {
        return new MeditationsWittstockWebSource(
            new NodeConverter(),
            new HtmlCleaner()
        );
    }
}
