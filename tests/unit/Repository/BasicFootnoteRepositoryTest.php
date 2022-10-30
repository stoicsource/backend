<?php

namespace App\Tests\unit\Repository;

use App\Repository\BasicFootnoteRepository;
use PHPUnit\Framework\TestCase;

class BasicFootnoteRepositoryTest extends TestCase
{
    public function testAdd()
    {
        $repo = new BasicFootnoteRepository();
        $repo->addNote(123, 'abc');
        $this->assertEquals('abc', $repo->getById(123));
    }
}
