<?php

namespace App\Tests\Service\Import;

use App\Service\Import\NodeConverter;
use DOMDocument;
use DOMElement;
use PHPUnit\Framework\TestCase;

class NodeConverterTest extends TestCase
{
    public function testConvertsNode()
    {
        $doc = new DOMDocument();
        $baseNode = $doc->appendChild($doc->createElement('p'));
        $baseNode->appendChild($doc->createElement('a'));

        $converter = new NodeConverter('a', null, 'sup', null);
        $converter->convertAllChildren($baseNode);

        $this->assertEquals(0, $baseNode->getElementsByTagName('a')->count());
    }

    public function testDoesNotConvertNode()
    {
        $doc = new DOMDocument();
        $baseNode = $doc->appendChild($doc->createElement('p'));
        $baseNode->appendChild($doc->createElement('a'));

        $converter = new NodeConverter('invalid', null, 'somethingelse', null);
        $converter->convertAllChildren($baseNode);

        $this->assertEquals(1, $baseNode->getElementsByTagName('a')->count());
    }

    public function testConvertsNodeAttribute()
    {
        $doc = new DOMDocument();
        $baseNode = $doc->appendChild($doc->createElement('p'));
        $linkNode = $baseNode->appendChild($doc->createElement('a'));
        $linkNode->setAttribute('ref-note', 123);

        $converter = new NodeConverter('a', 'ref-note', 'sup', 'footnote');
        $converter->convertAllChildren($baseNode);

        $supNodes = $baseNode->getElementsByTagName('sup');
        $this->assertEquals(1, $supNodes->count());
        $supNode = $supNodes[0];
        assert($supNode instanceof DOMElement);
        $supAttributeValue = $supNode->getAttribute('footnote');
        $this->assertEquals(123, $supAttributeValue);
    }

    public function testConvertsNodeValueToAttribute()
    {
        $doc = new DOMDocument();
        $baseNode = $doc->appendChild($doc->createElement('p'));
        $linkNode = $baseNode->appendChild($doc->createElement('a', 456));
        $linkNode->setAttribute('ref-note', 123);

        $converter = new NodeConverter('a', null, 'sup', 'footnote');
        $converter->convertAllChildren($baseNode);

        $supNodes = $baseNode->getElementsByTagName('sup');
        $this->assertEquals(1, $supNodes->count());
        $supNode = $supNodes[0];
        assert($supNode instanceof DOMElement);
        $supAttributeValue = $supNode->getAttribute('footnote');
        $this->assertEquals(456, $supAttributeValue);
    }


}
