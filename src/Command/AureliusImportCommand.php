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

class AureliusImportCommand extends Command
{
    private AuthorRepository $authorRepository;
    private EntityManagerInterface $entityManager;
    private WorkRepository $workRepository;
    protected static $defaultName = 'app:import:aurelius';

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
            ->setDescription('imports Aurelius')
            ->addArgument('filename', InputArgument::REQUIRED, 'file to import')
            ->addOption('authors', null, InputOption::VALUE_NONE, 'import author data')
            ->addOption('meditations', null, InputOption::VALUE_NONE, 'import meditations')
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

        if ($input->getOption('meditations')) {
            $io->info('Importing Meditations Data');
            $this->importMeditations($spreadsheet);
        }


        //$io->success(sprintf('Created "%d" work logs.', $addedLogCount));

        return 0;
    }

    protected function importAuthorsAndEditions(Spreadsheet $spreadsheet)
    {
        $translatorInfoSheet = $spreadsheet->getSheetByName('Tr. Info');

        $authorNameCol = 'B';
        $editionYearCol = 'C';
        $hasMeditationsCol = 'I';
        $editionNameCol = 'P';

        $meditationsWork = $this->workRepository->findOneBy(['name' => 'The Meditations']);

        $endRow = 15;

        for ($rowIndex = 2; $rowIndex <= $endRow; $rowIndex++) {
            $authorName = $translatorInfoSheet->getCell($authorNameCol . $rowIndex)->getValue();

            $author = $this->authorRepository->findOneBy(['name' => $authorName]);
            if (!$author) {
                $author = new Author();
                $author->setName($authorName);
                $this->entityManager->persist($author);
            }

            $hasMeditations = $translatorInfoSheet->getCell($hasMeditationsCol . $rowIndex)->getValue() == 'Yes';
            if ($hasMeditations) {
                $year = $translatorInfoSheet->getCell($editionYearCol . $rowIndex)->getValue();
                $title = $translatorInfoSheet->getCell($editionNameCol . $rowIndex)->getValue();

                $edition = new Edition();
                $edition->setName($title ?? 'The Meditations');
                $edition->setWork($meditationsWork);
                $edition->setYear($year);
                $edition->addAuthor($author);

                $this->entityManager->persist($edition);
            }
        }

        $this->entityManager->flush();
    }

    protected function importMeditations(Spreadsheet $spreadsheet)
    {
        $meditationsSheet = $spreadsheet->getSheetByName('meditations');

        $editionsMeta = [
          [
              'author' => $this->authorRepository->findOneBy(['name' => 'A. S. L. Farquharson']),
              'contentColumn' => 'P'
          ],
          [
              'author' => $this->authorRepository->findOneBy(['name' => 'George William Chrystal']),
              'contentColumn' => 'O'
          ],
          [
              'author' => $this->authorRepository->findOneBy(['name' => 'G. H. Rendall']),
              'contentColumn' => 'M'
          ],
          [
              'author' => $this->authorRepository->findOneBy(['name' => 'C. R. Haines']),
              'contentColumn' => 'L'
          ],
          [
              'author' => $this->authorRepository->findOneBy(['name' => 'J. Jackson']),
              'contentColumn' => 'D'
          ],
          [
              'author' => $this->authorRepository->findOneBy(['name' => 'H. McCormac']),
              'contentColumn' => 'K'
          ],
          [
              'author' => $this->authorRepository->findOneBy(['name' => 'R. Graves']),
              'contentColumn' => 'J'
          ],
          [
              'author' => $this->authorRepository->findOneBy(['name' => 'James Moor and Francis Hutcheson']),
              'contentColumn' => 'I'
          ],
          [
              'author' => $this->authorRepository->findOneBy(['name' => 'Hastings Crossley']),
              'contentColumn' => 'H'
          ],
          [
              'author' => $this->authorRepository->findOneBy(['name' => 'Jeremy Collier']),
              'contentColumn' => 'G'
          ],
          [
              'author' => $this->authorRepository->findOneBy(['name' => 'George Long']),
              'contentColumn' => 'C'
          ],
          [
              'author' => $this->authorRepository->findOneBy(['name' => 'James Thomson']),
              'contentColumn' => 'F'
          ],
          [
              'author' => $this->authorRepository->findOneBy(['name' => 'Meric Casaubon']),
              'contentColumn' => 'E'
          ],
        ];

        $meditationsWorkId = 2;
        $meditationsWork = $this->workRepository->find($meditationsWorkId);
        foreach ($editionsMeta as $index => $edition) {
            /* @var $author Author */
            $author = $edition['author'];
            $matchingEditions = $author->getEditions()->filter(function ($edition) use ($meditationsWorkId) { return $edition->getWork()->getId() == $meditationsWorkId; });
            $editionsMeta[$index]['edition'] = $matchingEditions->first();
        }

        $startRow = 2;
        $endRow = 515;
        $labelCol = 'B';

        for ($rowIndex = $startRow; $rowIndex <= $endRow; $rowIndex++) {
            $tocLabel = $meditationsSheet->getCell($labelCol . $rowIndex)->getValue();
            
            $tocEntry = new TocEntry();
            $tocEntry->setLabel($tocLabel);
            $tocEntry->setWork($meditationsWork);
            $this->entityManager->persist($tocEntry);

            foreach ($editionsMeta as $editionMeta) {
                $edition = $editionMeta['edition'];
                $content = $meditationsSheet->getCell($editionMeta['contentColumn'] . $rowIndex)->getValue();

                if ($content > '') {
                    $newContent = new Content();
                    $newContent->setContent($content);
                    $newContent->setEdition($edition);
                    $newContent->setTocEntry($tocEntry);

                    $this->entityManager->persist($newContent);
                }
            }
        }

        $this->entityManager->flush();
    }
}