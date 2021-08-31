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

class WebImportMultipleCommand extends Command
{
    private AuthorRepository $authorRepository;
    private EntityManagerInterface $entityManager;
    private WorkRepository $workRepository;
    private TocEntryRepository $tocEntryRepository;
    protected static $defaultName = 'app:import:webmultiple';

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
            ->setDescription('imports data from the web')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $firstUrl = 'https://www.projekt-gutenberg.org/antonius/selbstbe/chap001.html';

        $meditationsWork = $this->workRepository->findOneBy(['name' => 'The Meditations']);

        $authorName = 'Albert Friedrich Wittstock';
        $author = $this->authorRepository->findOneBy(['name' => $authorName]);
        if (!$author) {
            $author = new Author();
            $author->setName($authorName);
            $author->setUrlSlug('wittstock');
            $this->entityManager->persist($author);
        }

        $edition = new Edition();
        $edition->setName('Des Kaisers Marcus Aurelius Antonius Selbstbetrachtungen');
        $edition->setWork($meditationsWork);
        $edition->setYear(1894);
        $edition->setLanguage('deu');
        $edition->setSource('https://www.projekt-gutenberg.org/antonius/selbstbe/index.html');
        $edition->addAuthor($author);
        $this->entityManager->persist($edition);

        for ($bookNr = 1; $bookNr <= 12; $bookNr++) {
            $url = str_replace('001', sprintf('%03d', $bookNr), $firstUrl);

            $io->info("Importing Book $bookNr from $url");

            $doc = new DOMDocument();
            @$doc->loadHTMLFile($url);
            $x = new DOMXPath($doc);
            $headNodes = $x->query('//h5');

            foreach ($headNodes as $headNode) {
                $headerText = $headNode->nodeValue;
                $startsWithNumber = preg_match('/^\d/', $headerText) === 1;

                if ($startsWithNumber) {
                    $chapterNumber = str_replace('.', '', $headerText);
                    $leadingZero = $chapterNumber < 10 ? '0' : '';
                    $fullTocLabel = $bookNr . '.' . $leadingZero . $chapterNumber;

                    $io->info("importing $fullTocLabel");

                    $textNode = $headNode->nextSibling;
                    if ($textNode->nodeValue == "\n") {
                        $textNode = $textNode->nextSibling;
                    }

                    if ($textNode) {
                        $textWithoutComments = '';
                        foreach ($textNode->childNodes as $childNode) {
                            if ($childNode->nodeName == '#text') {
                                $textWithoutComments .= $childNode->nodeValue;
                            }
                        }
                        // $io->info("text: " . substr($textWithoutComments, 0, 100));

                        $tocEntry = $this->tocEntryRepository->findOneBy(['work' => $meditationsWork, 'label' => $fullTocLabel]);

                        if (!$tocEntry) {
                            die("no toc entry for " . $fullTocLabel);
                        }

                        $newContent = new Content();
                        $newContent->setContent($textWithoutComments);
                        $newContent->setEdition($edition);
                        $newContent->setTocEntry($tocEntry);

                        $this->entityManager->persist($newContent);
                    }
                }
            }
        }

        $this->entityManager->flush();

        return 0;
    }

}