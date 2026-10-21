<?php

namespace App\Tests\Security\Voter;

use App\Entity\Application;
use App\Entity\User;
use App\Security\Voter\ApplicationVoter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ApplicationVoterTest extends TestCase
{
    #[Test]
    public function ownerCanViewTheirApplication(): void
    {
        $user = $this->createStub(User::class);

        $application = $this->createStub(Application::class);
        $application->method('getUser')->willReturn($user);

        $token = $this->createStub(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $voter = new ApplicationVoter();

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($token, $application, ['VIEW'])
        );
    }

    #[Test]
    public function anotherUserCannotViewTheApplication(): void
    {
        $owner = $this->createStub(User::class);
        $someoneElse = $this->createStub(User::class);

        $application = $this->createStub(Application::class);
        $application->method('getUser')->willReturn($owner);

        $token = $this->createStub(TokenInterface::class);
        $token->method('getUser')->willReturn($someoneElse);

        $voter = new ApplicationVoter();

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($token, $application, ['VIEW'])
        );
    }

    #[Test]
    public function abstainsOnUnrelatedAttribute(): void
    {
        $application = $this->createStub(Application::class);
        $token = $this->createStub(TokenInterface::class);

        $voter = new ApplicationVoter();

        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $voter->vote($token, $application, ['EDIT']) // attribut non géré par ce Voter
        );
    }
}
