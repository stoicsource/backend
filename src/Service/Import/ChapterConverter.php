<?php

namespace App\Service\Import;

use App\Entity\Content;
use App\Entity\FootnoteIdMap;
use App\Entity\Import\ChapterInterface;
use DOMDocument;
use DOMElement;
use Exception;

class ChapterConverter
{
    private string $targetNoteTag = '';
    private string $targetNoteAttribute = '';
    private array $allowedTagsAndAttributesTitle = [];
    private array $allowedTagsAndAttributesContent = [];

    public function __construct(
        public NodeConverter $nodeConverter,
        public HtmlCleaner $htmlCleaner
    )
    {
    }

    /**
     * @throws Exception
     */
    public function convert(ChapterInterface $extractedChapter): Content
    {
        $resultContent = new Content();

        $idMap = new FootnoteIdMap();

        if ($extractedChapter->getTitle() > '') {
            $titleHtml = $this->processHtml($extractedChapter->getTitle(), $extractedChapter, $idMap, $this->allowedTagsAndAttributesTitle);

            $resultContent->setTitle($titleHtml);
        }

        if ($extractedChapter->getContent() > '') {
            $contentHtml = $this->processHtml($extractedChapter->getContent(), $extractedChapter, $idMap, $this->allowedTagsAndAttributesContent);

            $resultContent->setContent($contentHtml);
            $resultContent->setContentFormat(Content::CONTENT_TYPE_HTML);
        }

        if (count($idMap->getAllLocalIds()) > 0) {
            $sourceFootnotes = $extractedChapter->getFootnotes();
            $targetFootnotes = [];

            foreach ($idMap->getAllLocalIds() as $localId) {
                $globalId = $idMap->localToGlobal($localId);
                if (!array_key_exists($globalId, $sourceFootnotes)) {
                    throw new Exception('Footnote not found');
                }
                $noteContent = $sourceFootnotes[$globalId];
                $this->htmlCleaner->setAllowedTagsAndAttributes($this->allowedTagsAndAttributesContent);
                $noteContent = $this->htmlCleaner->clean($noteContent);
                $targetFootnotes[] = [
                    'id' => $localId,
                    'content' => $noteContent
                ] ;
            }

            $resultContent->setNotes(json_encode($targetFootnotes));
            $resultContent->setNotesFormat(Content::CONTENT_TYPE_JSON);
        }

        return $resultContent;
    }

    public function processHtml($sourceHtml, ChapterInterface $extractedChapter, FootnoteIdMap $idMap, $allowedTags)
    {
        $contentDoc = new DOMDocument('1.0', 'utf-8');
        $contentDoc->loadHTML(mb_convert_encoding($sourceHtml, 'HTML-ENTITIES', 'UTF-8'));
        $contentBaseNode = $contentDoc->getElementsByTagName('body')->item(0);
        assert($contentBaseNode instanceof DOMElement);

        $this->nodeConverter->convertAllChildren($contentBaseNode, $extractedChapter->getFootnoteTag(), $extractedChapter->getFootnoteAttribute(), $this->targetNoteTag, $this->targetNoteAttribute);
        $idMap->renumberNoteIds($contentBaseNode, $this->targetNoteTag, $this->targetNoteAttribute);

        $contentHtml = $contentDoc->saveHTML($contentBaseNode);
        $this->htmlCleaner->setAllowedTagsAndAttributes($allowedTags);
        return $this->htmlCleaner->clean($contentHtml);
    }

    public function getTargetNoteTag(): string
    {
        return $this->targetNoteTag;
    }

    public function setTargetNoteTag(string $targetNoteTag): void
    {
        $this->targetNoteTag = $targetNoteTag;
    }

    public function getTargetNoteAttribute(): string
    {
        return $this->targetNoteAttribute;
    }

    public function setTargetNoteAttribute(string $targetNoteAttribute): void
    {
        $this->targetNoteAttribute = $targetNoteAttribute;
    }

    public function getAllowedTagsAndAttributesTitle(): array
    {
        return $this->allowedTagsAndAttributesTitle;
    }

    public function setAllowedTagsAndAttributesTitle(array $allowedTagsAndAttributesTitle): void
    {
        $this->allowedTagsAndAttributesTitle = $allowedTagsAndAttributesTitle;
    }

    public function getAllowedTagsAndAttributesContent(): array
    {
        return $this->allowedTagsAndAttributesContent;
    }

    public function setAllowedTagsAndAttributesContent(array $allowedTagsAndAttributesContent): void
    {
        $this->allowedTagsAndAttributesContent = $allowedTagsAndAttributesContent;
    }
}