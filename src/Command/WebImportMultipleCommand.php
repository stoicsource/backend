<?php


namespace App\Command;


use App\Entity\Author;
use App\Entity\Client;
use App\Entity\Content;
use App\Entity\Edition;
use App\Entity\TocEntry;
use App\Entity\Work;
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
            ->setDescription('imports data from the web');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $firstUrl = 'https://en.wikisource.org/wiki/Moral_letters_to_Lucilius/Letter_1';

        $workName = 'Moral Letters to Lucilius';
        $lettersWork = $this->workRepository->findOneBy(['name' => $workName]);
        if (!$lettersWork) {
            $lettersWork = new Work();
            $lettersWork->setName($workName);
            $lettersWork->setUrlSlug('letters');
            $lettersWork->addAuthor($this->authorRepository->findOneBy(['urlSlug' => 'seneca']));

            $this->entityManager->persist($lettersWork);
        }

        $authorName = 'Richard Mott Gummere';
        $author = $this->authorRepository->findOneBy(['name' => $authorName]);
        if (!$author) {
            $author = new Author();
            $author->setName($authorName);
            $author->setUrlSlug('gummere');
            $this->entityManager->persist($author);
        }

        $edition = new Edition();
        $edition->setName('Moral letters to Lucilius');
        $edition->setWork($lettersWork);
        $edition->setYear(1925);
        $edition->setLanguage('eng');
        $edition->setSource($firstUrl);
        $edition->addAuthor($author);
        $this->entityManager->persist($edition);

        for ($letterNr = 1; $letterNr <= 4; $letterNr++) {
            $url = str_replace('1', sprintf('%d', $letterNr), $firstUrl);

            $io->info("Importing Book $letterNr from $url");

            $doc = new DOMDocument();
            @$doc->loadHTMLFile($url);
            $x = new DOMXPath($doc);
            $headNodes = $x->query('//h2');

            $headNode = $headNodes[0];

            $fullTocLabel = $letterNr;

            $io->info("importing $fullTocLabel");

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
            $combinedText = str_replace("\n\n", "\n", $combinedText);

            $tocEntry = $this->tocEntryRepository->findOneBy(['work' => $lettersWork, 'label' => $fullTocLabel]);

            if (!$tocEntry) {
                $tocEntry = new TocEntry();
                $tocEntry->setWork($lettersWork);
                $tocEntry->setLabel($fullTocLabel);
                $tocEntry->setSortOrder($letterNr);
                $this->entityManager->persist($tocEntry);
            }

            $newContent = new Content();
            $newContent->setContent($combinedText);
            $newContent->setEdition($edition);
            $newContent->setTocEntry($tocEntry);

            $this->entityManager->persist($newContent);
        }

        $this->entityManager->flush();

        return 0;
    }

}