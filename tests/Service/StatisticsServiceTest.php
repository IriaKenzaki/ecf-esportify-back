<?php

namespace App\Test\Service;

use App\Entity\Event;
use App\Entity\User;
use PHPUnit\Framework\TestCase;


class StatisticsServiceTest extends TestCase
{

    public function testGetEventCount(): void
    {
        $event1 = new Event();
        $event1->setDateTimeStart(new \DateTime('-21 day 00:00:00'));
        $event1->setDateTimeEnd(new \DateTime('now'));

        $event2 = new Event();
        $event2->setDateTimeStart(new \DateTime('-23 days 00:00:00'));
        $event2->setDateTimeEnd(new \DateTime('-22 days 00:00:00'));

        $allEvents = [$event1, $event2];

        $startDate = new \DateTime('-23 days 00:00:00');
        $endDate = new \DateTime('now');

        $filteredEvents = array_filter($allEvents, function($event) use ($startDate, $endDate) {
            return $event->getDateTimeStart() >= $startDate && $event->getDateTimeEnd() <= $endDate;
        });

        $eventCount = count($filteredEvents);

        $this->assertSame(2, $eventCount, 'Le nombre d\'événements devrait être 2.');
    }

    public function testGetUserCount(): void
    {
        $user1 = new User();
        $user1->setCreatedAt(new \DateTimeImmutable('-1 day 00:00:00'));

        $user2 = new User();
        $user2->setCreatedAt(new \DateTimeImmutable('-3 days 00:00:00'));

        $user3 = new User();
        $user3->setCreatedAt(new \DateTimeImmutable('now'));

        $allUsers = [$user1, $user2, $user3];

        $startDate = new \DateTime('-3 days 00:00:00');
        $endDate = new \DateTime('now');

        $filteredUsers = array_filter($allUsers, function($user) use ($startDate, $endDate) {
            return $user->getCreatedAt() >= $startDate && $user->getCreatedAt() <= $endDate;
        });

        $userCount = count($filteredUsers);

        $this->assertSame(3, $userCount, 'Le nombre d\'utilisateurs devrait être 3.');
    }

    public function testGetUserConnectedCount(): void
    {
        $user1 = new User();
        $user1->setLastLogin(new \DateTime('-5 day 00:00:00'));
    
        $user2 = new User();
        $user2->setLastLogin(new \DateTime('-8 days 00:00:00'));
    
        $user3 = new User();
        $user3->setLastLogin(new \DateTime('now'));
    
        $user4 = new User();
        
        $allUsers = [$user1, $user2, $user3, $user4];
    
        $startDate = new \DateTime('-8 days 00:00:00');
        $endDate = new \DateTime('now');
    
        $filteredUsers = array_filter($allUsers, function($user) use ($startDate, $endDate) {
            return $user->getLastLogin() && $user->getLastLogin() >= $startDate && $user->getLastLogin() <= $endDate;
        });
    
        $connectedUserCount = count($filteredUsers);
    
        $this->assertSame(3, $connectedUserCount, 'Le nombre d\'utilisateurs connectés devrait être 3.');
    }

    public function testGetEventCountWithoutDates(): void
    {
        $event1 = new Event();
        $event1->setDateTimeStart(new \DateTime('-11 day 00:00:00'));
        $event1->setDateTimeEnd(new \DateTime('now'));
    
        $event2 = new Event();
        $event2->setDateTimeStart(new \DateTime('-13 days 00:00:00'));
        $event2->setDateTimeEnd(new \DateTime('-12 days 00:00:00'));
    
        $allEvents = [$event1, $event2];
    
        $eventCount = count($allEvents);
    
        $this->assertSame(2, $eventCount, 'Le nombre d\'événements devrait être 2.');
    }
}
