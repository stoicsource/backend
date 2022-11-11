<?php

namespace App\Entity;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use App\Repository\TocEntryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(operations: [new Get(), new GetCollection()], normalizationContext: ['groups' => ['readTocEntry']])]
#[ApiFilter(filterClass: SearchFilter::class, properties: ['work' => 'exact'])]
#[ORM\Entity(repositoryClass: TocEntryRepository::class)]
class TocEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Work::class, inversedBy: 'tocEntries')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Work $work;

    #[ORM\OneToMany(mappedBy: 'tocEntry', targetEntity: Chapter::class)]
    private Collection $chapters;

    #[ORM\Column(type: 'string', length: 255)]
    private string $label;

    #[ORM\Column(type: 'integer')]
    private int $sortOrder;

    public function __construct()
    {
        $this->chapters = new ArrayCollection();
    }

    public function __toString(){
        return $this->label;
    }

    #[Groups(["readTocEntry"])]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[Groups(["readTocEntry"])]
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
            $chapter->setTocEntry($this);
        }

        return $this;
    }

    public function removeChapter(Chapter $chapter): self
    {
        if ($this->chapters->removeElement($chapter)) {
            // set the owning side to null (unless already changed)
            if ($chapter->getTocEntry() === $this) {
                $chapter->setTocEntry(null);
            }
        }

        return $this;
    }

    #[Groups(["readTocEntry"])]
    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    #[Groups(["readTocEntry"])]
    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }
}
