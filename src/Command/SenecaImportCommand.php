<?php


namespace App\Command;


use App\Entity\Author;
use App\Entity\Client;
use App\Entity\Content;
use App\Entity\Edition;
use App\Entity\TocEntry;
use App\Entity\Work;
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

class SenecaImportCommand extends Command
{
    private AuthorRepository $authorRepository;
    private EntityManagerInterface $entityManager;
    private WorkRepository $workRepository;
    protected static $defaultName = 'app:import:seneca';

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
            ->setDescription('imports Seneca')
            ->addArgument('filename', InputArgument::REQUIRED, 'file to import')
            ->addOption('authors', null, InputOption::VALUE_NONE, 'import author data')
            ->addOption('essays', null, InputOption::VALUE_NONE, 'import essays');
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

        if ($input->getOption('essays')) {
            $io->info('Importing essays Data');
            $this->importEssays($spreadsheet);
        }


        //$io->success(sprintf('Created "%d" work logs.', $addedLogCount));

        return 0;
    }

    protected function importAuthorsAndEditions(Spreadsheet $spreadsheet)
    {
        $translatorInfoSheet = $spreadsheet->getSheetByName('Tr. Info');

        $authorNameCol = 'B';
        $editionYearCol = 'C';
        $workNameCol = 'F';

        $endRow = 41;

        $senecaAuthor = $this->authorRepository->findOneBy(['name' => 'Lucius Annaeus Seneca the Younger']);

        for ($rowIndex = 2; $rowIndex <= $endRow; $rowIndex++) {
            $authorName = $translatorInfoSheet->getCell($authorNameCol . $rowIndex)->getValue();

            $author = $this->authorRepository->findOneBy(['name' => $authorName]);
            if (!$author) {
                $author = new Author();
                $author->setName($authorName);
                $this->entityManager->persist($author);
            }

            $workName = $translatorInfoSheet->getCell($workNameCol . $rowIndex)->getValue();
            $work = $this->workRepository->findOneBy(['name' => $workName]);
            if (!$work) {
                $work = new Work();
                $work->setName($workName);
                $work->addAuthor($senecaAuthor);
                $this->entityManager->persist($work);
            }

            $year = $translatorInfoSheet->getCell($editionYearCol . $rowIndex)->getValue();

            $edition = new Edition();
            $edition->setName($workName);
            $edition->setWork($work);
            $edition->setYear($year);
            $edition->addAuthor($author);

            $this->entityManager->persist($edition);

            $this->entityManager->flush();
        }
    }

    protected function importEssays(Spreadsheet $spreadsheet)
    {
        $essaysSheet = $spreadsheet->getSheetByName('Dia Txt');

        $nameMappings = [
            'De Providentia' => 'On Providence',
            'De Constantia Sapientis' => 'On the Constancy Of A Wise Man',
            'De Ira' => 'On Anger',
            'Ad Marciam, De Consolatione' => 'On Consolation - To Marcia',
            'De Vita Beata' => 'On Blessed Life',
            'De Otio' => 'On Leisure',
            'De Tranquillitate Animi' => 'The Tranquility and Peace of the Mind',
            'De Brevitae Vitae' => 'On the Shortness Of Life',
            'De Consolatione ad Polybium' => 'On Comfort',
            'Ad Helvian matrem, De consolatione' => 'On Consolation - To Helvia',
            'De Clementia' => 'On Clemency',
            'De Beneficiis' => 'On Benefits'
        ];

        $editionsMeta = [
            [
                'author' => $this->authorRepository->findOneBy(['name' => 'Thomas Lodge']),
                'contentColumn' => 'C',
                'noteColumn' => null
            ],
            [
                'author' => $this->authorRepository->findOneBy(['name' => 'Stewart Aubrey']),
                'contentColumn' => 'D',
                'noteColumn' => 'E'
            ],
            [
                'author' => $this->authorRepository->findOneBy(['name' => 'George Bennet']),
                'contentColumn' => 'F',
                'noteColumn' => null
            ],
            [
                'author' => $this->authorRepository->findOneBy(['name' => 'Nicolas Haward']),
                'contentColumn' => 'G',
                'noteColumn' => null
            ],
            [
                'author' => $this->authorRepository->findOneBy(['name' => 'Ralph Freeman']),
                'contentColumn' => 'H',
                'noteColumn' => null
            ],
            [
                'author' => $this->authorRepository->findOneBy(['name' => 'Arthur Golding']),
                'contentColumn' => 'I',
                'noteColumn' => null
            ],
            [
                'author' => $this->authorRepository->findOneBy(['name' => 'Timothy Chandler']),
                'contentColumn' => 'J',
                'noteColumn' => null
            ],
            [
                'author' => $this->authorRepository->findOneBy(['name' => 'John W. Basore']),
                'contentColumn' => 'K',
                'noteColumn' => 'L'
            ],
        ];


        $startRow = 14;
        $endRow = 536;
        $workCol = 'A';
        $labelCol = 'B';

        for ($rowIndex = $startRow; $rowIndex <= $endRow; $rowIndex++) {
            $workNameLatin = $essaysSheet->getCell($workCol . $rowIndex)->getValue();
            $tocLabel = $essaysSheet->getCell($labelCol . $rowIndex)->getValue();

            $workNameEnglish = $nameMappings[$workNameLatin];
            $work = $this->workRepository->findOneBy(['name' => $workNameEnglish]);

            $tocEntry = new TocEntry();
            $tocEntry->setLabel($tocLabel);
            $tocEntry->setWork($work);
            $this->entityManager->persist($tocEntry);


            foreach ($editionsMeta as $editionMeta) {
                /* @var $author Author */
                $author = $editionMeta['author'];
                $matchingEditions = $author->getEditions()->filter(function ($edition) use ($work) {
                    return $edition->getWork()->getId() == $work->getId();
                });
                $edition = $matchingEditions->first();

                if ($edition) {
                    $content = $essaysSheet->getCell($editionMeta['contentColumn'] . $rowIndex)->getValue();

                    if ($content > '') {
                        $newContent = new Content();
                        $newContent->setContent($content);
                        $newContent->setEdition($edition);
                        $newContent->setTocEntry($tocEntry);

                        $this->entityManager->persist($newContent);
                    }
                }
            }
        }

        $this->entityManager->flush();
    }
}