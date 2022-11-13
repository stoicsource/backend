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

class MeditationsWittstockWebSource implements EditionWebSourceInterface
{
    public function __construct(
        private readonly NodeConverter $nodeConverter,
        private readonly HtmlCleaner   $htmlCleaner
    )
    {
    }

    public function getEdition(): EditionDto
    {
        return new EditionDto(
            'Des Kaisers Marcus Aurelius Antonius Selbstbetrachtungen',
            1894,
            'Albert Friedrich Wittstock',
            'The Meditations',
            EditionDto::LANG_CODE_GERMAN,
            [
                [
                    "url" => 'https://www.projekt-gutenberg.org/antonius/selbstbe/chap001.html',
                    "type" => "text"
                ]
            ]
        );
    }

    public function getChapters(string $sourceUrl): iterable
    {
        for ($bookNr = 1; $bookNr <= 12; $bookNr++) {
            yield from $this->getBookChapters($sourceUrl, $bookNr);
        }
    }

    public function getBookChapters(string $sourceUrl, int $bookNr): iterable
    {
        $bookUrl = str_replace('01', sprintf('%02d', $bookNr) , $sourceUrl);

        $doc = new DOMDocument();
        @$doc->loadHTMLFile($bookUrl);
        $xPath = new DOMXPath($doc);

        $headNodes = $xPath->query("//h3//following-sibling::h5");
        foreach ($headNodes as $index => $headNode) {
            assert($headNode instanceof DOMElement);
            $chapterNr = $index + 1;
            assert(str_contains($headNode->nodeValue, $chapterNr));

            $chapter = new ChapterDto();
            $chapter->setTocLabel(sprintf('%d.%02d', $bookNr, $chapterNr));
            $chapter->setSortOrder($bookNr * 100 + $chapterNr);

            $contentNode = $headNode->nextElementSibling;
            assert($contentNode instanceof DOMElement);
            assert($contentNode->tagName === 'p');

            $footnotes = [];
            $footnoteNr = 0;
            $footnoteNodes = $contentNode->getElementsByTagName('span');
            while (count($footnoteNodes) > 0) {
                $footnoteNode = $footnoteNodes[0];
                assert($footnoteNode instanceof DOMElement);

                $footnoteNr++;
                $nodeClass = $footnoteNode->getAttribute('class');
                assert(in_array($nodeClass, ['footnote', 'tooltip']));
                if ($nodeClass === 'footnote') {
                    $footnotes[$footnoteNr] = $footnoteNode->nodeValue;
                } else {
                    $footnotes[$footnoteNr] = $footnoteNode->getAttribute('title');

                    $fakeElement = $footnoteNode->ownerDocument->createElement('fake-tag-to-be-inlined', $footnoteNode->nodeValue);
                    $footnoteNode->parentNode->insertBefore($fakeElement, $footnoteNode);
                }

                $supElement = $footnoteNode->ownerDocument->createElement(Chapter::FOOTNOTE_REFERENCE_TAG, $footnoteNr);
                $supElement->setAttribute(Chapter::FOOTNOTE_REFERENCE_ID_ATTRIBUTE, $footnoteNr);
                $footnoteNode->parentNode->replaceChild($supElement, $footnoteNode);
            }

            $contentHtml = $contentNode->ownerDocument->saveHTML($contentNode);
            $contentHtml = $this->htmlCleaner->clean($contentHtml, Chapter::ALLOWED_HTML_TAGS_AND_ATTRIBUTES);
            $chapter->setContent($contentHtml);

            $chapter->setFootnotes($footnotes);

            yield $chapter;
        }
    }
}