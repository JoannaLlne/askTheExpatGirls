<?php

namespace App\Entity;

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

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'questionnaire')]
    #[JoinColumn(nullable: false)]
    private User $user;

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

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getUserFirstName(): ?string
    {
        return $this->user?->getFirstName();
    }

    public function getUserLastName(): ?string
    {
        return $this->user?->getLastName();
    }

}
