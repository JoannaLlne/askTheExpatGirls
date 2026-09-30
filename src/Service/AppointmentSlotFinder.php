<?php
namespace App\Service;

use App\Repository\AppointmentRepository;

class AppointmentSlotFinder
{
    private const START_HOUR = 9; //1er rdv à 9h
    private const END_HOUR = 17; //dernier rdv à 16h, la personne a jusqu'à 17h
    private const CLOSED_WEEKDAYS = [6, 7]; // samedi, dimanche (format ISO-8601)

public function __construct(
    private AppointmentRepository $appointmentRepository
) {}

/**
* Retourne les créneaux disponibles (DateTimeImmutable) pour une date donnée.
*/
public function getAvailableSlotsForDay(\DateTimeImmutable $day): array
{
    //Si le jour est plus grand que 5 donc samedi, dimanche, pas de créneau
    if ((int) $day->format('N') > 5) {
    return []; // week-end, aucun créneau
        }

    //méthode du repository qui retourne tableau des heures réservées ce jour
    $bookedHours = $this->appointmentRepository->findBookedHoursForDay($day);

    // boucle chaque heure possible (9-16) et si, pas dans la liste, on l'ajoute
    $slots = [];
for ($hour = self::START_HOUR; $hour < self::END_HOUR; $hour++) {
    if (!in_array($hour, $bookedHours, true)) {
        //création d'un objet à cette date, avec cet heure et 0 minute
        $slots[] = $day->setTime($hour, 0);
    }
}
//tableau des créneaux libres ce jour-là
return $slots;
}

/**
* Retourne les créneaux disponibles pour les N prochains jours ouvrés.
*/
public function getAvailableSlotsForNextDays(int $numberOfDays = 30): array
{
    //Construit le calendrier des créneaux disponibles
    $slots = [];
    //Pour ne jamais proposé de rendez-vous pour le jour-même
    $day = new \DateTimeImmutable('tomorrow');
    $checked = 0;

    //Pour parcourir chaque jour sur les 30 jours à venir
    while ($checked < $numberOfDays) {
        //Visibilité des créneaux libres
        $daySlots = $this->getAvailableSlotsForDay($day);

        //Si le jour à au moins un créneau de libre, on l'affiche
        if (!empty($daySlots)) {
            $slots[$day->format('Y-m-d')] = $daySlots;
        }
        //Avance au jour suivant
        $day = $day->modify('+1 day');
        $checked++;
    }
    return $slots;
}
}
