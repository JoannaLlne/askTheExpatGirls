<?php
namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\Application;
use App\Entity\User;
use App\Enum\AppointmentStatus;
use App\Service\AppointmentSlotFinder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/application/{id}/appointment')]
#[IsGranted('ROLE_USER')]
class AppointmentController extends AbstractController
{
    public function __construct(
        #[Autowire('%appointments_enabled%')]
        private bool $appointmentsEnabled
    ) {}

    #[Route('', name: 'appointment_book', methods: ['GET', 'POST'])]
//Voter pour vérifier que le getUser est bien $user qui est connecté
    #[IsGranted('VIEW', subject: 'application')]
    public function book(Application $application, Request $request,
                         AppointmentSlotFinder $slotFinder, EntityManagerInterface $em): Response
    {
        // Désactiver la prise de rendez-vous
        if (!$this->appointmentsEnabled) {
            throw $this->createNotFoundException('This feature is not available yet.');
        }

        //On vient chercher les créneaux dispo
        $availableSlots = $slotFinder->getAvailableSlotsForNextDays();

        //Si méthode POST -> rdv confirmé
        if ($request->isMethod('POST')) {
            //on récupère la valeur 'slot' du formulaire
            $chosenSlot = $request->request->get('slot'); // format ISO envoyé par le formulaire
            //On transforme la donnée en DateTimeImmutable
            $date = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, $chosenSlot);
            //Si retourne false, mauvais format donc erreur
            if (!$date) {
                $this->addFlash('error', 'Please choose a valid slot.');
                return $this->redirectToRoute('appointment_book', ['id' => $application->getId()]);
            }

            // Sécurité : on revérifie que ce créneau est toujours libre au moment de la validation
            $stillAvailable = $slotFinder->getAvailableSlotsForDay($date->setTime(0, 0));
            $isFree = array_filter($stillAvailable, fn($slot) => $slot == $date);

            if (empty($isFree)) {
                $this->addFlash('error', 'Sorry, this slot has just been booked. Please choose another one.');
                return $this->redirectToRoute('account_appointment_book', ['id' => $application->getId()]);
            }

            $appointment = new Appointment();
            $appointment->setDate($date);
            $appointment->setStatus(AppointmentStatus::BOOKED);
            $appointment->setApplication($application);

            $em->persist($appointment);
            $em->flush();

            $this->addFlash('success', 'Your appointment has been booked.');
            return $this->redirectToRoute('user_profile', ['id' => $this->getUser()->getId()]);
        }

//Si méthode GET ou POST echouée, affiche formulaire avec créneaux dispo

        return $this->render('appointment/book.html.twig', [
            'application' => $application,
            'availableSlots' => $availableSlots,
        ]);
    }

    // ANNULATION RENDEZ-VOUS
    #[Route('/cancel', name: 'appointment_cancel', requirements: ['id' => '\d+'], methods: ['POST'])]
    //#[IsGranted('VIEW', subject: 'application')]
    public function cancel(Appointment $appointment, Request $request, EntityManagerInterface $em): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();

        //CSRF token
        if ($this->isCsrfTokenValid('cancel-' . $appointment->getId(), $request->request->get('_token'))) {
            $em->remove($appointment);
            $em->flush();

            $this->addFlash('success', 'Appointment successfully cancelled !');
        } else {
            $this->addFlash('danger', 'Sorry, something went wrong.');
        }

        return $this->redirectToRoute('user_profile', ['id' => $this->getUser()->getId()]);
    }
}
