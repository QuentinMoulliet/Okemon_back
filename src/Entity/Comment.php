<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=CommentRepository::class)
 */
class Comment
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"users", "comments"})
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Groups({"users", "comments"})
     * @Assert\NotBlank(message="Veuillez renseigner un commentaire")\Length(
     *      min = 1,
     *      max = 5000,
     *      minMessage = "Le commentaire doit contenir minimum 1 caractère.",
     *      maxMessage = "Le commentaire doit contenir maximum 5000 caractères.")
     */
    private $content;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"users", "comments"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"users", "comments"})
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=Review::class, inversedBy="comment")
     */
    private $review;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="comment")
     */
    private $user;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getReview(): ?Review
    {
        return $this->review;
    }

    public function setReview(?Review $review): self
    {
        $this->review = $review;

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
