<?php

namespace App\Entity;

use App\Repository\RefereeResponseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RefereeResponseRepository::class)]
#[ORM\Table(name: 'referee_response')]
class RefereeResponse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Conectamos con Referee. Apunta al $id primario automáticamente.
    #[ORM\ManyToOne(targetEntity: Referee::class)]
    #[ORM\JoinColumn(name: 'referee_id', nullable: false, onDelete: 'CASCADE')]
    private ?Referee $referee = null;

    // Conectamos con AvailabilityRequestPeriod.
    #[ORM\ManyToOne(targetEntity: AvailabilityRequestPeriod::class, inversedBy: 'refereeResponses')]
    #[ORM\JoinColumn(name: 'period_id', referencedColumnName: 'period_id', nullable: false, onDelete: 'CASCADE')]
    private ?AvailabilityRequestPeriod $period = null;

    #[ORM\Column(name: 'is_available', type: 'boolean')]
    private ?bool $isAvailable = true;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $displacement = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReferee(): ?Referee
    {
        return $this->referee;
    }

    public function setReferee(?Referee $referee): self
    {
        $this->referee = $referee;
        return $this;
    }

    public function getPeriod(): ?AvailabilityRequestPeriod
    {
        return $this->period;
    }

    public function setPeriod(?AvailabilityRequestPeriod $period): self
    {
        $this->period = $period;
        return $this;
    }

    public function isIsAvailable(): ?bool
    {
        return $this->isAvailable;
    }

    public function setIsAvailable(bool $isAvailable): self
    {
        $this->isAvailable = $isAvailable;
        return $this;
    }

    public function getDisplacement(): ?string
    {
        return $this->displacement;
    }

    public function setDisplacement(?string $displacement): self
    {
        $this->displacement = $displacement;
        return $this;
    }
}