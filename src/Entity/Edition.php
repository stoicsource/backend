<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\EditionRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    collectionOperations: ['get'],
    itemOperations: ['get'],
    normalizationContext: [
        'groups' => ['readEdition']
    ]
)]
#[ApiFilter(SearchFilter::class, properties: ['work' => 'exact'])]
#[ORM\Entity(repositoryClass: EditionRepository::class)]
class Edition
{
    public const QUALITY_POOR = 1; // poor, formatting faulty
    public const QUALITY_SOLID = 6; // solid, no apparent faults
    public const QUALITY_EDITED = 8; // flawless, edited manually if needed
    public const QUALITY_EXCELLENT = 10;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\ManyToOne(targetEntity: Work::class, inversedBy: 'editions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Work $work;

    #[ORM\OneToMany(mappedBy: 'edition', targetEntity: Chapter::class)]
    private Collection $chapters;

    #[ORM\Column(type: 'string', length: 12, nullable: true)]
    private string $year;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private string $source;

    #[ORM\Column(type: 'string', length: 3)]
    private string $language;

    #[ORM\Column(type: 'smallint')]
    private int $quality;

    #[ORM\ManyToOne(targetEntity: Author::class, inversedBy: 'editions')]
    #[ORM\JoinColumn(nullable: false)]
    private Author $author;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $contributor = [];

    #[ORM\Column(type: 'boolean')]
    private bool $hasContent;

    #[ORM\Column(type: 'text')]
    private string $copyright;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $internalComment;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $sources = [];

    public function __construct()
    {
        $this->chapters = new ArrayCollection();
    }

    public function __toString(){
        return $this->work . ', ' . $this->author . '(' . $this->year. ')';
    }

    #[Groups(["readEdition"])]
    public function getId(): int
    {
        return $this->id;
    }

    #[Groups(["readEdition"])]
    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    #[Groups(["readEdition"])]
    public function getWork(): Work
    {
        return $this->work;
    }

    public function setWork(Work $work): self
    {
        $this->work = $work;

        return $this;
    }

    /**
     * @return Collection|Chapter[]
     */
    public function getChapters(): Collection
    {
        return $this->chapters;
    }

    public function addChapter(Chapter $chapter): self
    {
        if (!$this->chapters->contains($chapter)) {
            $this->chapters[] = $chapter;
            $chapter->setEdition($this);
        }

        return $this;
    }

    public function removeChapter(Chapter $chapter): self
    {
        if ($this->chapters->removeElement($chapter)) {
            // set the owning side to null (unless already changed)
            if ($chapter->getEdition() === $this) {
                $chapter->setEdition(null);
            }
        }

        return $this;
    }

    #[Groups(["readEdition"])]
    public function getYear(): ?string
    {
        return $this->year;
    }

    public function setYear(?string $year): self
    {
        $this->year = $year;

        return $this;
    }

    #[Groups(["readEdition"])]
    public function getSource(): ?string
    {
        return $this->source;
    }

    /**
     * @deprecated use setSources instead
     */
    public function setSource(?string $source): self
    {
        $this->source = $source;

        return $this;
    }

    #[Groups(["readEdition"])]
    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    #[Groups(["readEdition"])]
    public function getQuality(): int
    {
        return $this->quality;
    }

    public function setQuality(int $quality): self
    {
        $this->quality = $quality;

        return $this;
    }

    #[Groups(["readEdition"])]
    public function getAuthor(): Author
    {
        return $this->author;
    }

    public function setAuthor(Author $author): self
    {
        $this->author = $author;

        return $this;
    }

    #[Groups(["readEdition"])]
    public function getContributor(): ?array
    {
        return $this->contributor;
    }

    public function setContributor(?array $contributor): self
    {
        $this->contributor = $contributor;

        return $this;
    }

    public function getContributorPlainText(): string
    {
        return $this->contributor ? json_encode($this->contributor) : "";
    }

    public function setContributorPlainText(?string $contributor): self
    {
        $this->contributor = $contributor ? json_decode($contributor) : null;

        return $this;
    }

    #[Groups(["readEdition"])]
    public function getHasContent(): ?bool
    {
        return $this->hasContent;
    }

    public function setHasContent(bool $hasContent): self
    {
        $this->hasContent = $hasContent;

        return $this;
    }

    #[Groups(["readEdition"])]
    public function getCopyright(): string
    {
        return $this->copyright;
    }

    public function setCopyright(string $copyright): self
    {
        $this->copyright = $copyright;

        return $this;
    }

    public function getInternalComment(): ?string
    {
        return $this->internalComment;
    }

    public function setInternalComment(?string $internalComment): self
    {
        $this->internalComment = $internalComment;

        return $this;
    }

    #[Groups(["readEdition"])]
    public function getSources(): ?array
    {
        return $this->sources;
    }

    public function setSources(?array $sources): self
    {
        $this->sources = $sources;

        return $this;
    }

    public function getSourcesPlainText(): string
    {
        return json_encode($this->sources ?? []);
    }

    public function setSourcesPlainText(string $sources): self
    {
        $this->sources = json_decode($sources);

        return $this;
    }

}
