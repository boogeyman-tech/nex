<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", unique: true)]
    private string $email;

    // 🔧 FIXED: SQLite-safe storage of roles
    #[ORM\Column(type: "text", nullable: true)]
    private ?string $roles = null;

    #[ORM\Column(type: "string")]
    private string $password;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: "string", length: 10, nullable: true)]
    private ?string $countryCode = null;

    #[ORM\Column(type: "string", length: 20, nullable: true)]
    private ?string $mobileNumber = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $organizationName = null;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $jobRole = null;


    #[ORM\Column(type: "string", length: 500, nullable: true)]
    private ?string $jobDescription = null;

    #[ORM\Column(type: "datetime_immutable")]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getUsername(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles ? json_decode($this->roles, true) : [];

        // ensure basic role
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = json_encode($roles);
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials()
    {
        // Clear temporary sensitive data here if needed
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

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(?string $countryCode): self
    {
        $this->countryCode = $countryCode;
        return $this;
    }

    public function getMobileNumber(): ?string
    {
        return $this->mobileNumber;
    }

    public function setMobileNumber(?string $mobileNumber): self
    {
        $this->mobileNumber = $mobileNumber;
        return $this;
    }

    public function getOrganizationName(): ?string
    {
        return $this->organizationName;
    }

    public function setOrganizationName(?string $organizationName): self
    {
        $this->organizationName = $organizationName;
        return $this;
    }

    public function getJobRole(): ?string
    {
        return $this->jobRole;
    }

    public function setJobRole(?string $jobRole): self
    {
        $this->jobRole = $jobRole;
        return $this;
    }


    public function getJobDescription(): ?string
    {
        return $this->jobDescription;
    }

    public function setJobDescription(?string $jobDescription): self
    {
        $this->jobDescription = $jobDescription;
        return $this;
    }

    /**
     * Check if user has admin privileges
     */
    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->getRoles());
    }

    /**
     * Check if user has specific role
     */
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles());
    }

    /**
     * Get all user data for admin access (masked sensitive fields)
     */
    public function getPublicData(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'organizationName' => $this->organizationName,
            'jobRole' => $this->jobRole,
            'countryCode' => $this->countryCode,
            'mobileNumber' => $this->mobileNumber ? substr($this->mobileNumber, -4) : null, // Mask phone
            'createdAt' => $this->createdAt ?? null,
            'isAdmin' => $this->isAdmin(),
        ];
    }

    /**
     * Check if this user can access another user's data
     */
    public function canAccessUserData(User $targetUser): bool
    {
        // Users can always access their own data
        if ($this === $targetUser) {
            return true;
        }

        // Admin users can access any user's data
        return $this->isAdmin();
    }

    /**
     * Get user's display name (fallback to email)
     */

    public function getDisplayName(): string
    {
        return $this->name ?: $this->email;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}




