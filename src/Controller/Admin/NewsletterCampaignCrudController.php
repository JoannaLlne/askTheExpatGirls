<?php

// src/Controller/Admin/NewsletterCampaignCrudController.php
namespace App\Controller\Admin;

use App\Entity\NewsletterCampaign;
use App\Entity\NewsletterSubscriber;
use App\Message\SendNewsletterMessage;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class NewsletterCampaignCrudController extends AbstractCrudController
{
    public function __construct(
        private EntityManagerInterface $em,
        private MessageBusInterface $bus,
    ) {}

    public static function getEntityFqcn(): string
    {
        return NewsletterCampaign::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('subject', 'Title');
        yield TextEditorField::new('content', 'Content');
        yield DateTimeField::new('sentAt', 'Sent :')->hideOnForm();
    }

    #[AdminRoute('/{entityId}/send', name: 'send')]
    #[IsGranted('ROLE_ADMIN')]
    public function send(AdminContext $context): Response
    {
        $campaign = $context->getEntity()->getInstance();
        $referrer = $context->getRequest()->headers->get('referer');

        if ($campaign->getSentAt() !== null) {
            $this->addFlash('warning', 'This newsletter has already been sent.');
            return $this->redirect($referrer);
        }

        $subscribers = $this->em->getRepository(NewsletterSubscriber::class)->findAll();

        foreach ($subscribers as $subscriber) {
            $this->bus->dispatch(new SendNewsletterMessage(
                $subscriber->getEmail(),
                $campaign->getSubject(),
                $campaign->getContent(),
                $subscriber->getUnsubscribeToken(),
            ));
        }

        $campaign->setSentAt(new \DateTimeImmutable());
        $this->em->flush();

        $this->addFlash('success', sprintf('Newsletter sent to %d subscribers.', count($subscribers)));

        return $this->redirect($referrer);
    }

    public function configureActions(Actions $actions): Actions
    {
        $send = Action::new('send', 'Send')
            ->linkToCrudAction('send')
            ->displayIf(fn (NewsletterCampaign $c) => $c->getSentAt() === null);

        return $actions
            ->add(Crud::PAGE_INDEX, $send)
            ->add(Crud::PAGE_EDIT, $send)
            ->add(Crud::PAGE_DETAIL, $send);
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
