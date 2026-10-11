<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserControllerTest extends WebTestCase
{
    public function testCannotAccessAnotherUsersProfile(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $hasher = $container->get(UserPasswordHasherInterface::class);

// Crée deux utilisateurs
        $userA = new User();
        $userA->setEmail('a@example.com')->setFirstName('A')->setLastName('A');
        $userA->setPassword($hasher->hashPassword($userA, 'password123'));
        $userA->setCreatedAt(new \DateTimeImmutable());
        $em->persist($userA);

        $userB = new User();
        $userB->setEmail('b@example.com')->setFirstName('B')->setLastName('B');
        $userB->setPassword($hasher->hashPassword($userB, 'password123'));
        $userB->setCreatedAt(new \DateTimeImmutable());
        $em->persist($userB);

        $em->flush();

// Connecte userA, puis essaie d'accéder au profil de userB
        $client->loginUser($userA);
        $client->request('GET', '/user/' . $userB->getId());

        $this->assertResponseStatusCodeSame(403);
    }

    public function testCanAccessOwnProfile(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $hasher = $container->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmail('me@example.com')->setFirstName('Me')->setLastName('Test');
        $user->setPassword($hasher->hashPassword($user, 'password123'));
        $user->setCreatedAt(new \DateTimeImmutable());
        $em->persist($user);
        $em->flush();

        $client->loginUser($user);
        $client->request('GET', '/user/' . $user->getId());

        $this->assertResponseIsSuccessful();
    }
}
