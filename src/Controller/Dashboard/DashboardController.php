<?php

namespace App\Controller\Dashboard;

use App\Controller\Admin\ExpatPackagesCrudController;
use App\Controller\Admin\NewsletterCampaignCrudController;
use App\Controller\Admin\NewsletterSubscriberCrudController;
use App\Controller\Admin\QuestionnaireCrudController;
use App\Controller\Admin\ServiceCrudController;
use App\Controller\Admin\UserCrudController;
use App\Entity\NewsletterCampaign;
use App\Entity\NewsletterSubscriber;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
        // ou : return $this->redirectToRoute('admin_user_index');
    }
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Administration - Ask the Expat Girls');
            //->setFaviconPath('images/favicon.svg');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Home', 'fa fa-home');
        yield MenuItem::section('Services & Packages');
        yield MenuItem::linkTo(ServiceCrudController::class, 'Services', 'fa fa-tags')->setAction(Action::INDEX);
        yield MenuItem::linkTo(ExpatPackagesCrudController::class, 'Expat Packages', 'fa fa-box')->setAction(Action::INDEX);

        yield MenuItem::section('Gestion');
        yield MenuItem::linkTo(UserCrudController::class, 'Users', 'fa fa-user')->setAction(Action::INDEX);
        yield MenuItem::linkTo(QuestionnaireCrudController::class, 'Questionnaires', 'fa fa-file')->setAction(Action::INDEX);

        yield MenuItem::section('Newsletter');
        yield MenuItem::linkTo(NewsletterSubscriberCrudController::class, 'Subscribers', 'fa fa-users')->setAction(Action::INDEX);
        yield MenuItem::linkTo(NewsletterCampaignCrudController::class, 'Campaigns', 'fa fa-envelope')->setAction(Action::INDEX);

    }
}
