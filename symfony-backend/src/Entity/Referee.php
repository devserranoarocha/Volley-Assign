<?php

namespace App\Entity;

use App\Repository\RefereeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RefereeRepository::class)]
class Referee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $last_name = null;

    #[ORM\Column]
    private ?int $base_venue_id = null;

    #[ORM\Column(length: 255)]
    private ?string $referre_level = null;

    #[ORM\Column(nullable: true)]
    private ?int $incompatibility_id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $other_class = null;

    #[ORM\Column(length: 255)]
    private ?string $others = null;

    // Nota: Corregido length en enteros ya que no aplica nativamente en tipos int de Doctrine
    #[ORM\Column]
    private ?int $season_counter = null;

    #[ORM\Column]
    private ?int $week_counter = null;

    // 1. Añadimos la relación OneToOne con la entidad User
    #[ORM\OneToOne(targetEntity: User::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: true, onDelete: "SET NULL")]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): static
    {
        $this->last_name = $last_name;

        return $this;
    }

    public function getBaseVenueId(): ?int
    {
        return $this->base_venue_id;
    }

    public function setBaseVenueId(int $base_venue_id): static
    {
        $this->base_venue_id = $base_venue_id;

        return $this;
    }

    public function getReferreLevel(): ?string
    {
        return $this->referre_level;
    }

    public function setReferreLevel(string $referre_level): static
    {
        $this->referre_level = $referre_level;

        return $this;
    }

    public function getIncompatibilityId(): ?int
    {
        return $this->incompatibility_id;
    }

    public function setIncompatibilityId(int $incompatibility_id): static
    {
        $this->incompatibility_id = $incompatibility_id;

        return $this;
    }

    public function getOtherClass(): ?string
    {
        return $this->other_class;
    }

    public function setOtherClass(string $other_class): static
    {
        $this->other_class = $other_class;

        return $this;
    }

    public function getOthers(): ?string
    {
        return $this->others;
    }

    public function setOthers(string $others): static
    {
        $this->others = $others;

        return $this;
    }

    public function getSeasonCounter(): ?int
    {
        return $this->season_counter;
    }

    public function setSeasonCounter(int $season_counter): static
    {
        $this->season_counter = $season_counter;

        return $this;
    }

    public function getWeekCounter(): ?int
    {
        return $this->week_counter;
    }

    public function setWeekCounter(int $week_counter): static
    {
        $this->week_counter = $week_counter;

        return $this;
    }

    // 2. Reemplazamos los métodos antiguos de user_id por los del objeto User completo
    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}