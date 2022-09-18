<?php

namespace App\Tests\Service\Import;

use App\Service\Import\FootnoteCollector;
use DOMDocument;
use PHPUnit\Framework\TestCase;

class FootnoteCollectorTest extends TestCase
{
    public function testCollects()
    {
        $doc = new DOMDocument();
        $baseNode = $doc->appendChild($doc->createElement('p'));
        $linkNode = $baseNode->appendChild($doc->createElement('sup', 123));
        $linkNode->setAttribute('data-footnote-reference', 123);
        $secondLinkNode = $baseNode->appendChild($doc->createElement('sup', 456));
        $secondLinkNode->setAttribute('data-footnote-reference', 456);

        $collector = new FootnoteCollector('sup', 'data-footnote-reference');
        $footNotes = $collector->collect($baseNode);
        $this->assertIsArray($footNotes);
        $this->assertCount(2, $footNotes);
        $this->assertEquals(123, $footNotes[0]);
        $this->assertEquals(456, $footNotes[1]);
    }
}
