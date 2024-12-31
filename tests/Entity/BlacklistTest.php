<?php

namespace App\Tests\Entity;

use App\Entity\Blacklist;
use App\Entity\Event;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class BlacklistTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $blacklist = new Blacklist();

        $this->assertNull($blacklist->getId(), 'ID should be null by default.');
        $this->assertNull($blacklist->getEvent(), 'Event should be null by default.');
        $this->assertNull($blacklist->getUser(), 'User should be null by default.');
    }

    public function testSetAndGetEvent(): void
    {
        $event = new Event();
        $blacklist = new Blacklist();

        $blacklist->setEvent($event);

        $this->assertSame($event, $blacklist->getEvent(), 'Event should be set correctly.');
    }

    public function testSetAndGetUser(): void
    {
        $user = new User();
        $blacklist = new Blacklist();

        $blacklist->setUser($user);

        $this->assertSame($user, $blacklist->getUser(), 'User should be set correctly.');
    }

    public function testChainingMethods(): void
    {
        $event = new Event();
        $user = new User();
        $blacklist = new Blacklist();

        $blacklist->setEvent($event)->setUser($user);

        $this->assertSame($event, $blacklist->getEvent(), 'Chained method should set the Event correctly.');
        $this->assertSame($user, $blacklist->getUser(), 'Chained method should set the User correctly.');
    }
}

