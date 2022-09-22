<?php

namespace App\Repository;

class BasicFootnoteRepository
{
    private array $footnotes = [];

    public function addNote(string $id, string $content): void
    {
        $this->footnotes[$id] = $content;
    }

    public function getById(string $id): ?string
    {
        if (array_key_exists($id, $this->footnotes)) {
            return $this->footnotes[$id];
        }
        return null;
    }

    public function getAll()
    {
        return $this->footnotes;
    }
}