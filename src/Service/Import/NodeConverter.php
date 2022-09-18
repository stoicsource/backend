<?php

namespace App\Service\Import;

use DOMElement;

class NodeConverter
{
    public function __construct(
        protected string $fromTag,
        protected ?string $fromAttribute,
        protected string $toTag,
        protected ?string $toAttribute
    )
    {
    }

    public function convertAllChildren(DOMElement $baseNode): void
    {
        $linkNodes = $baseNode->getElementsByTagName($this->fromTag);
        while (count($linkNodes) > 0) {
            $linkNode = $linkNodes[0];
            assert($linkNode instanceof DOMElement);
            $attributeValue = $this->fromAttribute ? $linkNode->getAttribute($this->fromAttribute) : $linkNode->nodeValue;
            $supElement = $baseNode->ownerDocument->createElement($this->toTag);
            if ($this->toAttribute) {
                $supElement->setAttribute($this->toAttribute, $attributeValue);
            } else {
                $supElement->nodeValue = $attributeValue;
            }
            $linkNode->parentNode->replaceChild($supElement, $linkNode);
        }
    }
}