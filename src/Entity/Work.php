<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Repository\WorkRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=WorkRepository::class)
 */
class Work
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=Edition::class, mappedBy="work")
     */
    private $editions;

    /**
     * @ORM\OneToMany(targetEntity=TocEntry::class, mappedBy="work")
     */
    private $tocEntries;

    public function __construct()
    {
        $this->editions = new ArrayCollection();
        $this->tocEntries = new ArrayCollection();
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
}
