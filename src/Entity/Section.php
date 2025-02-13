<?php

namespace App\Entity;

use App\Entity\Article;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\SectionRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SectionRepository::class)]

#[ApiResource(
    normalizationContext: ['groups' => ['read:articles']]
)]
class Section
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read:articles'])]

    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['read:articles'])]

    private ?string $title = null;

    #[ORM\Column(length: 1000)]
    #[Groups(['read:articles'])]

    private ?string $body = null;

    #[ORM\Column(length: 1000, nullable: true)]
    #[Groups(['read:articles'])]

    private ?string $link = null;

    #[ORM\ManyToOne(inversedBy: 'section')]
    
    private ?Article $article = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['read:articles'])]

    private ?string $image = null;

    #[ORM\Column]
    #[Groups(['read:articles'])]

    private ?int $position = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['read:articles'])]

    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'sections')]
    #[Groups(['read:articles'])]

    private ?User $createdBy = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['read:articles'])]

    private ?string $linkName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['read:articles'])]

    private ?string $imageSource = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        $this->article = $article;

        return $this;
    }
    public function __toString() : string
    {
        if ($this->title){
            return $this->title . " - ". $this->position;
        }else{
            return "Sans titre - " . $this->position;
        }
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getLinkName(): ?string
    {
        return $this->linkName;
    }

    public function setLinkName(?string $linkName): self
    {
        $this->linkName = $linkName;

        return $this;
    }

    public function getImageSource(): ?string
    {
        return $this->imageSource;
    }

    public function setImageSource(?string $imageSource): self
    {
        $this->imageSource = $imageSource;

        return $this;
    }
}