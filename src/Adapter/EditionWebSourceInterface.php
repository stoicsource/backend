<?php

namespace App\Adapter;

interface EditionWebSourceInterface
{
    public function getChapters(string $sourceUrl): iterable;
}