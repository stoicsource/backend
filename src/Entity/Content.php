<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ContentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ContentRepository::class)
 */
class Content
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=TocEntry::class, inversedBy="contents")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $tocEntry;

    /**
     * @ORM\ManyToOne(targetEntity=Edition::class, inversedBy="contents")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $edition;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $notes;

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
}
