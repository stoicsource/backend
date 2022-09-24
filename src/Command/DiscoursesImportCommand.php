<?php


namespace App\Command;


use App\Entity\Content;
use App\Entity\Edition;
use App\Entity\Import\ExtractedChapter;
use App\Entity\TocEntry;
use App\Entity\Work;
use App\Repository\AuthorRepository;
use App\Repository\BasicFootnoteRepository;
use App\Repository\TocEntryRepository;
use App\Repository\WorkRepository;
use App\Service\Import\ChapterConverter;
use App\Service\Import\FootnoteReferenceCollector;
use App\Service\Import\HtmlCleaner;
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

        $converter = new ChapterConverter(new NodeConverter(), new HtmlCleaner());
        $converter->setTargetNoteTag('sup');
        $converter->setTargetNoteAttribute('data-footnote-reference');
        $converter->setAllowedTagsAndAttributesTitle(['sup' => 'data-footnote-reference']);
        $converter->setAllowedTagsAndAttributesContent(Content::ALLOWED_HTML_TAGS_AND_ATTRIBUTES);

        $footnoteRepository = new BasicFootnoteRepository();
        $x = new DOMXPath($doc);
        $footnoteElements = $x->query("//section[@id='endnotes']/ol/li");
        foreach ($footnoteElements as $footnoteElement) {
            assert($footnoteElement instanceof DOMElement);
            $elementId = $footnoteElement->getAttribute('id');
            $globalNoteId = explode('-', $elementId)[1];

            // TODO: html and clean
            $footnoteText = $doc->saveHTML($footnoteElement);
            $footnoteText = str_replace('↩', '', $footnoteText);

            $footnoteRepository->addNote($globalNoteId, $footnoteText);
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


                $chapter = new ExtractedChapter();
                $chapter->setFootnoteTag('a');


                $headNodes = $chapterNode->getElementsByTagName('h4');
                assert($headNodes->count() > 0);
                $titleNode = $headNodes[0];
                assert($titleNode instanceof DOMNode);
                $chapter->setTitle($titleNode->ownerDocument->saveHTML($titleNode));

                $io->info("importing $tocLabel: $titleNode->nodeValue");



                // Content of the chapter
                $combinedContentHtml = '';
                $contentNodes = $chapterNode->childNodes;
                foreach ($contentNodes as $contentNode) {
                    if ($contentNode instanceof DOMElement && in_array($contentNode->tagName, ['p', 'blockquote']) ) {
                        $combinedContentHtml .= $contentNode->ownerDocument->saveHTML($contentNode);
                    }
                }
                $chapter->setContent($combinedContentHtml);

                $chapter->extractFootnotes(new FootnoteReferenceCollector(), $footnoteRepository);


                $tocEntry = $this->tocEntryRepository->findOneBy(['work' => $discoursesWork, 'label' => $tocLabel]);
                if (!$tocEntry) {
                    $tocEntry = new TocEntry();
                    $tocEntry->setWork($discoursesWork);
                    $tocEntry->setLabel($tocLabel);
                    $tocEntry->setSortOrder(($bookNr * 100) + (int)$chapterNr);
                    $this->entityManager->persist($tocEntry);
                }

                $newContent = $converter->convert($chapter);
                $newContent->setEdition($edition);
                $newContent->setTocEntry($tocEntry);

                $this->entityManager->persist($newContent);
            }
        }

        $this->entityManager->flush();

        return 0;
    }

}