<?php

namespace App\Tests\Service;

use PHPUnit\Framework\TestCase;
use App\Service\BlacklistService;
use App\Entity\Event;
use App\Entity\User;
use App\Entity\Blacklist;
use Doctrine\Common\Collections\ArrayCollection;

class BlacklistServiceTest extends TestCase
{
    private BlacklistService $blacklistService;

    protected function setUp(): void
    {
        $this->blacklistService = new BlacklistService();
    }

    public function testAddToBlacklist(): void
{
    $event = new Event();
    

    $user = new User();
    $user->setEmail('test@example.com');

    $this->blacklistService->addToBlacklist($event, $user);

    $this->assertCount(1, $event->getBlacklist(), 'Blacklist should contain 1 entry after adding a user.');

    $blacklistEntry = $event->getBlacklist()->first();
    $this->assertSame($user, $blacklistEntry->getUser(), 'The user in the blacklist should match the added user.');
}

    public function testRemoveFromBlacklist(): void
    {
        $event = new Event();
        $user = new User();
        $blacklist = new ArrayCollection();

        $blacklistEntry = new Blacklist();
        $blacklistEntry->setEvent($event);
        $blacklistEntry->setUser($user);
        $event->getBlacklist()->add($blacklistEntry);


        $this->blacklistService->removeFromBlacklist($event, $user);

        $this->assertCount(0, $blacklist, 'Blacklist should be empty after removing the user.');
    }

    public function testIsUserBlacklisted(): void
    {
        $event = new Event();
        $user = new User();
        $blacklist = new ArrayCollection();

        $blacklistEntry = new Blacklist();
        $blacklistEntry->setEvent($event);
        $blacklistEntry->setUser($user);
        $event->getBlacklist($blacklist)->add($blacklistEntry);

        $this->assertTrue($this->blacklistService->isUserBlacklisted($event, $user), 'User should be blacklisted.');
    }
}
