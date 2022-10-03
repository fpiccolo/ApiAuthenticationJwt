<?php

namespace App\Entity;

use App\Enum\Roles;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @codeCoverageIgnore
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 255)]
    private string $firstName;

    #[ORM\Column(length: 255)]
    private string $lastName;

    #[ORM\Column(length: 255, unique: true)]
    private string $email;

    #[ORM\Column(length: 255)]
    private string $password;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserToken::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $token;

    #[ORM\Column]
    private array $roles = [];

    public function __construct(
        string $firstName,
        string $lastName,
        string $email,
        string $password,
        array $roles
    )
    {
        $this->token = new ArrayCollection();
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->password = $password;
        $this->roles = $roles;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
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

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return Collection<int, UserToken>
     */
    public function getToken(): Collection
    {
        return $this->token;
    }

    public function addToken(UserToken $jwt): self
    {
        if (!$this->token->contains($jwt)) {
            $this->token->add($jwt);
            $jwt->setUser($this);
        }

        return $this;
    }

    public function removeToken(UserToken $jwt): self
    {
        if ($this->token->removeElement($jwt)) {
            if ($jwt->getUser() === $this) {
                $jwt->setUser(null);
            }
        }

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUserIdentifier(): string
    {
        return $this->getEmail();
    }

    public function isAdmin(): bool
    {
        return in_array(Roles::ROLE_ADMIN->value, $this->roles);
    }
}
