<?php

namespace App\Controller\Dashboard;

use App\Controller\Admin\QuestionnaireCrudController;
use App\Controller\Admin\UserCrudController;
use App\Entity\User;
use App\Entity\Questionnaire;
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
        yield MenuItem::section('Gestion');
        yield MenuItem::linkTo(UserCrudController::class, 'Users', 'fa fa-tags')->setAction(Action::INDEX);;
        yield MenuItem::linkTo(QuestionnaireCrudController::class, 'Questionnaires', 'fa fa-tags')->setAction(Action::INDEX);;
    }
}
