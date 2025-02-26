<?php

namespace App\Entity;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use App\Dto\ChapterDto;
use App\Repository\ChapterRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[ApiResource(
    operations: [new Get(), new GetCollection()],
    normalizationContext: ['groups' => ['readChapter']],
    paginationClientItemsPerPage: true,
    paginationEnabled: true,
    paginationItemsPerPage: 30,
    paginationMaximumItemsPerPage: 100)
]
#[ApiFilter(filterClass: SearchFilter::class, properties: ['tocEntry' => 'exact', 'edition' => 'exact'])]
#[ORM\Entity(repositoryClass: ChapterRepository::class)]
class Chapter
{
    const CONTENT_TYPE_PLAIN = 1;
    const CONTENT_TYPE_HTML = 2;
    const CONTENT_TYPE_JSON = 3;

    const CONTENT_TYPE_NAMES = [
        self::CONTENT_TYPE_PLAIN => 'text',
        self::CONTENT_TYPE_HTML => 'html',
        self::CONTENT_TYPE_JSON => 'json'
    ];

    // const ALLOWED_HTML_TAGS = ['<p>', '<blockquote>', '<sup>', '<b>', '<i>'];
    const ALLOWED_HTML_TAGS_AND_ATTRIBUTES = [
        'p' => [],
        'blockquote' => [],
        'sup' => ['data-footnote-reference'],
        'b' => [],
        'i' => [],
        'br' => []
    ];

    const FOOTNOTE_REFERENCE_TAG = 'sup';
    const FOOTNOTE_REFERENCE_ID_ATTRIBUTE = 'data-footnote-reference';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: TocEntry::class, inversedBy: 'chapters')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private $tocEntry;

    #[ORM\ManyToOne(targetEntity: Edition::class, inversedBy: 'chapters')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private $edition;

    #[ORM\Column(type: 'text')]
    private $content;

    #[ORM\Column(type: 'text', nullable: true)]
    private $notes;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $title;

    #[ORM\Column(type: 'smallint')]
    private $contentFormat;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $notesFormat = null;

    public static function fromDto(ChapterDto $source): Chapter
    {
        $newChapter = new self();
        $newChapter->setTitle($source->getTitle());
        $newChapter->setContent($source->getContent());
        $newChapter->setNotes($source->getFootnotes() ? json_encode(
            array_map(static function ($content, $noteId) {
                return [
                    'id' => $noteId,
                    'content' => $content
                ];
            }, $source->getFootnotes(), array_keys($source->getFootnotes()))

            , JSON_THROW_ON_ERROR) : null);
        $newChapter->setNotesFormat(self::CONTENT_TYPE_JSON);
        return $newChapter;
    }

    #[Groups(["readChapter"])]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[Groups(["readChapter"])]
    public function getTocEntry(): ?TocEntry
    {
        return $this->tocEntry;
    }

    public function setTocEntry(?TocEntry $tocEntry): self
    {
        $this->tocEntry = $tocEntry;

        return $this;
    }

    #[Groups(["readChapter"])]
    public function getEdition(): ?Edition
    {
        return $this->edition;
    }

    public function setEdition(?Edition $edition): self
    {
        $this->edition = $edition;

        return $this;
    }

    #[Groups(["readChapter"])]
    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    #[Groups(["readChapter"])]
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    #[Groups(["readChapter"])]
    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContentFormat(): ?int
    {
        return $this->contentFormat;
    }

    public function setContentFormat(int $contentFormat): self
    {
        $this->contentFormat = $contentFormat;

        return $this;
    }

    #[Groups(["readChapter"])]
    #[SerializedName("contentType")]
    public function getFormattedContentType(): string
    {
        if (array_key_exists($this->contentFormat, self::CONTENT_TYPE_NAMES)) {
            return self::CONTENT_TYPE_NAMES[$this->contentFormat];
        }
        return '';
    }

    public function getNotesFormat(): ?int
    {
        return $this->notesFormat;
    }

    public function setNotesFormat(int $notesFormat): self
    {
        $this->notesFormat = $notesFormat;

        return $this;
    }

    #[Groups(["readChapter"])]
    #[SerializedName("notesFormat")]
    public function getFormattedNoteFormat(): string
    {
        if (array_key_exists($this->notesFormat, self::CONTENT_TYPE_NAMES)) {
            return self::CONTENT_TYPE_NAMES[$this->notesFormat];
        }
        return '';
    }

}
