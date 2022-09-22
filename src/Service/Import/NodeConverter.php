<?php

namespace App\Service\Import;

use DOMElement;

class NodeConverter
{
    public function convertAllChildren(DOMElement $baseNode, string $fromTag, ?string $fromAttribute, string $toTag, ?string $toAttribute): void
    {
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
}