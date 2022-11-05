<?php

namespace App\Tests\integration\Command;

use App\Adapter\DiscoursesEditionWebSource;
use App\Command\EditionImportCommand;
use App\Service\Import\EditionImporter;
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
            $this->createMock(DiscoursesEditionWebSource::class),
            $editionImporterMock
        );
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'source' => 'https://www.testurl.com'
        ]);

        $commandTester->assertCommandIsSuccessful();

    }
}
