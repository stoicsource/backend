<?php

namespace App\Service\Import;

use DOMDocument;
use DOMElement;

class FootnoteReferenceCollector
{
    public function collectReferences(string $html, string $footnoteTag, ?string $footnoteAttribute): array
    {
        $doc = new DOMDocument();
        $doc->loadHTML($html);
        $noteIds = [];

        $footnoteNodes = $doc->getElementsByTagName($footnoteTag);
        foreach ($footnoteNodes as $footnoteNode) {
            assert($footnoteNode instanceof DOMElement);
            if ($footnoteAttribute) {
                $noteIds[] = $footnoteNode->getAttribute($footnoteAttribute);
            } else {
                $noteIds[] = $footnoteNode->nodeValue;
            }
        }

        return $noteIds;
    }
}