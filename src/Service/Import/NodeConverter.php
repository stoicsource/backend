<?php

namespace App\Service\Import;

use Closure;
use DOMElement;
use InvalidArgumentException;

class NodeConverter
{
    public function convertAllChildren(DOMElement $baseNode, string $fromTag, ?string $fromAttribute, string $toTag, ?string $toAttribute): void
    {
        if ($fromTag === $toTag) {
            throw new InvalidArgumentException("fromTag and toTag can not be identical");
        }

        $linkNodes = $baseNode->getElementsByTagName($fromTag);
        while (count($linkNodes) > 0) {
            $linkNode = $linkNodes[0];
            assert($linkNode instanceof DOMElement);
            $attributeValue = $fromAttribute ? $linkNode->getAttribute($fromAttribute) : $linkNode->nodeValue;
            $supElement = $baseNode->ownerDocument->createElement($toTag);
            if ($toAttribute) {
                $supElement->setAttribute($toAttribute, $attributeValue);
            } else {
                $supElement->nodeValue = $attributeValue;
            }
            if ($linkNode->nodeValue > '') {
                $supElement->nodeValue = $linkNode->nodeValue;
            }
            $linkNode->parentNode->replaceChild($supElement, $linkNode);
        }
    }

    public function flattenTag(DOMElement $baseNode, string $tag, Closure $nodeValueConverter = null)
    {
        $tagNodes = $baseNode->getElementsByTagName($tag);
        while (count($tagNodes) > 0) {
            $tagNode = $tagNodes[0];
            assert($tagNode instanceof DOMElement);
            $newNodeValue = $nodeValueConverter ? $nodeValueConverter($tagNode->nodeValue) : $tagNode->nodeValue;
            $parentNode = $tagNode->parentNode;
            $parentNode->removeChild($tagNode);
            $parentNode->nodeValue = $newNodeValue;
        }
    }

    public function convertAttributes(DOMElement $baseNode, string $tag, string $fromAttrib, string $toAttrib, Closure $attributeValueConverter = null)
    {
        $tagNodes = $baseNode->getElementsByTagName($tag);
        foreach ($tagNodes as $tagNode) {
            assert($tagNode instanceof DOMElement);
            $attribValue = $tagNode->getAttribute($fromAttrib);
            if ($attribValue) {
                $attribValue = $attributeValueConverter ? $attributeValueConverter($attribValue) : $attribValue;
                $tagNode->setAttribute($toAttrib, $attribValue);
                $tagNode->removeAttribute($fromAttrib);
            }
        }
    }
}