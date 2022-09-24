<?php

namespace App\Tests\Entity;

use App\Entity\FootnoteIdMap;
use DOMDocument;
use PHPUnit\Framework\TestCase;

class FootnoteIdMapTest extends TestCase
{

    public function testRenumberNotes()
    {
        $doc = new DOMDocument();
        $baseNode = $doc->appendChild($doc->createElement('p'));
        $linkNode = $baseNode->appendChild($doc->createElement('sup', 123));
        $linkNode->setAttribute('data-footnote-reference', 123);
        $secondLinkNode = $baseNode->appendChild($doc->createElement('sup', 456));
        $secondLinkNode->setAttribute('data-footnote-reference', 456);

        $idMap = new FootnoteIdMap();
        $idMap->renumberNoteIds($baseNode, 'sup', 'data-footnote-reference');
        $this->assertEquals(1, $linkNode->nodeValue);
        $this->assertEquals(1, $linkNode->getAttribute('data-footnote-reference'));
        $this->assertEquals(2, $secondLinkNode->nodeValue);
        $this->assertEquals(2, $secondLinkNode->getAttribute('data-footnote-reference'));
        $this->assertEquals(2, $idMap->count());
    }

}
