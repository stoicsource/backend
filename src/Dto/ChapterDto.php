<?php

namespace App\Dto;


class ChapterDto
{
    public function __construct(
        private string  $title = '',
        private string  $content = '',
        private string  $tocLabel = '',
        private int $sortOrder = 0,
        private ?array  $footnotes = null
    )
    {
    }

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

    public function getTocLabel(): string
    {
        return $this->tocLabel;
    }

    public function setTocLabel(string $tocLabel): void
    {
        $this->tocLabel = $tocLabel;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
    }

    public function getFootnotes(): ?array
    {
        return $this->footnotes;
    }

    public function setFootnotes(?array $footnotes): void
    {
        $this->footnotes = $footnotes;
    }


}