<?php

namespace App\Entity;

use App\Repository\TocEntryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=TocEntryRepository::class)
 */
class TocEntry
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({"work_details", "content_details"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Work::class, inversedBy="tocEntries")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $work;

    /**
     * @ORM\OneToMany(targetEntity=Content::class, mappedBy="tocEntry")
     */
    private $contents;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({"work_details"})
     */
    private $label;

    public function __construct()
    {
        $this->contents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
            $content->setTocEntry($this);
        }

        return $this;
    }

    public function removeContent(Content $content): self
    {
        if ($this->contents->removeElement($content)) {
            // set the owning side to null (unless already changed)
            if ($content->getTocEntry() === $this) {
                $content->setTocEntry(null);
            }
        }

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }
}
