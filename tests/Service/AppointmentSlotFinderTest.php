<?php

namespace App\Tests\Service;

use App\Repository\AppointmentRepository;
use App\Service\AppointmentSlotFinder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AppointmentSlotFinderTest extends TestCase
{
    #[Test]
    public function noSlotsOnWeekend(): void
    {
        $repository = $this->createStub(AppointmentRepository::class);
        $finder = new AppointmentSlotFinder($repository);

        $saturday = new \DateTimeImmutable('next saturday');

        $this->assertSame([], $finder->getAvailableSlotsForDay($saturday));
    }

    #[Test]
    public function excludesAlreadyBookedHours(): void
    {
        // createStub pour fournir une valeur de retour toute faite
        $repository = $this->createStub(AppointmentRepository::class);
        //Crea faux repository,quand appel -> renvoi 9, 14 comme heures bookees
        $repository->method('findBookedHoursForDay')->willReturn([9, 14]);
        $finder = new AppointmentSlotFinder($repository);

        //Un lundi
        $monday = new \DateTimeImmutable('next monday');
        $slots = $finder->getAvailableSlotsForDay($monday);

        //transformer tableau d'objet DateTime en tableau d'heures avec entiers
        $hours = array_map(fn ($slot) => (int) $slot->format('G'), $slots);

        //Verification
        // Ne doit pas apparaitre : 9h, 14h
        $this->assertNotContains(9, $hours);
        $this->assertNotContains(14, $hours);
        // Doit apparaitre : 10h, 6 creneaux au total
        $this->assertContains(10, $hours);
        $this->assertCount(6, $slots);
    }
}
