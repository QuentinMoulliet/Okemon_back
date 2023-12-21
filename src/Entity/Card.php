<?php

namespace App\Entity;

use App\Repository\CardRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=CardRepository::class)
 */
class Card
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"users"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"users"})
     */
    private $api_id;

    /**
     * @ORM\OneToMany(targetEntity=Review::class, mappedBy="card")
     * @Groups({"users"})
     */
    private $reviews;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups({"users"})
     */
    private $own;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups({"users"})
     */
    private $wish;

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
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="card")
     */
    private $users;

    public function __construct()
    {
        $this->reviews = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApiId(): ?string
    {
        return $this->api_id;
    }

    public function setApiId(string $api_id): self
    {
        $this->api_id = $api_id;

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): self
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews[] = $review;
            $review->setCard($this);
        }

        return $this;
    }

    public function removeReview(Review $review): self
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getCard() === $this) {
                $review->setCard(null);
            }
        }

        return $this;
    }

    public function getOwn(): ?int
    {
        return $this->own;
    }

    public function setOwn(?int $own): self
    {
        $this->own = $own;

        return $this;
    }

    public function getWish(): ?int
    {
        return $this->wish;
    }

    public function setWish(?int $wish): self
    {
        $this->wish = $wish;

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
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addCard($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeCard($this);
        }

        return $this;
    }
}
