<?php

namespace App\Adapter;

use App\Dto\EditionDto;

interface EditionWebSourceInterface
{
    public function getChapters(string $sourceUrl): iterable;
    public function getEdition(): EditionDto;
}