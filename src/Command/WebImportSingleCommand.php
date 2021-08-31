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

        $url = 'https://www.projekt-gutenberg.org/epiktet/moral/moral.html';

        $enchirideonWork = $this->workRepository->findOneBy(['name' => 'The Enchirideon']);

        $authorName = 'Carl Hilty';
        $author = $this->authorRepository->findOneBy(['name' => $authorName]);
        if (!$author) {
            $author = new Author();
            $author->setName($authorName);
            $author->setUrlSlug('hilty');
            $this->entityManager->persist($author);
        }

        $edition = new Edition();
        $edition->setName('Handbüchlein der Moral');
        $edition->setWork($enchirideonWork);
        $edition->setYear(1946);
        $edition->setLanguage('deu');
        $edition->setSource('https://www.projekt-gutenberg.org/epiktet/moral/moral.html');
        $edition->addAuthor($author);
        $this->entityManager->persist($edition);


        $doc = new DOMDocument();
        @$doc->loadHTMLFile($url);
        $x = new DOMXPath($doc);
        $headNodes = $x->query('//h4');

        foreach ($headNodes as $headNode) {
            $headerText = $headNode->nodeValue;
            $startsWithNumber = preg_match('/^\d/', $headerText) === 1;

            if ($startsWithNumber) {
                $fullTocLabel = $headerText;

                $io->info("importing $fullTocLabel");

                $textNode = $headNode->nextSibling;
                if ($textNode->nodeValue == "\n") {
                    $textNode = $textNode->nextSibling;
                }

                $combinedText = '';
                while ($textNode->tagName === 'p') {
                    $combinedText .= ($combinedText > '' ? "\n" : '') . $textNode->nodeValue;

                    $textNode = $textNode->nextSibling;
                    if ($textNode->nodeValue == "\n") {
                        $textNode = $textNode->nextSibling;
                    }
                }

                $tocEntry = $this->tocEntryRepository->findOneBy(['work' => $enchirideonWork, 'label' => $fullTocLabel]);

                if (!$tocEntry) {
                    die("no toc entry for " . $fullTocLabel);
                }

                $newContent = new Content();
                $newContent->setContent($combinedText);
                $newContent->setEdition($edition);
                $newContent->setTocEntry($tocEntry);

                $this->entityManager->persist($newContent);
            }
        }

        $this->entityManager->flush();

        return 0;
    }

}