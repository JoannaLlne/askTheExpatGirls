<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Create admin account (ROLE_ADMIN)',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Create Admin account');

        // Demande du prénom
        $firstName = $io->ask('First name', null, function (?string $value) {
            if (empty($value)) {
                throw new \RuntimeException('First name required.');
            }
            return $value;
        });

        // Demande du nom
        $lastName = $io->ask('Last name', null, function (?string $value) {
            if (empty($value)) {
                throw new \RuntimeException('Last name required .');
            }
            return $value;
        });

        // Demande de l'email
        $email = $io->ask('Client email', null, function (?string $value) {
            if (empty($value) || !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException('Please enter a valid email address.');
            }
            return $value;
        });

        // Vérifie que l'email n'est pas déjà utilisé
        $existingUser = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => $email]);

        if ($existingUser) {
            $io->error(sprintf('Sorry, we can\'t create an account with this email', $email));
            return Command::FAILURE;
        }

        // Demande du mot de passe (saisie masquée)
        $plainPassword = $io->askHidden('Password (min. 8 characters)', function (?string $value) {
            if (empty($value) || strlen($value) < 8) {
                throw new \RuntimeException('Password needs at least 8 characters.');
            }
            return $value;
        });

        $confirmPassword = $io->askHidden('Confirm your password');

        if ($plainPassword !== $confirmPassword) {
            $io->error('Password does not match.');
            return Command::FAILURE;
        }

        // Création de l'utilisateur
        $user = new User();
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setEmail($email);
        $user->setRole(['ROLE_ADMIN']);
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $plainPassword)
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf('Admin account created with success', $email));

        return Command::SUCCESS;
    }
}
