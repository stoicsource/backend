<?php

namespace App\Entity;

use App\Repository\WorkRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity(repositoryClass=WorkRepository::class)
 *
 */
class Work
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({"work_details"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({"work_details"})
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=Edition::class, mappedBy="work")
     *
     * @Groups({"work_details"})
     */
    private $editions;

    /**
     * @ORM\OneToMany(targetEntity=TocEntry::class, mappedBy="work")
     *
     * @Groups({"work_details"})
     * @SerializedName("tocEntries")
     */
    private $tocEntries;

    /**
     * @ORM\ManyToMany(targetEntity=Author::class, inversedBy="works")
     *
     * @Groups({"work_details"})
     */
    private $authors;

    public function __construct()
    {
        $this->editions = new ArrayCollection();
        $this->tocEntries = new ArrayCollection();
        $this->authors = new ArrayCollection();
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

    /**
     * @return Collection|Edition[]
     */
    public function getEditions(): Collection
    {
        return $this->editions;
    }

    public function addEdition(Edition $edition): self
    {
        if (!$this->editions->contains($edition)) {
            $this->editions[] = $edition;
            $edition->setWork($this);
        }

        return $this;
    }

    public function removeEdition(Edition $edition): self
    {
        if ($this->editions->removeElement($edition)) {
            // set the owning side to null (unless already changed)
            if ($edition->getWork() === $this) {
                $edition->setWork(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TocEntry[]
     */
    public function getTocEntries(): Collection
    {
        return $this->tocEntries;
    }

    public function addTocEntry(TocEntry $tocEntry): self
    {
        if (!$this->tocEntries->contains($tocEntry)) {
            $this->tocEntries[] = $tocEntry;
            $tocEntry->setWork($this);
        }

        return $this;
    }

    public function removeTocEntry(TocEntry $tocEntry): self
    {
        if ($this->tocEntries->removeElement($tocEntry)) {
            // set the owning side to null (unless already changed)
            if ($tocEntry->getWork() === $this) {
                $tocEntry->setWork(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Author[]
     */
    public function getAuthors(): Collection
    {
        return $this->authors;
    }

    public function addAuthor(Author $author): self
    {
        if (!$this->authors->contains($author)) {
            $this->authors[] = $author;
        }

        return $this;
    }

    public function removeAuthor(Author $author): self
    {
        $this->authors->removeElement($author);

        return $this;
    }
}
