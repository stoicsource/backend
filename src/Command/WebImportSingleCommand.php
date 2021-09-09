<?php


namespace App\Command;


use App\Entity\Author;
use App\Entity\Client;
use App\Entity\Content;
use App\Entity\Edition;
use App\Entity\TocEntry;
use App\Repository\AuthorRepository;
use App\Repository\TocEntryRepository;
use App\Repository\WorkRepository;
use Doctrine\ORM\EntityManagerInterface;
use DOMDocument;
use DOMNode;
use DOMXPath;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class WebImportSingleCommand extends Command
{
    private AuthorRepository $authorRepository;
    private EntityManagerInterface $entityManager;
    private WorkRepository $workRepository;
    private TocEntryRepository $tocEntryRepository;
    protected static $defaultName = 'app:import:websingle';

    public function __construct(AuthorRepository $authorRepository, WorkRepository $workRepository, TocEntryRepository $tocEntryRepository, EntityManagerInterface $entityManager)
    {
        $this->authorRepository = $authorRepository;
        $this->entityManager = $entityManager;
        $this->workRepository = $workRepository;
        $this->tocEntryRepository = $tocEntryRepository;

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

        $url = 'https://www.stoicsource.com/rufus.html';

        $lecturesWork = $this->workRepository->findOneBy(['name' => 'Lectures']);

        $authorName = 'Cora Elizabeth Lutz';
        $author = $this->authorRepository->findOneBy(['name' => $authorName]);
        if (!$author) {
            $author = new Author();
            $author->setName($authorName);
            $author->setShortName('Cora E. Lutz');
            $author->setUrlSlug('lutz');
            $this->entityManager->persist($author);
        }

        $edition = new Edition();
        $edition->setName('The Roman Socrates');
        $edition->setWork($lecturesWork);
        $edition->setYear(1947);
        $edition->setLanguage('eng');
        $edition->setSource($url);
        $edition->addAuthor($author);
        $this->entityManager->persist($edition);


        $doc = new DOMDocument();
        @$doc->loadHTMLFile($url);
        $x = new DOMXPath($doc);
        $headNodes = $x->query('//h2');

        foreach ($headNodes as $index => $headNode) {
            /* @var DOMNode $headNode */
            $tocLabel = $headNode->firstChild->nodeValue;
            $tocTitle = $headNode->lastChild->nodeValue;

            $io->info("importing $tocLabel");

            $textNode = $headNode->nextSibling;
            if (trim($textNode->nodeValue) == '') {
                $textNode = $textNode->nextSibling;
            }

            $combinedText = '';
            while ($textNode->tagName === 'p') {
                $combinedText .= ($combinedText > '' ? "\n" : '') . $textNode->nodeValue;

                $textNode = $textNode->nextSibling;
                if (trim($textNode->nodeValue) == '') {
                    $textNode = $textNode->nextSibling;
                }
            }
            $combinedText = str_replace("\r\n", '', $combinedText);

            $tocEntry = $this->tocEntryRepository->findOneBy(['work' => $lecturesWork, 'label' => $tocLabel]);

            if (!$tocEntry) {
                $tocEntry = new TocEntry();
                $tocEntry->setWork($lecturesWork);
                $tocEntry->setLabel($tocLabel);
                $tocEntry->setSortOrder($index + 1);
                $this->entityManager->persist($tocEntry);
            }

            $newContent = new Content();
            $newContent->setContent($combinedText);
            $newContent->setEdition($edition);
            $newContent->setTitle(ucfirst(strtolower($tocTitle)));
            $newContent->setTocEntry($tocEntry);

            $this->entityManager->persist($newContent);

        }

        $this->entityManager->flush();

        return 0;
    }

}