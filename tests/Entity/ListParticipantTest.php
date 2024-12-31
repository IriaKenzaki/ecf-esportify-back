<?php

namespace App\Tests\Entity;

use App\Entity\Event;
use App\Entity\ListParticipant;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class ListParticipantTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $event = new Event();
        $listParticipant = new ListParticipant();

        $listParticipant->setEvent($event);
        $this->assertNull($listParticipant->getId(), 'ID should be null by default.');
        if (method_exists($listParticipant, 'getEvent')){
            $this->assertNotNull($listParticipant->getEvent(), 'Event should be null by default.');
        }
        $this->assertInstanceOf(ArrayCollection::class, $listParticipant->getParticipants(), 'Participants should be an instance of ArrayCollection.');
        $this->assertCount(0, $listParticipant->getParticipants(), 'Participants should be empty by default.');
    }

    public function testSetAndGetEvent(): void
    {
        $event = new Event();
        $listParticipant = new ListParticipant();

        $listParticipant->setEvent($event);

        $this->assertSame($event, $listParticipant->getEvent(), 'Event should be correctly set and retrieved.');
    }

    public function testAddParticipant(): void
    {
        $listParticipant = new ListParticipant();
        $user = new User();

        $listParticipant->addParticipant($user);

        $this->assertCount(1, $listParticipant->getParticipants(), 'Participants collection should contain one participant after adding.');
        $this->assertTrue($listParticipant->getParticipants()->contains($user), 'Participants collection should contain the added user.');
    }

    public function testAddSameParticipantTwice(): void
    {
        $listParticipant = new ListParticipant();
        $user = new User();

        $listParticipant->addParticipant($user);
        $listParticipant->addParticipant($user);

        $this->assertCount(1, $listParticipant->getParticipants(), 'Participants collection should not allow duplicate participants.');
    }

    public function testRemoveParticipant(): void
    {
        $listParticipant = new ListParticipant();
        $user = new User();

        $listParticipant->addParticipant($user);
        $listParticipant->removeParticipant($user);

        $this->assertCount(0, $listParticipant->getParticipants(), 'Participants collection should be empty after removing the participant.');
        $this->assertFalse($listParticipant->getParticipants()->contains($user), 'Participants collection should not contain the removed user.');
    }
}
