<?php

namespace App\Entity\Import;

use App\Repository\BasicFootnoteRepository;
use App\Service\Import\FootnoteReferenceCollector;
use Exception;

class ExtractedChapter implements ChapterInterface
{
    private string $title = '';
    private string $content = '';
    private ?string $footnoteTag = null;
    private ?string $footnoteAttribute = null;
    private ?array $footnotes = null;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getFootnoteTag(): string
    {
        return $this->footnoteTag;
    }

    public function setFootnoteTag(string $footnoteTag): void
    {
        $this->footnoteTag = $footnoteTag;
    }

    public function getFootnoteAttribute(): string
    {
        return $this->footnoteAttribute;
    }

    public function setFootnoteAttribute(string $footnoteAttribute): void
    {
        $this->footnoteAttribute = $footnoteAttribute;
    }

    /**
     * @throws Exception
     */
    public function getFootnotes(): array
    {
        if (!$this->footnotes) {
            throw new Exception('Footnotes have not been extracted');
        }
        return $this->footnotes;
    }

    /**
     * @throws Exception
     */
    public function extractFootnotes(FootnoteReferenceCollector $collector, BasicFootnoteRepository $footnoteRepo): void
    {
        $this->footnotes = [];

        $footnoteIds = [];
        if ($this->title) {
            $footnoteIds = array_merge($footnoteIds, $collector->collectReferences($this->title, $this->footnoteTag, $this->footnoteAttribute));
        }
        if ($this->content) {
            $footnoteIds = array_merge($footnoteIds, $collector->collectReferences($this->content, $this->footnoteTag, $this->footnoteAttribute));
        }

        foreach ($footnoteIds as $footnoteId) {
            $noteText = $footnoteRepo->getById($footnoteId);
            if ($noteText === null) {
                throw new Exception('footnote not found in repo');
            }
            $this->footnotes[$footnoteId] = $noteText;
        }
    }
}