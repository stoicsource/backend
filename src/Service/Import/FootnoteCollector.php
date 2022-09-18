<?php

namespace App\Service\Import;

use DOMElement;

class FootnoteCollector
{
    public function __construct(
        protected string $footnoteTag,
        protected ?string $footnoteAttribute
    )
    {
    }

    public function collect(DOMElement $baseNode): array
    {
        $footNotes = [];
        $footnoteNodes = $baseNode->getElementsByTagName($this->footnoteTag);
        foreach ($footnoteNodes as $footnoteNode) {
            assert($footnoteNode instanceof DOMElement);
            $noteNumber = $this->footnoteAttribute ? $footnoteNode->getAttribute($this->footnoteAttribute) : $footnoteNode->nodeValue;
            $footNotes[] = $noteNumber;
        }
        return $footNotes;
    }
}