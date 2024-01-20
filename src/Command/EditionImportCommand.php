<?php


namespace App\Command;


use App\Service\Import\EditionImporter;
use App\Service\Import\HtmlCleaner;
use App\Service\Import\NodeConverter;
use ReflectionClass;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:import:edition')]
class EditionImportCommand extends Command
{
    public function __construct(
        private readonly NodeConverter   $nodeConverter,
        private readonly HtmlCleaner     $htmlCleaner,
        private readonly EditionImporter $editionImporter
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('imports data from the web')
            ->addArgument('adapter', InputArgument::REQUIRED, 'FQCN of the adapter to use')
            ->addArgument('source', InputArgument::REQUIRED, 'url pf the source')
            ->addOption('replace', null, InputOption::VALUE_OPTIONAL, 'replace existing edition', false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // $io = new SymfonyStyle($input, $output);

        $adapterFqcn = $input->getArgument('adapter');
        assert(!empty($adapterFqcn));
        $ref = new ReflectionClass($adapterFqcn);
        assert($ref->getNamespaceName() === 'App\Adapter');
        $adapter = $ref->newInstanceArgs(array($this->nodeConverter, $this->htmlCleaner));

        $sourceUrl = $input->getArgument('source');
        assert(!empty($sourceUrl));

        $replaceExisting = $input->getOption('replace');

        $this->editionImporter->import($adapter, $sourceUrl, $replaceExisting);

        return Command::SUCCESS;
    }

}