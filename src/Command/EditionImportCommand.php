<?php


namespace App\Command;


use App\Adapter\DiscoursesEditionWebSource;
use App\Service\Import\EditionImporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:import:edition')]
class EditionImportCommand extends Command
{
    public function __construct(
        private readonly DiscoursesEditionWebSource $sourceAdapter,
        private readonly EditionImporter $editionImporter
    )
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('imports data from the web')
            ->addArgument('source', InputArgument::REQUIRED, 'url pf the source')
            // ->addArgument('adapter', InputArgument::REQUIRED, 'FQCN of the adapter to use')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // $io = new SymfonyStyle($input, $output);

        $sourceUrl = $input->getArgument('source');
        assert(!empty($sourceUrl));

        $this->editionImporter->import($this->sourceAdapter, $sourceUrl);

        return Command::SUCCESS;
    }

}