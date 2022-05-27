<?php


namespace App\Command;


use App\Entity\Client;
use App\Repository\AuthorRepository;
use App\Repository\ContentRepository;
use App\Repository\EditionRepository;
use App\Repository\TocEntryRepository;
use App\Repository\WorkRepository;
use Doctrine\ORM\EntityManagerInterface;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class WebImportMultipleCommand extends Command
{
    private AuthorRepository $authorRepository;
    private EntityManagerInterface $entityManager;
    private WorkRepository $workRepository;
    private TocEntryRepository $tocEntryRepository;
    protected static $defaultName = 'app:import:webmultiple';
    private EditionRepository $editionRepository;
    private ContentRepository $contentRepository;

    public function __construct(AuthorRepository $authorRepository, WorkRepository $workRepository, TocEntryRepository $tocEntryRepository, EntityManagerInterface $entityManager, EditionRepository $editionRepository, ContentRepository $contentRepository)
    {
        $this->authorRepository = $authorRepository;
        $this->entityManager = $entityManager;
        $this->workRepository = $workRepository;
        $this->tocEntryRepository = $tocEntryRepository;
        $this->editionRepository = $editionRepository;
        $this->contentRepository = $contentRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('imports data from the web');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $firstUrl = 'https://en.wikisource.org/wiki/Moral_letters_to_Lucilius/Letter_1';

        $workName = 'Moral Letters to Lucilius';
        $work = $this->workRepository->findOneBy(['name' => $workName]);

        $editionName = 'Moral letters to Lucilius';
        $edition = $this->editionRepository->findOneBy(['name' => $editionName]);

        if (!$work || !$edition) {
            return "oops";
        }

        // 124
        for ($letterNr = 1; $letterNr <= 124; $letterNr++) {
            $tocEntry = $this->tocEntryRepository->findOneBy(['work' => $work, 'label' => $letterNr]);
            $content = $this->contentRepository->findOneBy(['tocEntry' => $tocEntry, 'edition' => $edition]);

            if (!$content || !$tocEntry) {
                return "oops 2";
            }

            $url = str_replace('1', sprintf('%d', $letterNr), $firstUrl);

            // $io->info("Importing Book $letterNr from $url");

            $doc = new DOMDocument();
            @$doc->loadHTMLFile($url);
            $x = new DOMXPath($doc);
            $headNodes = $x->query('//h2');

            /* @var $headNode DOMElement */
            $headNode = $headNodes[0];
            $spanNode = $headNode->firstChild;

            if ($spanNode->nodeValue == '') {
                $spanNode = $spanNode->nextSibling;
            }

            $rawHeadline = $spanNode->nodeValue;
            $refinedHeadline = trim(substr($rawHeadline, strpos($rawHeadline, '.') + 1));

            $io->info("importing $letterNr as $refinedHeadline");

            $content->setTitle($refinedHeadline);

            $this->entityManager->persist($content);

            usleep(100000);
        }

        $this->entityManager->flush();

        return 0;
    }

}