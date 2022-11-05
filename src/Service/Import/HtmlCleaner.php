<?php

namespace App\Service\Import;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class HtmlCleaner
{
    public function clean(string $taintedHtml, $allowedTagsAndAttributes): string
    {
        $strippedHtml = strip_tags($taintedHtml, array_map(function ($attribs, $tag) { return '<' . $tag . '>'; }, $allowedTagsAndAttributes, array_keys($allowedTagsAndAttributes)) );

        $config = (new HtmlSanitizerConfig());

        foreach ($allowedTagsAndAttributes as $tag => $attributes) {
            $config = $config->allowElement($tag, $attributes);
        }

        $sanitizer = new HtmlSanitizer($config);
        $sanitizedHtml = $sanitizer->sanitize($strippedHtml);

        return trim($sanitizedHtml);
    }
}