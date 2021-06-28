<?php


namespace App\Command;


use App\Entity\Author;
use App\Entity\Client;
use App\Entity\Content;
use App\Entity\Edition;
use App\Entity\TocEntry;
use App\Repository\AuthorRepository;
use App\Repository\WorkRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class EpictetusImportCommand extends Command
{
    private AuthorRepository $authorRepository;
    private EntityManagerInterface $entityManager;
    private WorkRepository $workRepository;
    protected static $defaultName = 'app:import:epictetus';

    public function __construct(AuthorRepository $authorRepository, WorkRepository $workRepository, EntityManagerInterface $entityManager)
    {
        $this->authorRepository = $authorRepository;
        $this->entityManager = $entityManager;
        $this->workRepository = $workRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('imports Epic MC')
            ->addArgument('filename', InputArgument::REQUIRED, 'file to import')
            ->addOption('authors', null, InputOption::VALUE_NONE, 'import author data')
            ->addOption('enchirideon', null, InputOption::VALUE_NONE, 'import enchirideon')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $filename = $input->getArgument('filename');
        $io->info('Reading from ' . $filename);

        $spreadsheet = IOFactory::load($filename);

        if ($input->getOption('authors')) {
            $io->info('Importing Author Data');
            $this->importAuthorsAndEditions($spreadsheet);
        }

        if ($input->getOption('enchirideon')) {
            $io->info('Importing Enchirideon Data');
            $this->importEnchirideon($spreadsheet);
        }


        //$io->success(sprintf('Created "%d" work logs.', $addedLogCount));

        return 0;
    }

    protected function importAuthorsAndEditions(Spreadsheet $spreadsheet)
    {
        $translatorInfoSheet = $spreadsheet->getSheetByName('Tr. Info');
        //$translatorInfoSheet->getCell('A1')->getValue()

        $authorNameCol = 'B';
        $editionYearCol = 'C';
        $hasDiscoursesCol = 'E';
        $hasEnchirideonCol = 'F';
        $hasFragementsCol = 'G';
        $hasNotesCol = 'H';
        $copyrightCol = 'M';

        $enchirideonWork = $this->workRepository->findOneBy(['name' => 'The Enchirideon']);

        for ($rowIndex = 2; $rowIndex <= $translatorInfoSheet->getHighestDataRow(); $rowIndex++) {
            $authorName = $translatorInfoSheet->getCell($authorNameCol . $rowIndex)->getValue();

            $author = new Author();
            $author->setName($authorName);

            $this->entityManager->persist($author);

            $hasEnchirideon = $translatorInfoSheet->getCell($hasEnchirideonCol . $rowIndex)->getValue() == 'Yes';
            if ($hasEnchirideon) {
                $edition = new Edition();
                $edition->setName('The Enchirideon');
                $edition->setWork($enchirideonWork);
                $edition->addAuthor($author);

                $this->entityManager->persist($edition);
            }
        }

        $this->entityManager->flush();
    }

    protected function importEnchirideon(Spreadsheet $spreadsheet)
    {
        $enchirideonSheet = $spreadsheet->getSheetByName('Ench Txt');

        $editionsMeta = [
          [
              'author' => $this->authorRepository->findOneBy(['name' => 'Elizabeth Carter']),
              'contentColumn' => 'B',
              'notesColumn' => null,
          ],
          [
              'author' => $this->authorRepository->findOneBy(['name' => 'Hastings Crossley']),
              'contentColumn' => 'J',
              'notesColumn' => null
          ],
          [
              'author' => $this->authorRepository->findOneBy(['name' => 'John Davies']),
              'contentColumn' => 'X',
              'notesColumn' => null
          ],
          [
              'author' => $this->authorRepository->findOneBy(['name' => 'John Healey']),
              'contentColumn' => 'T',
              'notesColumn' => null
          ],
          [
              'author' => $this->authorRepository->findOneBy(['name' => 'Thomas Wentworth Higginson']),
              'contentColumn' => 'C',
              'notesColumn' => 'D'
          ],
          [
              'author' => $this->authorRepository->findOneBy(['name' => 'George Long']),
              'contentColumn' => 'O',
              'notesColumn' => 'P'
          ],
          [
              'author' => $this->authorRepository->findOneBy(['name' => 'Percy Ewing Matheson']),
              'contentColumn' => 'K',
              'notesColumn' => 'L'
          ],
          [
              'author' => $this->authorRepository->findOneBy(['name' => 'Lady Mary Wortley Montagu']),
              'contentColumn' => 'R',
              'notesColumn' => 'S'
          ],
          [
              'author' => $this->authorRepository->findOneBy(['name' => 'William Abbot Oldfather']),
              'contentColumn' => 'M',
              'notesColumn' => 'N'
          ],
          [
              'author' => $this->authorRepository->findOneBy(['name' => 'Person of Quality (POQ)']),
              'contentColumn' => 'U',
              'notesColumn' => null
          ],
          [
              'author' => $this->authorRepository->findOneBy(['name' => 'Thomas Rolleston']),
              'contentColumn' => 'G',
              'notesColumn' => 'H'
          ],
          [
              'author' => $this->authorRepository->findOneBy(['name' => 'James Sandford']),
              'contentColumn' => 'V',
              'notesColumn' => 'W'
          ],
          [
              'author' => $this->authorRepository->findOneBy(['name' => 'George Stanhope']),
              'contentColumn' => 'I',
              'notesColumn' => null
          ],
          [
              'author' => $this->authorRepository->findOneBy(['name' => 'Ellis Walker']),
              'contentColumn' => 'Q',
              'notesColumn' => null
          ],
          [
              'author' => $this->authorRepository->findOneBy(['name' => 'Arrian']),
              'contentColumn' => 'Y',
              'notesColumn' => null
          ],
        ];

        $enchirideonWorkId = 1;
        $enchirideonWork = $this->workRepository->find(1);
        foreach ($editionsMeta as $index => $edition) {
            /* @var $author Author */
            $author = $edition['author'];
            $matchingEditions = $author->getEditions()->filter(function ($edition) use ($enchirideonWorkId) { return $edition->getWork()->getId() == $enchirideonWorkId; });
            $editionsMeta[$index]['edition'] = $matchingEditions->first();
        }

        $startRow = 2;
        $endRow = 53;
        $labelCol = 'A';

        for ($rowIndex = $startRow; $rowIndex <= $endRow; $rowIndex++) {
            $tocLabel = $enchirideonSheet->getCell($labelCol . $rowIndex)->getValue();
            
            $tocEntry = new TocEntry();
            $tocEntry->setLabel($tocLabel);
            $tocEntry->setWork($enchirideonWork);
            $this->entityManager->persist($tocEntry);

            foreach ($editionsMeta as $editionMeta) {
                $edition = $editionMeta['edition'];
                $content = $enchirideonSheet->getCell($editionMeta['contentColumn'] . $rowIndex)->getValue();
                $notes = $editionMeta['notesColumn'] ? $enchirideonSheet->getCell($editionMeta['notesColumn'] . $rowIndex)->getValue() : null;

                $newContent = new Content();
                $newContent->setContent($content);
                $newContent->setEdition($edition);
                $newContent->setNotes($notes);
                $newContent->setTocEntry($tocEntry);

                $this->entityManager->persist($newContent);
            }
        }

        $this->entityManager->flush();
    }
}