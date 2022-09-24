<?php

namespace App\Entity\Import;

interface ChapterInterface
{
    public function getTitle(): string;
    public function getContent(): string;
    public function getFootnotes(): array;
    public function getFootnoteTag(): string;
    public function getFootnoteAttribute(): ?string;
}