<?php

namespace App\Entity;

use DOMElement;

class FootnoteIdMap
{
    private array $idMappingsGlobalToLocal = [];

    private function getLocalNoteId(string $globalId): string
    {
        if (!array_key_exists($globalId, $this->idMappingsGlobalToLocal)) {
            $this->idMappingsGlobalToLocal[$globalId] = count($this->idMappingsGlobalToLocal) + 1;
        }
        return $this->idMappingsGlobalToLocal[$globalId];
    }

    public function renumberNoteIds(DOMElement $baseNode, string $footnoteTag, string $footnoteAttribute): void
    {
        $footnoteNodes = $baseNode->getElementsByTagName($footnoteTag);
        foreach ($footnoteNodes as $footnoteNode) {
            assert($footnoteNode instanceof DOMElement);
            $globalNoteId = $footnoteNode->getAttribute($footnoteAttribute);
            $localNoteId = $this->getLocalNoteId($globalNoteId);
            $footnoteNode->setAttribute($footnoteAttribute, $localNoteId);
            if ($footnoteNode->nodeValue == $globalNoteId) {
                $footnoteNode->nodeValue = $localNoteId;
            }
        }
    }

    public function count(): int
    {
        return count($this->idMappingsGlobalToLocal);
    }

    public function getAllLocalIds()
    {
        return array_values($this->idMappingsGlobalToLocal);
    }

    public function getAllGlobalIds()
    {
        return array_keys($this->idMappingsGlobalToLocal);
    }

    public function localToGlobal($id)
    {
        return array_search($id, $this->idMappingsGlobalToLocal);
    }

    public function globalToLocal($id)
    {
        return $this->idMappingsGlobalToLocal[$id];;
    }
}