<?php

namespace App\Entity;

use AllowDynamicProperties;
use App\Enum\ServiceCategory;
use App\Enum\ServiceTiming;
use App\Repository\ServiceRepository;
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
    #[Assert\NotBlank()]
    private ?int $price = null;

    #[ORM\Column(type: 'json')]
    private array $serviceCategory = [];

    #[ORM\Column(type: 'json')]
    private array $serviceTiming = [];

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'service', targetEntity: Application::class)]
    private Collection $applications;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

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

    public function getApplication(): ?Application
    {
        return $this->Application;
    }

    public function setApplication(Application $Application): static
    {
        $this->Application = $Application;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }
}
