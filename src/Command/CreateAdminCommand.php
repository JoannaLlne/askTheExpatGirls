<?php
namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Create an new user with ROLE_ADMIN',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('first name', InputArgument::REQUIRED, 'Admin first name')
            ->addArgument('last name', InputArgument::REQUIRED, 'Admin last name')
            ->addArgument('email', InputArgument::REQUIRED, 'Admin email')
            ->addArgument('phone', InputArgument::REQUIRED, 'Admin phone number')
            ->addArgument('password', InputArgument::REQUIRED, 'Admin password')
            ->addOption('secret', null, InputOption::VALUE_REQUIRED, 'Jeton de sécurité');

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $expectedSecret = $_ENV['ADMIN_CREATION_SECRET'] ?? null;

        if (!$expectedSecret || $input->getOption('secret') !== $expectedSecret) {
            $io->error('Failed');
            return Command::FAILURE;
        }

        $firstName = $input->getArgument('first name');
        $lastName = $input->getArgument('last name');
        $email = $input->getArgument('email');
        $phone = $input->getArgument('phone');
        $password = $input->getArgument('password');

        // Vérifie que l'utilisateur n'existe pas déjà
        $existingUser = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            $io->error(sprintf('Sorry, this email is already used', $email));
            return Command::FAILURE;
        }

        $user = new User();
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setPhone($phone);
        $user->setEmail($email);
        $user->setRole(['ROLE_ADMIN']);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->em->persist($user);
        $this->em->flush();

        $io->success(sprintf('Admin "%s" successfully created', $email));

        return Command::SUCCESS;
    }
}
