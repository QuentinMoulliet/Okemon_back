<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use App\DataFixtures\Provider\OkemonProvider;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Faker\Factory;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"email"}, message="Il y a déjà un compte à cette adresse")
 * @UniqueEntity(fields={"nickname"}, message="Il y a déjà un compte à ce nom")
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"users"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Groups({"users"})
     * @Assert\NotBlank(message="Veuillez renseigner un email")
     * @Assert\Email(
     *      message = "L'adresse email n'est pas valide."
     * )
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     * @Groups({"users"})
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"users"})
     * @Assert\NotBlank(message="Veuillez renseigner un pseudo")
     * @Assert\Length(
     *      min = 3,
     *      max = 17,
     *      minMessage = "Le pseudo doit avoir au moins 3 caractères.",
     *      maxMessage = "Le pseudo doit avoir au maximum 17 caractères.")
     */
    private $nickname;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups({"users"})
     * @Assert\Type(
     *     type="integer",
     *     message="la valeur doit etre un entier"
     * )
     * @Assert\Range(
     *     min=0,
     *     max=99,
     *     notInRangeMessage="L'âge doit être compris entre 0 et 99.")
     */
    private $age;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     * @Groups({"users"})
     */
    private $country;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"users"})
     * @Assert\Length(
     *      max = 5000,
     *      maxMessage = "La description doit contenir maximum 5000 caractères.")
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"users"})
     * @Assert\Length(
     *      min = 1,
     *      max = 70,
     *      minMessage = "La phrase doit contenir minimum 1 caractère.",
     *      maxMessage = "La phrase doit contenir maximum 70 caractères.")
     */
    private $catchphrase;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"users"})
     */
    private $image;

    /**
     * @ORM\Column(type="smallint")
     * @Groups({"users"})
     */
    private $status;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"users"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"users"})
     */
    private $updatedAt;

    /**
     * @ORM\ManyToMany(targetEntity=Card::class, inversedBy="users")
     */
    private $card;

    /**
     * @ORM\OneToMany(targetEntity=Review::class, mappedBy="user", cascade={"persist"}, orphanRemoval=true)
     * @Groups({"users"})
     */
    private $review;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="user", orphanRemoval=true)
     * @Groups({"users"})
     */
    private $comment;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"users"})
     */
    private $contact;

    public function __construct()
    {
        // Create instance of faker generator
        $faker = Factory::create('fr_FR');
        // Give our Provider to faker
        $faker->addProvider(new OkemonProvider());

        $this->card = new ArrayCollection();
        $this->review = new ArrayCollection();
        $this->comment = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
        $this->status = 1;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
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
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(string $nickname): self
    {
        $this->nickname = $nickname;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(?int $age): self
    {
        $this->age = $age;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCatchphrase(): ?string
    {
        return $this->catchphrase;
    }

    public function setCatchphrase(?string $catchphrase): self
    {
        $this->catchphrase = $catchphrase;

        return $this;
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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return Collection<int, Card>
     */
    public function getCard(): Collection
    {
        return $this->card;
    }

    public function addCard(Card $card): self
    {
        if (!$this->card->contains($card)) {
            $this->card[] = $card;
        }

        return $this;
    }

    public function removeCard(Card $card): self
    {
        $this->card->removeElement($card);

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReview(): Collection
    {
        return $this->review;
    }

    public function addReview(Review $review): self
    {
        if (!$this->review->contains($review)) {
            $this->review[] = $review;
            $review->setUser($this);
        }

        return $this;
    }

    public function removeReview(Review $review): self
    {
        if ($this->review->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getUser() === $this) {
                $review->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComment(): Collection
    {
        return $this->comment;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comment->contains($comment)) {
            $this->comment[] = $comment;
            $comment->setUser($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comment->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getUser() === $this) {
                $comment->setUser(null);
            }
        }

        return $this;
    }

    public function getContact(): ?string
    {
        return $this->contact;
    }

    public function setContact(?string $contact): self
    {
        $this->contact = $contact;

        return $this;
    }
}
