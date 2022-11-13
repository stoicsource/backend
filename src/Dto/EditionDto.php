<?php

namespace App\Dto;


class EditionDto
{
    public const LANG_CODE_GERMAN = 'deu';

    public function __construct(
        private string $name = '',
        private string $year = '',
        private string $authorName = '',
        private string $workName = '',
        private string $language = '',
        private array $sources = []
    )
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getYear(): string
    {
        return $this->year;
    }

    public function setYear(string $year): void
    {
        $this->year = $year;
    }

    public function getAuthorName(): string
    {
        return $this->authorName;
    }

    public function setAuthorName(string $authorName): void
    {
        $this->authorName = $authorName;
    }

    public function getWorkName(): string
    {
        return $this->workName;
    }

    public function setWorkName(string $workName): void
    {
        $this->workName = $workName;
    }

    public function getSources(): array
    {
        return $this->sources;
    }

    public function setSources(array $sources): void
    {
        $this->sources = $sources;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): void
    {
        $this->language = $language;
    }

}