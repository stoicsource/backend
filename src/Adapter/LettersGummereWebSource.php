<?php

namespace App\Adapter;

use App\Dto\ChapterDto;
use App\Dto\EditionDto;
use App\Entity\Chapter;
use App\Service\Import\HtmlCleaner;
use App\Service\Import\NodeConverter;
use DOMDocument;
use DOMElement;
use DOMXPath;

class LettersGummereWebSource implements EditionWebSourceInterface
{
    public function __construct(
        private readonly NodeConverter $nodeConverter,
        private readonly HtmlCleaner   $htmlCleaner
    )
    {
    }

    public function getChapters(string $sourceUrl): iterable
    {
        $baseUrl = str_replace('1', '', $sourceUrl);

        for ($letterNr = 1; $letterNr <= 124; $letterNr++) {
            $chapter = $this->getChapter($baseUrl, $letterNr);

            yield $chapter;

            // on prod this needs sleep
            usleep(100000);
        }
    }

    public function getEdition(): EditionDto
    {
        return new EditionDto(
            'Moral letters to Lucilius',
            1925,
            'Richard Mott Gummere',
            'Moral Letters to Lucilius',
            [
                [
                    "url" => "https://en.wikisource.org/wiki/Moral_letters_to_Lucilius/Letter_1",
                    "type" => "text"
                ]
            ]
        );
    }

    public function getChapter(string $baseUrl, int $letterNr): ChapterDto
    {
        $nodeValueConverter = function ($nodeValue) {
            return str_replace(['[', ']'], ['', ''], $nodeValue);
        };
        $attributeValueConverter = function ($nodeValue) {
            return str_replace('cite_ref-', '', $nodeValue);
        };

        $doc = new DOMDocument();
        @$doc->loadHTMLFile($baseUrl . $letterNr);
        $x = new DOMXPath($doc);

        $chapter = new ChapterDto();
        $chapter->setTocLabel($letterNr);

        $headNodes = $x->query("//h2/span[@class='mw-headline' and not(@id='Footnotes')]");

        $titleNode = $headNodes[0];
        assert($titleNode instanceof DOMElement);

        $this->nodeConverter->flattenTag($titleNode, 'a', $nodeValueConverter);
        $this->nodeConverter->convertAttributes($titleNode, 'sup', 'id', Chapter::FOOTNOTE_REFERENCE_ID_ATTRIBUTE, $attributeValueConverter);
        $titleHtml = $titleNode->ownerDocument->saveHTML($titleNode);
        $titleHtml = $this->htmlCleaner->clean($titleHtml, [Chapter::FOOTNOTE_REFERENCE_TAG => [Chapter::FOOTNOTE_REFERENCE_ID_ATTRIBUTE]]);
        $titleHtml = substr($titleHtml, strpos($titleHtml, '. ') + 2);
        $chapter->setTitle($titleHtml);


        // Content of the chapter
        $combinedContentHtml = '';
        $contentNodes = $x->query("//div[@id='mw-content-text']//div/p | //div[@id='mw-content-text']//div/blockquote");
        foreach ($contentNodes as $contentNode) {
            assert($contentNode instanceof DOMElement);

            $this->nodeConverter->flattenTag($contentNode, 'a', $nodeValueConverter);
            $this->nodeConverter->convertAttributes($contentNode, 'sup', 'id', Chapter::FOOTNOTE_REFERENCE_ID_ATTRIBUTE, $attributeValueConverter);
            $nodeHtml = $contentNode->ownerDocument->saveHTML($contentNode);
            $nodeHtml = str_replace("\n", '', $nodeHtml);
            $combinedContentHtml .= $nodeHtml;
        }

        $combinedContentHtml = str_replace('  ', ' ', $combinedContentHtml);
        $combinedContentHtml = $this->htmlCleaner->clean($combinedContentHtml, Chapter::ALLOWED_HTML_TAGS_AND_ATTRIBUTES);
        $chapter->setContent($combinedContentHtml);


        $footnotes = [];
        $x = new DOMXPath($doc);
        $footnoteElements = $x->query("//div[@class='reflist']//li");
        foreach ($footnoteElements as $footnoteElement) {
            assert($footnoteElement instanceof DOMElement);
            $elementId = $footnoteElement->getAttribute('id');
            $globalNoteId = explode('-', $elementId)[1];

            $footnoteText = $doc->saveHTML($footnoteElement);
            $footnoteText = $this->htmlCleaner->clean($footnoteText, Chapter::ALLOWED_HTML_TAGS_AND_ATTRIBUTES);
            $footnoteText = str_replace('↑ ', '', $footnoteText);

            $footnotes[$globalNoteId] = $footnoteText;
        }
        $chapter->setFootnotes($footnotes);

        return $chapter;
    }
}