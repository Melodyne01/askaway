<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\VisitorRepository;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: VisitorRepository::class)]

#[ApiResource]
class Visitor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:articles'])]

    private ?string $ip = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:articles'])]

    private ?string $city = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:articles'])]

    private ?string $region = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:articles'])]

    private ?string $country = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['read:articles'])]

    private ?\DateTimeInterface $visitedAt = null;

    #[ORM\ManyToOne(inversedBy: 'visitors')]
    private ?Article $page = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(string $region): self
    {
        $this->region = $region;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getVisitedAt(): ?\DateTimeInterface
    {
        return $this->visitedAt;
    }

    public function setVisitedAt(\DateTimeInterface $visitedAt): self
    {
        $this->visitedAt = $visitedAt;

        return $this;
    }

    public function getPage(): ?Article
    {
        return $this->page;
    }

    public function setPage(?Article $page): self
    {
        $this->page = $page;

        return $this;
    }
}
