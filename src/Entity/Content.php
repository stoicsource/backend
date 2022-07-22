<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\ContentRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity(repositoryClass=ContentRepository::class)
 */
#[ApiResource(
    collectionOperations: ['get'],
    itemOperations: ['get'],
)]
#[ApiFilter(SearchFilter::class, properties: ['tocEntry' => 'exact', 'edition' => 'exact'])]
class Content
{
    const CONTENT_TYPE_TEXT = 1;
    const CONTENT_TYPE_HTML = 2;

    const CONTENT_TYPE_NAMES = [
      self::CONTENT_TYPE_TEXT => 'text',
      self::CONTENT_TYPE_HTML => 'html'
    ];

    const ALLOWED_HTML_TAGS = ['<p>', '<blockquote>', '<sup>', '<b>'];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({"content_details"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=TocEntry::class, inversedBy="contents")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Groups({"content_details"})
     * @SerializedName("tocEntry")
     */
    private $tocEntry;

    /**
     * @ORM\ManyToOne(targetEntity=Edition::class, inversedBy="contents")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Groups({"content_details"})
     */
    private $edition;

    /**
     * @ORM\Column(type="text")
     *
     * @Groups({"content_details"})
     */
    private $content;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Groups({"content_details"})
     */
    private $notes;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Groups({"content_details"})
     */
    private $title;

    /**
     * @ORM\Column(type="smallint")
     *
     */
    private $contentType;

    /**
     * @Groups({"content_details"})
     * @SerializedName("contentType")
     * @Accessor(getter="getFormattedContentType")
     */
    private $formattedContentType;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTocEntry(): ?TocEntry
    {
        return $this->tocEntry;
    }

    public function setTocEntry(?TocEntry $tocEntry): self
    {
        $this->tocEntry = $tocEntry;

        return $this;
    }

    public function getEdition(): ?Edition
    {
        return $this->edition;
    }

    public function setEdition(?Edition $edition): self
    {
        $this->edition = $edition;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContentType(): ?int
    {
        return $this->contentType;
    }

    public function setContentType(int $contentType): self
    {
        $this->contentType = $contentType;

        return $this;
    }

    public function getFormattedContentType(): string
    {
        if (array_key_exists($this->contentType, self::CONTENT_TYPE_NAMES)) {
            return self::CONTENT_TYPE_NAMES[$this->contentType];
        }
        return '';
    }
}
