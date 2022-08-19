<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\AuthorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AuthorRepository::class)
 */
#[ApiResource(
    collectionOperations: ['get'],
    itemOperations: ['get'],
)]
class Author
{
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
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     */
    private $shortName;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     *
     */
    private $urlSlug;

    /**
     * @ORM\OneToMany(targetEntity=Work::class, mappedBy="author")
     */
    private $works;

    /**
     * @ORM\OneToMany(targetEntity=Edition::class, mappedBy="author")
     */
    private $editions;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     *
     */
    private $yearsAlive;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     */
    private $summary;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     */
    private $moreInfoUrl;

    public function __construct()
    {
        $this->works = new ArrayCollection();
        $this->editions = new ArrayCollection();
    }

    public function __toString(){
        return $this->shortName ?? $this->name;
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

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(?string $shortName): self
    {
        $this->shortName = $shortName;

        return $this;
    }

    public function getUrlSlug(): ?string
    {
        return $this->urlSlug;
    }

    public function setUrlSlug(string $urlSlug): self
    {
        $this->urlSlug = $urlSlug;

        return $this;
    }

    /**
     * @return Collection<int, Work>
     */
    public function getWorks(): Collection
    {
        return $this->works;
    }

    public function addWork(Work $work): self
    {
        if (!$this->works->contains($work)) {
            $this->works[] = $work;
            $work->setAuthor($this);
        }

        return $this;
    }

    public function removeWork(Work $work): self
    {
        if ($this->works->removeElement($work)) {
            // set the owning side to null (unless already changed)
            if ($work->getAuthor() === $this) {
                $work->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Edition>
     */
    public function getEditions(): Collection
    {
        return $this->editions;
    }

    public function addEdition(Edition $edition): self
    {
        if (!$this->editions->contains($edition)) {
            $this->editions[] = $edition;
            $edition->setAuthor($this);
        }

        return $this;
    }

    public function removeEdition(Edition $edition): self
    {
        if ($this->editions->removeElement($edition)) {
            // set the owning side to null (unless already changed)
            if ($edition->getAuthor() === $this) {
                $edition->setAuthor(null);
            }
        }

        return $this;
    }

    public function getYearsAlive(): ?string
    {
        return $this->yearsAlive;
    }

    public function setYearsAlive(?string $yearsAlive): self
    {
        $this->yearsAlive = $yearsAlive;

        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): self
    {
        $this->summary = $summary;

        return $this;
    }

    public function getMoreInfoUrl(): ?string
    {
        return $this->moreInfoUrl;
    }

    public function setMoreInfoUrl(?string $moreInfoUrl): self
    {
        $this->moreInfoUrl = $moreInfoUrl;

        return $this;
    }
}
