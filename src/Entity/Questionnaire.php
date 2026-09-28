<?php

namespace App\Entity;

use App\Enum\ApplicationStatus;
use App\Enum\AppointmentStatus;
use App\Enum\ServiceCategory;
use App\Enum\Situation;
use App\Repository\QuestionnaireRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: QuestionnaireRepository::class)]
class Questionnaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank()]
    private ?string $nationality = null;

    #[ORM\Column(enumType: Situation::class)]
    private ?Situation $situation = null;

    #[ORM\Column(enumType: ServiceCategory::class)]
    #[Assert\NotBlank()]
    private ?ServiceCategory $helpReason = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $additionalInfo = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $submittedAt = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank()]
    private ?string $firstName = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank()]
    private ?string $lastName = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank()]
    #[Assert\Email()]
    private ?string $email = null;

    #[ORM\OneToOne(mappedBy: 'questionnaire', cascade: ['persist', 'remove'])]
    private ?Application $application = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSituation(): ?Situation
    {
        return $this->situation;
    }

    public function setSituation(Situation $situation): static
    {
        $this->situation = $situation;

        return $this;
    }

    public function getSituationLabel(): ?string
    {
        return $this->situation?->label();
    }

    public function getHelpReasonLabel(): ?string
    {
        return $this->helpReason?->label();
    }

    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    public function setNationality(string $nationality): static
    {
        $this->nationality = $nationality;

        return $this;
    }

    public function getHelpReason(): ?ServiceCategory
    {
        return $this->helpReason;
    }

    public function setHelpReason(ServiceCategory $helpReason): static
    {
        $this->helpReason = $helpReason;
       return $this;
    }

    public function getAdditionalInfo(): ?string
    {
        return $this->additionalInfo;
    }

    public function setAdditionalInfo(?string $additionalInfo): static
    {
        $this->additionalInfo = $additionalInfo;

        return $this;
    }

    public function getSubmittedAt(): ?\DateTimeImmutable
    {
        return $this->submittedAt;
    }

    public function setSubmittedAt(\DateTimeImmutable $submittedAt): static
    {
        $this->submittedAt = $submittedAt;

        return $this;
    }

   public function getFirstName(): ?string
   {
       return $this->firstName;
   }

   public function setFirstName(string $firstName): static
   {
       $this->firstName = $firstName;

       return $this;
   }

   public function getLastName(): ?string
   {
       return $this->lastName;
   }

   public function setLastName(string $lastName): static
   {
       $this->lastName = $lastName;

       return $this;
   }

   public function getEmail(): ?string
   {
       return $this->email;
   }

   public function setEmail(string $email): static
   {
       $this->email = $email;

       return $this;
   }

   public function getApplication(): ?Application
   {
       return $this->application;
   }

   public function setApplication(Application $application): static
   {
       // set the owning side of the relation if necessary
       if ($application->getQuestionnaire() !== $this) {
           $application->setQuestionnaire($this);
       }

       $this->application = $application;

       return $this;
   }

   //Récupérer le statut de la demande pour l'avoir dans le dashboard
    public function getApplicationStatus(): ?ApplicationStatus
    {
        return $this->application?->getStatus();
    }

    public function setApplicationStatus(?ApplicationStatus $status): static
    {
        if ($status !== null && $this->application !== null) {
            $this->application->setStatus($status);
            $this->application->setUpdatedAt(new \DateTimeImmutable());
        }
        return $this;
    }
}
