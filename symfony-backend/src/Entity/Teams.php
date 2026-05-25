<?php

namespace App\Entity;

use App\Repository\TeamsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeamsRepository::class)]
class Teams
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 100)]
    private ?string $teamLevel = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $firstCoach = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $secondCoach = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $delegate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $doctor = null;

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

    public function getTeamLevel(): ?string
    {
        return $this->teamLevel;
    }

    public function setTeamLevel(string $teamLevel): static
    {
        $this->teamLevel = $teamLevel;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getFirstCoach(): ?string
    {
        return $this->firstCoach;
    }

    public function setFirstCoach(?string $firstCoach): static
    {
        $this->firstCoach = $firstCoach;

        return $this;
    }

    public function getSecondCoach(): ?string
    {
        return $this->secondCoach;
    }

    public function setSecondCoach(?string $secondCoach): static
    {
        $this->secondCoach = $secondCoach;

        return $this;
    }

    public function getDelegate(): ?string
    {
        return $this->delegate;
    }

    public function setDelegate(?string $delegate): static
    {
        $this->delegate = $delegate;

        return $this;
    }

    public function getDoctor(): ?string
    {
        return $this->doctor;
    }

    public function setDoctor(?string $doctor): static
    {
        $this->doctor = $doctor;

        return $this;
    }
}