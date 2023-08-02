<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]

#[ApiResource(
    normalizationContext: ['groups' => ['read:articles']]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read:articles'])]

    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:articles'])]

    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:articles'])]

    private ?string $lastname = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:articles'])]

    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:articles'])]

    private ?string $accountType = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['read:articles'])]

    private ?\DateTimeInterface $createdAt = null;

    public $confirmPassword;

    #[ORM\OneToMany(mappedBy: 'createdBy', targetEntity: Section::class)]
    private Collection $sections;

    public function __construct()
    {
        $this->sections = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getAccountType(): ?string
    {
        return $this->accountType;
    }

    public function setAccountType(string $accountType): self
    {
        $this->accountType = $accountType;

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
    public function getUsername() {
        return $this->getEmail();
    }
    function getRoles(): array {
        return [$this->getAccountType()];
    }
	
	/**
	 * Removes sensitive data from the user.
	 *
	 * This is important if, at any given point, sensitive information like
	 * the plain-text password is stored on this object.
	 *
	 * @return mixed
	 */
	function eraseCredentials() {
    }
	
	/**
	 * Returns the identifier for this user (e.g. its username or email address).
	 *
	 * @return string
	 */
	function getUserIdentifier(): string {
        return $this->getId();
    }

    /**
     * @return Collection<int, Section>
     */
    public function getSections(): Collection
    {
        return $this->sections;
    }

    public function addSection(Section $section): self
    {
        if (!$this->sections->contains($section)) {
            $this->sections->add($section);
            $section->setCreatedBy($this);
        }

        return $this;
    }

    public function removeSection(Section $section): self
    {
        if ($this->sections->removeElement($section)) {
            // set the owning side to null (unless already changed)
            if ($section->getCreatedBy() === $this) {
                $section->setCreatedBy(null);
            }
        }

        return $this;
    }
}