<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\EditionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EditionRepository::class)
 */
#[ApiResource(
    collectionOperations: ['get'],
    itemOperations: ['get'],
)]
#[ApiFilter(SearchFilter::class, properties: ['work' => 'exact'])]
class Edition
{
    public const QUALITY_POOR = 1; // poor, formatting faulty
    public const QUALITY_SOLID = 6; // solid, no apparent faults
    public const QUALITY_EDITED = 8; // flawless, edited manually if needed
    public const QUALITY_EXCELLENT = 10; // flawless, uses html formatting if necessary

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=Work::class, inversedBy="editions")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     */
    private $work;

    /**
     * @ORM\OneToMany(targetEntity=Content::class, mappedBy="edition")
     */
    private $contents;

    /**
     * @ORM\Column(type="string", length=12, nullable=true)
     *
     */
    private $year;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     */
    private $source;

    /**
     * @ORM\Column(type="string", length=3)
     *
     */
    private $language;

    /**
     * @ORM\Column(type="smallint")
     *
     */
    private $quality;

    /**
     * @ORM\ManyToOne(targetEntity=Author::class, inversedBy="editions")
     * @ORM\JoinColumn(nullable=false)
     *
     */
    private $author;

    /**
     * @ORM\Column(type="json", nullable=true)
     *
     */
    private $contributor = [];

    public function __construct()
    {
        $this->contents = new ArrayCollection();
    }

    public function __toString(){
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getWork(): ?Work
    {
        return $this->work;
    }

    public function setWork(?Work $work): self
    {
        $this->work = $work;

        return $this;
    }

    /**
     * @return Collection|Content[]
     */
    public function getContents(): Collection
    {
        return $this->contents;
    }

    public function addContent(Content $content): self
    {
        if (!$this->contents->contains($content)) {
            $this->contents[] = $content;
            $content->setEdition($this);
        }

        return $this;
    }

    public function removeContent(Content $content): self
    {
        if ($this->contents->removeElement($content)) {
            // set the owning side to null (unless already changed)
            if ($content->getEdition() === $this) {
                $content->setEdition(null);
            }
        }

        return $this;
    }

    public function getYear(): ?string
    {
        return $this->year;
    }

    public function setYear(?string $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getQuality(): ?int
    {
        return $this->quality;
    }

    public function setQuality(int $quality): self
    {
        $this->quality = $quality;

        return $this;
    }

    public function getAuthor(): ?Author
    {
        return $this->author;
    }

    public function setAuthor(?Author $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getContributor(): ?array
    {
        return $this->contributor;
    }

    public function setContributor(?array $contributor): self
    {
        $this->contributor = $contributor;

        return $this;
    }
}
