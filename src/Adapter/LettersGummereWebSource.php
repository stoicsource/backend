<?php

namespace App\Adapter;

use App\Dto\ChapterDto;
use App\Dto\EditionDto;
use App\Entity\Chapter;
use App\Entity\FootnoteIdMap;
use App\Repository\BasicFootnoteRepository;
use App\Service\Import\HtmlCleaner;
use App\Service\Import\NodeConverter;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Exception;

class LettersGummereWebSource implements EditionWebSourceInterface
{
    private const sourceFootnoteTag = 'a';

    public function __construct(
        private readonly NodeConverter $nodeConverter,
        private readonly HtmlCleaner   $htmlCleaner
    )
    {
    }

    public function getChapters(string $sourceUrl): iterable
    {
        $baseUrl = str_replace('1', '', $sourceUrl);

        $nodeValueConverter = function ($nodeValue) {
            return str_replace(['[', ']'], ['', ''], $nodeValue);
        };
        $attributeValueConverter = function ($nodeValue) {
            return str_replace('cite_ref-', '', $nodeValue);
        };

        for ($letterNr = 1; $letterNr <= 124; $letterNr++) {
            //for ($letterNr = 124; $letterNr <= 124; $letterNr++) {
            $doc = new DOMDocument();
            @$doc->loadHTMLFile($baseUrl . $letterNr);

            $footnoteRepository = new BasicFootnoteRepository();
            $x = new DOMXPath($doc);
            $footnoteElements = $x->query("//div[@class='reflist']/ol/li");
            foreach ($footnoteElements as $footnoteElement) {
                assert($footnoteElement instanceof DOMElement);
                $elementId = $footnoteElement->getAttribute('id');
                $globalNoteId = explode('-', $elementId)[1];

                $footnoteText = $doc->saveHTML($footnoteElement);
                $footnoteText = str_replace('↩', '', $footnoteText);

                $footnoteRepository->addNote($globalNoteId, $footnoteText);
            }

            $chapter = new ChapterDto();
            $chapter->setTocLabel($letterNr);

            $headNodes = $x->query("//h2/span[@class='mw-headline' and not(@id='Footnotes')]");

            // $headNodes = $x->query('//h2');

            $titleNode = $headNodes[0];
            assert($titleNode instanceof DOMElement);

            // $this->nodeConverter->convertAllChildren($headNode, self::sourceFootnoteTag, null, Chapter::FOOTNOTE_REFERENCE_TAG, Chapter::FOOTNOTE_REFERENCE_ID_ATTRIBUTE);
            // $idMap->renumberAndCollectNoteReferences($titleNode, Chapter::FOOTNOTE_REFERENCE_TAG, Chapter::FOOTNOTE_REFERENCE_ID_ATTRIBUTE);
            $this->nodeConverter->flattenTag($titleNode, 'a', $nodeValueConverter);
            $this->nodeConverter->convertAttributes($titleNode, 'sup', 'id', Chapter::FOOTNOTE_REFERENCE_ID_ATTRIBUTE, $attributeValueConverter);
            $titleHtml = $titleNode->ownerDocument->saveHTML($titleNode);
            $titleHtml = $this->htmlCleaner->clean($titleHtml, [Chapter::FOOTNOTE_REFERENCE_TAG => [Chapter::FOOTNOTE_REFERENCE_ID_ATTRIBUTE]]);
            $titleHtml = substr($titleHtml, strpos($titleHtml, '. ') + 2);
            $chapter->setTitle($titleHtml);


            // Content of the chapter
            $combinedContentHtml = '';
            $contentNodes = $x->query("//div[@id='mw-content-text']//p | //div[@id='mw-content-text']//blockquote");
            foreach ($contentNodes as $contentNode) {
                if ($contentNode instanceof DOMElement && in_array($contentNode->tagName, ['p', 'blockquote'])) {

                    $this->nodeConverter->flattenTag($contentNode, 'a', $nodeValueConverter);
                    $this->nodeConverter->convertAttributes($contentNode, 'sup', 'id', Chapter::FOOTNOTE_REFERENCE_ID_ATTRIBUTE, $attributeValueConverter);

                    // $this->nodeConverter->convertAllChildren($contentNode, self::sourceFootnoteTag, null, Chapter::FOOTNOTE_REFERENCE_TAG, Chapter::FOOTNOTE_REFERENCE_ID_ATTRIBUTE);
                    // $idMap->renumberAndCollectNoteReferences($contentNode, Chapter::FOOTNOTE_REFERENCE_TAG, Chapter::FOOTNOTE_REFERENCE_ID_ATTRIBUTE);
                    $nodeHtml = $contentNode->ownerDocument->saveHTML($contentNode);
                    $nodeHtml = str_replace("\n", '', $nodeHtml);
                    $combinedContentHtml .= $nodeHtml;
                }
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

            yield $chapter;

            // on prod this needs sleep
            // usleep(100000);
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
}