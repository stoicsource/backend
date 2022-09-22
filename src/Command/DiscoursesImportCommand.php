<?php


namespace App\Command;


use App\Entity\Content;
use App\Entity\Edition;
use App\Entity\FootnoteIdMap;
use App\Entity\TocEntry;
use App\Entity\Work;
use App\Repository\AuthorRepository;
use App\Repository\BasicFootnoteRepository;
use App\Repository\TocEntryRepository;
use App\Repository\WorkRepository;
use App\Service\Import\NodeConverter;
use Doctrine\ORM\EntityManagerInterface;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DiscoursesImportCommand extends Command
{
    protected static $defaultName = 'app:import:discourses';

    public function __construct(
        public AuthorRepository $authorRepository,
        public WorkRepository $workRepository,
        public TocEntryRepository $tocEntryRepository,
        public EntityManagerInterface $entityManager,
        public NodeConverter $nodeConverter
    )
    {
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
        // new ConsoleLogger($io);

        $url = 'https://standardebooks.org/ebooks/epictetus/discourses/george-long/text/single-page';


        $discoursesWork = $this->workRepository->findOneBy(['name' => 'Discourses']);
        if (!$discoursesWork) {
            $authorEpictetus = $this->authorRepository->findOneBy(['name' => 'Epictetus']);
            $discoursesWork = new Work();
            $discoursesWork->setAuthor($authorEpictetus);
            $discoursesWork->setName('Discourses');
            $discoursesWork->setUrlSlug('discourses');
            $this->entityManager->persist($discoursesWork);
        }

        $authorName = 'George Long';
        $author = $this->authorRepository->findOneBy(['name' => $authorName]);

        $edition = new Edition();
        $edition->setName('The Discourses of Epictetus');
        $edition->setWork($discoursesWork);
        $edition->setYear(1877);
        $edition->setLanguage('eng');
        $edition->setSource($url);
        $edition->setAuthor($author);
        $edition->setQuality(Edition::QUALITY_SOLID);
        $edition->setHasContent(true);
        $edition->setCopyright('Public Domain');
        $this->entityManager->persist($edition);


        $doc = new DOMDocument();
        @$doc->loadHTMLFile($url);


        $footnoteRepository = new BasicFootnoteRepository();
        $x = new DOMXPath($doc);
        $footnoteElements = $x->query("//section[@id='endnotes']/ol/li");
        foreach ($footnoteElements as $footnoteElement) {
            assert($footnoteElement instanceof DOMElement);
            $elementId = $footnoteElement->getAttribute('id');
            $globalNoteId = explode('-', $elementId)[1];
            $footnoteRepository->addNote($globalNoteId, $footnoteElement->nodeValue);
        }


        for ($bookNr = 1; $bookNr <= 4; $bookNr++) {
//         for ($bookNr = 1; $bookNr <= 1; $bookNr++) {
            $bookSection = $doc->getElementById('book-' . $bookNr);
            assert($bookSection !== null);

            foreach ($bookSection->getElementsByTagName('section') as $chapterNode) {
                assert($chapterNode instanceof DOMElement);
                $chapterNodeId = $chapterNode->getAttribute('id');
                assert($chapterNodeId !== null);
                $idElements = explode('-', $chapterNodeId);
                assert(is_array($idElements));
                assert(array_key_exists(2, $idElements));
                $chapterNr = $idElements[2];

                $tocLabel = "$bookNr.$chapterNr";

                $footnoteIdMap = new FootnoteIdMap();


                $headNodes = $chapterNode->getElementsByTagName('h4');
                assert($headNodes->count() > 0);
                $titleNode = $headNodes[0];
                assert($titleNode instanceof DOMNode);

                $this->nodeConverter->convertAllChildren($titleNode, 'a', null, 'sup', 'data-footnote-reference');
                $footnoteIdMap->adjustNoteIds($titleNode, 'sup', 'data-footnote-reference');
                $chapterTitle = $titleNode->ownerDocument->saveHTML($titleNode);
                $chapterTitle = strip_tags($chapterTitle, ['sup']);

                $io->info("importing $tocLabel: $chapterTitle");


                // Content of the chapter
                $combinedContentHtml = '';
                $pNodes = $chapterNode->getElementsByTagName('p');
                foreach ($pNodes as $pNode) {
                    assert($pNode instanceof DOMElement);

                    $this->nodeConverter->convertAllChildren($pNode, 'a', null, 'sup', 'data-footnote-reference');
                    $footnoteIdMap->adjustNoteIds($pNode, 'sup', 'data-footnote-reference');

                    $combinedContentHtml .= $pNode->ownerDocument->saveHTML($pNode);
                }

                $combinedContentHtml = strip_tags($combinedContentHtml, Content::ALLOWED_HTML_TAGS);


                // NoteCollection
                $localNoteCollection = new BasicFootnoteRepository();
                foreach ($footnoteIdMap->getAllLocalIds() as $localNoteId) {
                    $globalNoteId = $footnoteIdMap->localToGlobal($localNoteId);
                    $noteContent = str_replace('↩', '', $footnoteRepository->getById($globalNoteId));
                    $localNoteCollection->addNote($localNoteId, $noteContent);
                }

                $entryNotes = '';
                foreach ($localNoteCollection->getAll() as $noteId => $noteContent) {
                    $entryNotes .= "<li data-footnote-id=\"$noteId\">" . $noteContent . '</li>';
                }
                $entryNotes = $entryNotes > '' ? ('<ol>' . $entryNotes . '</ol>') : null;


                $tocEntry = $this->tocEntryRepository->findOneBy(['work' => $discoursesWork, 'label' => $tocLabel]);
                if (!$tocEntry) {
                    $tocEntry = new TocEntry();
                    $tocEntry->setWork($discoursesWork);
                    $tocEntry->setLabel($tocLabel);
                    $tocEntry->setSortOrder(($bookNr * 100) + (int)$chapterNr);
                    $this->entityManager->persist($tocEntry);
                }

                $newContent = new Content();
                $newContent->setContent($combinedContentHtml);
                $newContent->setTitle($chapterTitle);
                $newContent->setEdition($edition);
                $newContent->setTocEntry($tocEntry);
                $newContent->setNotes($entryNotes);
                $newContent->setContentType(Content::CONTENT_TYPE_HTML);

                $this->entityManager->persist($newContent);
            }
        }

        $this->entityManager->flush();

        return 0;
    }

}