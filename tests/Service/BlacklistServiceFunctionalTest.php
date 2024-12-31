<?php

namespace App\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Service\BlacklistService;
use App\Entity\Event;
use App\Entity\User;

class BlacklistServiceFunctionalTest extends KernelTestCase
{
    private BlacklistService $blacklistService;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->blacklistService = static::getContainer()->get(BlacklistService::class);
    }

    public function testAddAndRemoveFromBlacklist(): void
    {
        $event = new Event();
        $event->setTitle('Test Event');
        $event->setDescription('An event for testing');
        $event->setPlayers(10);

        $user = new User();
        $user->setEmail('testuser@example.com');

        $this->blacklistService->addToBlacklist($event, $user);
        $this->assertCount(1, $event->getBlacklist(), 'Blacklist should contain 1 entry after adding.');

        $this->assertTrue($this->blacklistService->isUserBlacklisted($event, $user), 'User should be blacklisted.');

        $this->blacklistService->removeFromBlacklist($event, $user);
        $this->assertCount(0, $event->getBlacklist(), 'Blacklist should be empty after removing the user.');
    }
}
