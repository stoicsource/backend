<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Filter\RandomOrderFilter;
use App\Repository\ContentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[ApiResource(
    collectionOperations: ['get'],
    itemOperations: ['get'],
    attributes: [
        'pagination_enabled' => true,
        'pagination_client_items_per_page' => true,
        'pagination_items_per_page' => 30,
        'pagination_maximum_items_per_page' => 100
    ],
    normalizationContext: [
        'groups' => ['read']
    ]
)]
#[ApiFilter(SearchFilter::class, properties: ['tocEntry' => 'exact', 'edition' => 'exact'])]
#[ApiFilter(RandomOrderFilter::class)]
#[ORM\Entity(repositoryClass: ContentRepository::class)]
class Content
{
    const CONTENT_TYPE_PLAIN = 1;
    const CONTENT_TYPE_HTML = 2;
    const CONTENT_TYPE_JSON = 3;

    const CONTENT_TYPE_NAMES = [
      self::CONTENT_TYPE_PLAIN => 'text',
      self::CONTENT_TYPE_HTML => 'html',
      self::CONTENT_TYPE_JSON => 'json'
    ];

    const ALLOWED_HTML_TAGS = ['<p>', '<blockquote>', '<sup>', '<b>'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: TocEntry::class, inversedBy: 'contents')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private $tocEntry;

    #[ORM\ManyToOne(targetEntity: Edition::class, inversedBy: 'contents')]
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

    #[Groups(["read"])]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[Groups(["read"])]
    public function getTocEntry(): ?TocEntry
    {
        return $this->tocEntry;
    }

    public function setTocEntry(?TocEntry $tocEntry): self
    {
        $this->tocEntry = $tocEntry;

        return $this;
    }

    #[Groups(["read"])]
    public function getEdition(): ?Edition
    {
        return $this->edition;
    }

    public function setEdition(?Edition $edition): self
    {
        $this->edition = $edition;

        return $this;
    }

    #[Groups(["read"])]
    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    #[Groups(["read"])]
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    #[Groups(["read"])]
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

    #[Groups(["read"])]
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

    #[Groups(["read"])]
    #[SerializedName("noteFormat")]
    public function getFormattedNoteFormat(): string
    {
        if (array_key_exists($this->notesFormat, self::CONTENT_TYPE_NAMES)) {
            return self::CONTENT_TYPE_NAMES[$this->notesFormat];
        }
        return '';
    }

}
