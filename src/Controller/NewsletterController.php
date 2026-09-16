<?php

namespace App\Controller;

use App\Entity\NewsletterSubscriber;
use App\Form\NewsletterSubscriberType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NewsletterController extends AbstractController
{
    #[Route('/newsletter/subscribe/{placement}',
        name: 'newsletter_subscribe', defaults: ['placement' => 'default'],
        methods: ['GET', 'POST'])]
    public function subscribe(Request $request, EntityManagerInterface $em,
        FormFactoryInterface $formFactory, string $placement): Response {
        $subscriber = new NewsletterSubscriber();

        $form = $formFactory->createNamed(
            'newsletter_' . $placement,
            NewsletterSubscriberType::class,
            $subscriber,
            ['action' => $this->generateUrl('newsletter_subscribe', ['placement' => $placement])]
        );

        $form->handleRequest($request);
        $isAjax = $request->isXmlHttpRequest();

        if ($form->isSubmitted() && $form->isValid()) {
            $existing = $em->getRepository(NewsletterSubscriber::class)
                ->findOneBy(['email' => $subscriber->getEmail()]);

            if (!$existing) {
                $em->persist($subscriber);
                $em->flush();
                $message = 'You are now subscribed to !';
            } else {
                $message = 'Sorry but this email is already subscribed !';
            }

            if ($isAjax) {
                return $this->json(['success' => true, 'message' => $message]);
            }

            $this->addFlash('newsletter_success', $message);
            return $this->redirectToRoute('main_index');
        }

        if ($form->isSubmitted() && $isAjax) {
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $errors[] = $error->getMessage();
            }
            return $this->json(['success' => false, 'errors' => $errors], 422);
        }

        return $this->render('newsletter/_form.html.twig', [
            'newsletterForm' => $form,
        ]);
    }

    #[Route('/newsletter/unsubscribe/{token}', name: 'newsletter_unsubscribe')]
    public function unsubscribe(string $token, EntityManagerInterface $em): Response
    {
        $subscriber = $em->getRepository(NewsletterSubscriber::class)
            ->findOneBy(['unsubscribeToken' => $token]);

        if ($subscriber) {
            $em->remove($subscriber);
            $em->flush();
        }
        return $this->render('newsletter/unsubscribed.html.twig');
    }
}
