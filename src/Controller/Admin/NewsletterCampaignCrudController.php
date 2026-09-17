<?php

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
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class NewsletterCampaignCrudController extends AbstractCrudController
{
    public function __construct(
        private EntityManagerInterface $em,
        private MessageBusInterface $bus,
        private AdminUrlGenerator $adminUrlGenerator,
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

    // --- Logique métier commune, appelée par les deux actions ci-dessous ---
    private function dispatchCampaign(NewsletterCampaign $campaign): int
    {
        if ($campaign->getSentAt() !== null) {
            return 0; // déjà envoyée, on ne refait rien
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

        return count($subscribers);
    }

    // Action individuelle
    #[AdminRoute('/{entityId}/send', name: 'send')]
    #[IsGranted('ROLE_ADMIN')]
    public function send(AdminContext $context): Response
    {
        $campaign = $context->getEntity()->getInstance();
        $referrer = $context->getRequest()->headers->get('referer');

        $count = $this->dispatchCampaign($campaign);

        if ($count === 0) {
            $this->addFlash('warning', 'This newsletter has already been sent.');
        } else {
            $this->em->flush();
            $this->addFlash('success', sprintf('Newsletter sent to %d subscribers.', $count));
        }

        return $this->redirect($referrer);
    }

    // Action selon selection
    #[AdminRoute('/send-selected', name: 'send_selected')]
    #[IsGranted('ROLE_ADMIN')]
    public function sendSelected(BatchActionDto $batchActionDto): Response
    {
        $totalSent = 0;
        $skipped = 0;

        foreach ($batchActionDto->getEntityIds() as $id) {
            $campaign = $this->em->getRepository(NewsletterCampaign::class)->find($id);

            if (!$campaign) {
                continue;
            }

            $count = $this->dispatchCampaign($campaign);

            if ($count === 0) {
                $skipped++;
            } else {
                $totalSent += $count;
            }
        }

        $this->em->flush();

        if ($totalSent > 0) {
            $this->addFlash('success', sprintf('%d email(s) sent.', $totalSent));
        }
        if ($skipped > 0) {
            $this->addFlash('warning', sprintf('%d campaign already sent', $skipped));
        }

        $url = $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();

        return $this->redirect($url);
    }

    public function configureActions(Actions $actions): Actions
    {
        $send = Action::new('send', 'Send')
            ->linkToCrudAction('send')
            ->displayIf(fn (NewsletterCampaign $c) => $c->getSentAt() === null);

        $sendBatch = Action::new('sendSelected', 'Send the selected campaigns')
            ->linkToCrudAction('sendSelected')
            ->createAsBatchAction();

        return $actions
            ->add(Crud::PAGE_INDEX, $send)
            ->add(Crud::PAGE_EDIT, $send)
            ->addBatchAction($sendBatch);
    }
}
