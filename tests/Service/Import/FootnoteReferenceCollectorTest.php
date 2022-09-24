<?php

namespace App\Tests\Service\Import;

use App\Service\Import\FootnoteReferenceCollector;
use DOMDocument;
use PHPUnit\Framework\TestCase;

class FootnoteReferenceCollectorTest extends TestCase
{
    public function testCollectNoteIds()
    {
        $doc = new DOMDocument();
        $baseNode = $doc->appendChild($doc->createElement('p'));
        $linkNode = $baseNode->appendChild($doc->createElement('sup', 123));
        $linkNode->setAttribute('data-footnote-reference', 123);
        $secondLinkNode = $baseNode->appendChild($doc->createElement('sup', 456));
        $secondLinkNode->setAttribute('data-footnote-reference', 456);
        $html = $doc->saveHTML($baseNode);

        $collector = new FootnoteReferenceCollector();
        $noteIds = $collector->collectReferences($html, 'sup', 'data-footnote-reference');
        $this->assertCount(2, $noteIds);
        $this->assertEquals(123, $noteIds[0]);
        $this->assertEquals(456, $noteIds[1]);
    }
}
