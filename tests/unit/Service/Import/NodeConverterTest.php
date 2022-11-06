<?php

namespace App\Tests\unit\Service\Import;

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

        $converter = new NodeConverter();
        $converter->convertAllChildren($baseNode, 'a', null, 'sup', null);

        $this->assertEquals(0, $baseNode->getElementsByTagName('a')->count());
    }

    public function testDoesNotConvertNode()
    {
        $doc = new DOMDocument();
        $baseNode = $doc->appendChild($doc->createElement('p'));
        $baseNode->appendChild($doc->createElement('a'));

        $converter = new NodeConverter();
        $converter->convertAllChildren($baseNode, 'invalid', null, 'somethingelse', null);

        $this->assertEquals(1, $baseNode->getElementsByTagName('a')->count());
    }

    public function testConvertsNodeAttribute()
    {
        $doc = new DOMDocument();
        $baseNode = $doc->appendChild($doc->createElement('p'));
        $linkNode = $baseNode->appendChild($doc->createElement('a'));
        $linkNode->setAttribute('ref-note', 123);

        $converter = new NodeConverter();
        $converter->convertAllChildren($baseNode, 'a', 'ref-note', 'sup', 'footnote');

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

        $converter = new NodeConverter();
        $converter->convertAllChildren($baseNode, 'a', null, 'sup', 'footnote');

        $supNodes = $baseNode->getElementsByTagName('sup');
        $this->assertEquals(1, $supNodes->count());
        $supNode = $supNodes[0];
        assert($supNode instanceof DOMElement);
        $supAttributeValue = $supNode->getAttribute('footnote');
        $this->assertEquals(456, $supAttributeValue);
    }

    public function testConvertsNodeValueToAttributeAndKeepsNodeValue()
    {
        $doc = new DOMDocument();
        $baseNode = $doc->appendChild($doc->createElement('p'));
        $linkNode = $baseNode->appendChild($doc->createElement('a', 456));
        $linkNode->setAttribute('ref-note', 123);

        $converter = new NodeConverter();
        $converter->convertAllChildren($baseNode, 'a', null, 'sup', 'footnote');

        $supNodes = $baseNode->getElementsByTagName('sup');
        $this->assertEquals(1, $supNodes->count());
        $supNode = $supNodes[0];
        assert($supNode instanceof DOMElement);
        $this->assertEquals(456, $supNode->nodeValue);
    }

    public function testRemovesTag()
    {
        $doc = new DOMDocument();
        $baseNode = $doc->appendChild($doc->createElement('sup'));
        $linkNode = $doc->createElement('a', '[1]');
        $baseNode->appendChild($linkNode);

        $nodeValueConverter = function ($nodeValue) { return str_replace(['[', ']'], ['', ''], $nodeValue); };
        $converter = new NodeConverter();
        $converter->flattenTag($baseNode, 'a', $nodeValueConverter);

        $this->assertEquals(0, $baseNode->getElementsByTagName('a')->count());
        $this->assertEquals('1', $baseNode->nodeValue);
    }

    public function testConvertsAttribute()
    {
        $doc = new DOMDocument();
        $baseNode = $doc->appendChild($doc->createElement('p'));
        $supNode = $doc->createElement('sup');
        $supNode->setAttribute('id', 'cite_ref-1');
        $baseNode->appendChild($supNode);

        $attributeValueConverter = function ($nodeValue) { return str_replace('cite_ref-', '', $nodeValue); };
        $converter = new NodeConverter();
        $converter->convertAttributes($baseNode, 'sup', 'id', 'my-new-attribute', $attributeValueConverter);

        $this->assertEquals('1', $supNode->getAttribute('my-new-attribute'));
        $this->assertEquals(false, $supNode->hasAttribute('id'));
    }
}
