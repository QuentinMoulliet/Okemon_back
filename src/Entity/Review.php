<?php

namespace App\Entity;

use App\Repository\ReviewRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ReviewRepository::class)
 */
class Review
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"users", "reviews"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"users", "reviews"})
     * @Assert\NotBlank(message="Veuillez renseigner un titre")\Length(
     *      min = 1, 
     *      max = 255,
     *      minMessage = "Le pseudo doit contenir minimum 1 caractère.",
     *      maxMessage = "Le pseudo doit contenir maximum 255 caractères.")
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     * @Groups({"users", "reviews"})
     * @Assert\NotBlank(message="Veuillez renseigner un contenu")\Length(
     *      min = 1, 
     *      max = 5000,
     *      minMessage = "Le contenu doit contenir minimum 1 caractère.",
     *      maxMessage = "Le contenu doit contenir maximum 5000 caractères.")
     */
    private $content;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"users", "reviews"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"users", "reviews"})
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="review", orphanRemoval=true)
     */
    private $comment;

    /**
     * @ORM\ManyToOne(targetEntity=Card::class, inversedBy="reviews")
     */
    private $card;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="review")
     */
    private $user;

    public function __construct()
    {
        $this->comment = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
    }

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

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
            $comment->setReview($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comment->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getReview() === $this) {
                $comment->setReview(null);
            }
        }

        return $this;
    }

    public function getCard(): ?Card
    {
        return $this->card;
    }

    public function setCard(?Card $card): self
    {
        $this->card = $card;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
