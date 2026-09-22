<?php

namespace App\Entity;

use App\Enum\AppointmentStatus;
use App\Repository\AppointmentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AppointmentRepository::class)]
class Appointment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(enumType: AppointmentStatus::class)]
    private ?AppointmentStatus $status = null;

    public function getStatus(): ?AppointmentStatus
    {
        return $this->status;
    }

    public function setStatus(?AppointmentStatus $status): void
    {
        $this->status = $status;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }
}
