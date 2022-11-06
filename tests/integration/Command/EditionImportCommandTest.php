<?php

namespace App\Tests\integration\Command;

use App\Adapter\DiscoursesLongWebSource;
use App\Command\EditionImportCommand;
use App\Service\Import\EditionImporter;
use App\Service\Import\HtmlCleaner;
use App\Service\Import\NodeConverter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class EditionImportCommandTest extends TestCase
{
/*
 * tests to write:
 * - sets up source
 * - invokes edition importer
 */
    public function testCallsImporter()
    {
        $editionImporterMock = $this->createMock(EditionImporter::class);
        $editionImporterMock->expects(self::once())->method('import');

        $command = new EditionImportCommand(
            $this->createMock(NodeConverter::class),
            $this->createMock(HtmlCleaner::class),
            $editionImporterMock
        );
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'adapter' => 'App\Adapter\LettersGummereWebSource',
            'source' => 'https://www.testurl.com'
        ]);

        $commandTester->assertCommandIsSuccessful();

    }
}
