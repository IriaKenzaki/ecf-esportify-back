<?php
namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;

#[MongoDB\Document]
class Review
{
    #[MongoDb\Id]
    private $id;

    #[MongoDB\Field(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    private $title;

    #[MongoDB\Field(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 10)]
    private $content;

    #[MongoDB\Field(type: 'int')]
    #[Assert\NotBlank]
    #[Assert\Range(min: 1, max: 5)]
    private $rating;

    #[MongoDB\Field(type: 'date')]
    private $createdAt;

    #[MongoDB\Field(type: 'string')]
    #[Assert\NotBlank]
    private $user;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?string
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

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): self
    {
        $this->rating = $rating;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function setUser(string $user): self
    {
        $this->user = $user;
        return $this;
    }
}
