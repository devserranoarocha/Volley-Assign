<?php

namespace App\Entity;

use App\Repository\AvailabilityRequestPeriodRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AvailabilityRequestPeriodRepository::class)]
#[ORM\Table(name: 'availability_request_period')]
class AvailabilityRequestPeriod
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'period_id')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: AvailabilityRequest::class, inversedBy: 'periods')]
    #[ORM\JoinColumn(name: 'request_id', referencedColumnName: 'request_id', nullable: false, onDelete: 'CASCADE')]
    private ?AvailabilityRequest $request = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 50)]
    private ?string $period = null;

    // 1. Añadido 'cascade: ['remove']' y 'orphanRemoval: true'
    #[ORM\OneToMany(mappedBy: 'period', targetEntity: RefereeResponse::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $refereeResponses;

    public function __construct()
    {
        $this->refereeResponses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRequest(): ?AvailabilityRequest
    {
        return $this->request;
    }

    public function setRequest(?AvailabilityRequest $request): self
    {
        $this->request = $request;
        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getPeriod(): ?string
    {
        return $this->period;
    }

    public function setPeriod(string $period): self
    {
        $this->period = $period;
        return $this;
    }

    // ------------------------------------------------------------------
    // 2. NUEVOS MÉTODOS AÑADIDOS PARA MANEJAR RESPUESTAS DE ÁRBITROS
    // ------------------------------------------------------------------

    /**
     * @return Collection<int, RefereeResponse>
     */
    public function getRefereeResponses(): Collection
    {
        return $this->refereeResponses;
    }

    public function addRefereeResponse(RefereeResponse $refereeResponse): self
    {
        if (!$this->refereeResponses->contains($refereeResponse)) {
            $this->refereeResponses->add($refereeResponse);
            $refereeResponse->setPeriod($this);
        }

        return $this;
    }

    public function removeRefereeResponse(RefereeResponse $refereeResponse): self
    {
        if ($this->refereeResponses->removeElement($refereeResponse)) {
            if ($refereeResponse->getPeriod() === $this) {
                $refereeResponse->setPeriod(null);
            }
        }

        return $this;
    }
}