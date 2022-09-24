<?php

namespace App\Service\Import;

use App\Entity\Content;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class HtmlCleaner
{
    private array $allowedTagsAndAttributes = [];

    public function getAllowedTagsAndAttributes(): array
    {
        return $this->allowedTagsAndAttributes;
    }

    public function setAllowedTagsAndAttributes(array $allowedTagsAndAttributes): void
    {
        $this->allowedTagsAndAttributes = $allowedTagsAndAttributes;
    }

    public function clean(string $taintedHtml): string
    {
        $strippedHtml = strip_tags($taintedHtml, array_map(function ($attribs, $tag) { return '<' . $tag . '>'; }, $this->allowedTagsAndAttributes, array_keys($this->allowedTagsAndAttributes)) );

        $config = (new HtmlSanitizerConfig());

        foreach ($this->allowedTagsAndAttributes as $tag => $attributes) {
            $config = $config->allowElement($tag, $attributes);
        }

        $sanitizer = new HtmlSanitizer($config);
        $sanitizedHtml = $sanitizer->sanitize($strippedHtml);

        return $sanitizedHtml;
    }
}