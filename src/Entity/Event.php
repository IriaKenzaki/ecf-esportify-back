<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["user_events", "participant_details"])]
    private ?int $id = null;

    #[ORM\Column(length: 64)]
    #[Groups(["user_events", "participant_details"])]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Groups(["user_events", "participant_details"])]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $players = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateTimeStart = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateTimeEnd = null;

    #[ORM\Column(length: 255)]
    #[Groups(["user_events"])]
    private ?string $createdBy = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column]
    private ?bool $visibility = null;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: ListParticipant::class, cascade: ['persist', 'remove'])]
    #[Groups(["participant_details"])]
    private Collection $listParticipants;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: Blacklist::class, cascade: ['persist', 'remove'])]
    #[Groups("blacklist_details")]
    private Collection $blacklist;

    #[ORM\OneToMany(targetEntity: Score::class, mappedBy: 'event', cascade: ['persist', 'remove'])]
    private Collection $scores;

    public function __construct()
    {
        $this->blacklist = new ArrayCollection();
        $this->scores = new ArrayCollection();
        $this->listParticipants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getPlayers(): ?int
    {
        return $this->players;
    }

    public function setPlayers(int $players): static
    {
        $this->players = $players;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getDateTimeStart(): ?\DateTimeInterface
    {
        return $this->dateTimeStart;
    }

    public function setDateTimeStart(\DateTimeInterface $date_time_start): static
    {
        $this->dateTimeStart = $date_time_start;
        return $this;
    }

    public function getDateTimeEnd(): ?\DateTimeInterface
    {
        return $this->dateTimeEnd;
    }

    public function setDateTimeEnd(\DateTimeInterface $date_time_end): static
    {
        $this->dateTimeEnd = $date_time_end;
        return $this;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setCreatedBy(string $createdBy): static
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function isVisibility(): ?bool
    {
        return $this->visibility;
    }

    public function setVisibility(bool $visibility): static
    {
        $this->visibility = $visibility;
        return $this;
    }

    public function getListParticipants(): Collection
    {
        return $this->listParticipants;
    }

    public function getBlacklist(): Collection
    {
        return $this->blacklist;
    }

    public function getScores(): Collection
    {
        return $this->scores;
    }
}
