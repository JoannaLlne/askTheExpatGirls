<?php

namespace App\Entity;

use App\Enum\AppointmentStatus;
use App\Repository\AppointmentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AppointmentRepository::class)]
class Appointment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotNull()]
    #[Assert\GreaterThan('now', message: 'The appointment date must be in the future.')]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(enumType: AppointmentStatus::class)]
    #[Assert\NotNull()]
    private ?AppointmentStatus $status = null;

    #[ORM\ManyToOne(targetEntity: Application::class, inversedBy: 'appointments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull()]
    private ?Application $application = null;

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

    public function getApplication(): ?Application
    {
        return $this->application;
    }

    public function setApplication(?Application $application): static
    {
        $this->application = $application;

        return $this;
    }
}
