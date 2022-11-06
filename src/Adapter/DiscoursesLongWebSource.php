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

class DiscoursesLongWebSource implements EditionWebSourceInterface
{
    private const sourceFootnoteTag = 'a';

    public function __construct(
        private readonly NodeConverter    $nodeConverter,
        private readonly HtmlCleaner      $htmlCleaner
    )
    {
    }

    public function getChapters(string $sourceUrl): iterable
    {
        $doc = new DOMDocument();
        @$doc->loadHTMLFile($sourceUrl);

        $footnoteRepository = new BasicFootnoteRepository();
        $x = new DOMXPath($doc);
        $footnoteElements = $x->query("//section[@id='endnotes']/ol/li");
        foreach ($footnoteElements as $footnoteElement) {
            assert($footnoteElement instanceof DOMElement);
            $elementId = $footnoteElement->getAttribute('id');
            $globalNoteId = explode('-', $elementId)[1];

            $footnoteText = $doc->saveHTML($footnoteElement);
            $footnoteText = str_replace('↩', '', $footnoteText);

            $footnoteRepository->addNote($globalNoteId, $footnoteText);
        }


        for ($bookNr = 1; $bookNr <= 4; $bookNr++) {
            //for ($bookNr = 1; $bookNr <= 1; $bookNr++) {
            $bookSection = $doc->getElementById('book-' . $bookNr);
            assert($bookSection !== null);

            foreach ($bookSection->getElementsByTagName('section') as $chapterNode) {
                $idMap = new FootnoteIdMap();

                assert($chapterNode instanceof DOMElement);
                $chapterNodeId = $chapterNode->getAttribute('id');
                assert($chapterNodeId !== null);
                $idElements = explode('-', $chapterNodeId);
                assert(is_array($idElements));
                assert(array_key_exists(2, $idElements));
                $chapterNr = $idElements[2];

                $chapter = new ChapterDto();
                $chapter->setTocLabel("$bookNr.$chapterNr");

                $headNodes = $chapterNode->getElementsByTagName('h4');
                assert($headNodes->count() > 0);
                $titleNode = $headNodes[0];
                assert($titleNode instanceof DOMNode);

                $this->nodeConverter->convertAllChildren($titleNode, self::sourceFootnoteTag, null, Chapter::FOOTNOTE_REFERENCE_TAG, Chapter::FOOTNOTE_REFERENCE_ID_ATTRIBUTE);
                $idMap->renumberAndCollectNoteReferences($titleNode, Chapter::FOOTNOTE_REFERENCE_TAG, Chapter::FOOTNOTE_REFERENCE_ID_ATTRIBUTE);
                $titleHtml = $titleNode->ownerDocument->saveHTML($titleNode);
                $titleHtml = $this->htmlCleaner->clean($titleHtml, [Chapter::FOOTNOTE_REFERENCE_TAG => [Chapter::FOOTNOTE_REFERENCE_ID_ATTRIBUTE]]);
                $chapter->setTitle($titleHtml);

                // Content of the chapter
                $combinedContentHtml = '';
                $contentNodes = $chapterNode->childNodes;
                foreach ($contentNodes as $contentNode) {
                    if ($contentNode instanceof DOMElement && in_array($contentNode->tagName, ['p', 'blockquote'])) {
                        $this->nodeConverter->convertAllChildren($contentNode, self::sourceFootnoteTag, null, Chapter::FOOTNOTE_REFERENCE_TAG, Chapter::FOOTNOTE_REFERENCE_ID_ATTRIBUTE);
                        $idMap->renumberAndCollectNoteReferences($contentNode, Chapter::FOOTNOTE_REFERENCE_TAG, Chapter::FOOTNOTE_REFERENCE_ID_ATTRIBUTE);
                        $combinedContentHtml .= $contentNode->ownerDocument->saveHTML($contentNode);
                    }
                }

                $combinedContentHtml = $this->htmlCleaner->clean($combinedContentHtml, Chapter::ALLOWED_HTML_TAGS_AND_ATTRIBUTES);
                $chapter->setContent($combinedContentHtml);

                if ($idMap->count() > 0) {
                    $targetFootnotes = [];

                    foreach ($idMap->getAllLocalIds() as $localId) {
                        $globalId = $idMap->localToGlobal($localId);
                        $noteContent = $footnoteRepository->getById($globalId);
                        if (!$noteContent) {
                            throw new Exception('Footnote not found');
                        }
                        $noteContent = $this->htmlCleaner->clean($noteContent, Chapter::ALLOWED_HTML_TAGS_AND_ATTRIBUTES);
                        $targetFootnotes[$localId] = $noteContent;
                    }

                    $chapter->setFootnotes($targetFootnotes);
                    // $chapter->setNotesFormat(Chapter::CONTENT_TYPE_JSON);
                }

                yield $chapter;
            }
        }
    }

    public function getEdition(): EditionDto
    {
        return new EditionDto(
            'The Discourses of Epictetus',
            1877,
            'George Long',
            'Discourses'
        );
    }
}