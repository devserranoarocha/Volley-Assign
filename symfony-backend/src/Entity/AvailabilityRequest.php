<?php

namespace App\Entity;

use App\Repository\AvailabilityRequestRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AvailabilityRequestRepository::class)]
#[ORM\Table(name: 'availability_request')]
class AvailabilityRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'request_id')]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $title = null; // Ej: "Disponibilidad - Jornada 12"

    #[ORM\Column(type: Types::DATE_MUTABLE, name: 'start_date')]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, name: 'end_date')]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(length: 30)]
    private ?string $status = 'Abierta'; // Ej: 'Abierta', 'Cerrada'

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'request', targetEntity: AvailabilityRequestPeriod::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $periods;

    public function __construct()
    {
        $this->periods = new ArrayCollection();
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

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
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

    /**
     * @return Collection<int, AvailabilityRequestPeriod>
     */
    public function getPeriods(): Collection
    {
        return $this->periods;
    }

    public function addPeriod(AvailabilityRequestPeriod $period): self
    {
        if (!$this->periods->contains($period)) {
            $this->periods->add($period);
            $period->setRequest($this);
        }
        return $this;
    }

    public function removePeriod(AvailabilityRequestPeriod $period): self
    {
        if ($this->periods->removeElement($period)) {
            // Configura el lado "muchos" a null si se elimina de la colección
            if ($period->getRequest() === $this) {
                $period->setRequest(null);
            }
        }
        return $this;
    }
}