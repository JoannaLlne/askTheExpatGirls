<?php

namespace App\Entity;

use AllowDynamicProperties;
use App\Enum\ServiceCategory;
use App\Enum\ServiceTiming;
use App\Repository\ServiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[AllowDynamicProperties]
#[ORM\Entity(repositoryClass: ServiceRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Service
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank()]
    #[Assert\Length(max: 180)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank()]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero(message: 'The price cannot be negative.')]
    private ?int $price = null;

    #[ORM\Column(type: 'json')]
    //Choisir au moins une catégorie
    #[Assert\Count(min: 1, minMessage: 'Please select at least one category.')]
    private array $serviceCategory = [];

    #[ORM\Column(type: 'json')]
    #[Assert\Count(min: 1, minMessage: 'Please select at least one choice.')]
    private array $serviceTiming = [];

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $archived = false;

    #[ORM\OneToMany(targetEntity: Application::class, mappedBy: 'service')]
    //mappedBy : va voir la propriété service sur l'entité application (qui porte la relation)
    private Collection $applications;

    public function __construct()
    {
        $this->applications = new ArrayCollection();
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }



    public function getApplications(): Collection
    {
        return $this->applications;
    }

    public function setApplications(Collection $applications): void
    {
        $this->applications = $applications;
    }

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getServiceCategory(): array
    {
        return $this->serviceCategory;
    }

    public function setServiceCategory(array $serviceCategory): static
    {
        $this->serviceCategory = array_map(
            fn ($c) => $c instanceof ServiceCategory ? $c->value : $c,
            $serviceCategory
        );

        return $this;
    }

    public function getServiceCategoryEnums(): array
    {
        return array_map(fn (string $v) => ServiceCategory::from($v), $this->serviceCategory);
    }

    public function getServiceTiming(): array
    {
        return $this->serviceTiming;
    }

    public function setServiceTiming(array $serviceTiming): static
    {
        $this->serviceTiming = array_map(
            fn ($t) => $t instanceof ServiceTiming ? $t->value : $t,
            $serviceTiming
        );

        return $this;
    }

    public function getServiceTimingEnums(): array
    {
        return array_map(fn (string $v) => ServiceTiming::from($v), $this->serviceTiming);
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function addApplication(Application $application): static
    {
        //Vérifie que Application n'est pas dans la liste
        if (!$this->applications->contains($application)) {
            //Ajout à la collection
            $this->applications->add($application);
            //Appelle pour que l'objet Application sache qu'il est lié à CE Service
            $application->setService($this);
        }
        return $this;
    }

    public function removeApplication(Application $application): static
    {
        if ($this->applications->removeElement($application)) {
            if ($application->getService() === $this) {
                $application->setService(null);
            }
        }
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image ?? 'default-service.jpg';
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function isArchived(): bool
    {
        return $this->archived;
    }

    public function setArchived(bool $archived): static
    {
        $this->archived = $archived;

        return $this;
    }
}
