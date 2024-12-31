<?php

namespace App\Tests\Service;

use App\Entity\Event;
use App\Entity\ListParticipant;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use App\Service\ParticipantService;
use Doctrine\ORM\EntityManagerInterface;

class ParticipantServiceTest extends TestCase
{
    private ParticipantService $participantService;
    private EntityManagerInterface $entityManagerMock;

    protected function setUp(): void
    {

        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->participantService = new ParticipantService($this->entityManagerMock);
    }

    public function testAddParticipantToEvent(): void
    {
        $event = new Event();
        $listParticipant = new ListParticipant();
        $user = new User();
        $user->setEmail('test@example.com');

        $listParticipant->addParticipant($user);

        $this->participantService->addParticipantToEvent($event, $listParticipant);

        $this->assertCount(1, $event->getListParticipants(), 'ListParticipant should contain 1 entry after adding user');

        $listParticipantEntry = $event->getListParticipants()->first();
        $this->assertCount(1, $listParticipantEntry->getParticipants(), 'ListParticipant should contain 1 user');
        $this->assertSame($user, $listParticipantEntry->getParticipants()->first(), 'The user in the listparticipant should match the added user');
    }
    
    public function testRemoveParticipantfromEvent(): void
    {
        $event = new Event();
        $listParticipant = new ListParticipant();
        $user = new User();
        $listParticipant->addParticipant($user);
        $this->participantService->addParticipantToEvent($event, $listParticipant);
        $this->assertCount(1, $event->getListParticipants(), 'ListParticipant should contain 1 entry after adding a user');


        $this->participantService->removeParticipantFromEvent($event, $listParticipant);
        $this->assertCount(0, $event->getListParticipants(), 'Event should contain 0 ListParticipant after removal.');
    }
    public function testAddListParticipant(): void
    {
        $event = new Event();
        $listParticipant = new ListParticipant();

        $listParticipant->setEvent($event);

        $event->getListParticipants()->add($listParticipant);

        $this->assertCount(1, $event->getListParticipants(), 'Event should contain 1 ListParticipant after addition.');
        $this->assertSame($listParticipant, $event->getListParticipants()->first(), 'The first ListParticipant should match the added ListParticipant.');
        $this->assertSame($event, $listParticipant->getEvent(), 'ListParticipant should reference the associated Event.');
    }

    public function testRemoveListParticipant(): void
    {
        $user = new User();
        $listParticipant = new ListParticipant();
        $event = new Event();

        $listParticipant->addParticipant($user);
        $event->getListParticipants()->add($listParticipant);

        $participantsCollection = $listParticipant->getParticipants();
        
        $this->assertCount(1, $participantsCollection, 'Participant count should be 1 before removal');
        $this->participantService->removeListParticipant($user, $listParticipant);
        $this->assertCount(0, $listParticipant->getParticipants(), 'Participant count should be 0 after removal');
        $this->entityManagerMock->flush();
        $this->assertTrue(true);
    }

    public function testGetParticipants(): void
    {
        $event = new Event();
        
        $user1 = new User();
        $user1->setEmail('user1@example.com');
        
        $user2 = new User();
        $user2->setEmail('user2@example.com');
        
        $user3 = new User();
        $user3->setEmail('user3@example.com');
        
        $listParticipant1 = new ListParticipant();
        $listParticipant1->addParticipant($user1);
        $listParticipant1->addParticipant($user2);
        
        $listParticipant2 = new ListParticipant();
        $listParticipant2->addParticipant($user2);
        $listParticipant2->addParticipant($user3);

        $event->getListParticipants()->add($listParticipant1);
        $event->getListParticipants()->add($listParticipant2);

        $participants = $this->participantService->getParticipants($event);

        $this->assertCount(3, $participants, 'There should be 3 unique participants in the collection');
        
        $participantsEmails = array_map(fn($participant) => $participant->getEmail(), iterator_to_array($participants));
        
        $this->assertContains('user1@example.com', $participantsEmails);
        $this->assertContains('user2@example.com', $participantsEmails);
        $this->assertContains('user3@example.com', $participantsEmails);
    }

    public function testIsEventFull(): void
    {
        $event = new Event();
        $event->setPlayers(5);
        $listParticipant1 = new ListParticipant();
        $listParticipant2 = new ListParticipant();

        $this->participantService->addParticipantToEvent($event, $listParticipant1);
        $this->participantService->addParticipantToEvent($event, $listParticipant2);

        $user1 = new User();
        $user2 = new User();
        $user3 = new User();
        $user4 = new User();
        $user5 = new User();
        $user6 = new User();

        $listParticipant1->addParticipant($user1);
        $listParticipant1->addParticipant($user2);
        $listParticipant2->addParticipant($user3);
        $listParticipant2->addParticipant($user4);
        $listParticipant2->addParticipant($user5);

        $this->assertTrue($this->participantService->isEventFull($event), 'Event should be full.');

        $listParticipant2->addParticipant($user6);
        $this->assertTrue($this->participantService->isEventFull($event), 'Event should still be full even after adding an extra participant.');
    }

    public function testIsUserInEvent(): void
    {
        $event = new Event();
        $listParticipant = new ListParticipant();
        $user = new User();
        $listParticipant->addParticipant($user);
        $this->participantService->addParticipantToEvent($event, $listParticipant);
        $this->assertCount(1, $event->getListParticipants(), 'ListParticipant should contain 1 entry after adding a user');


        $this->participantService->isUserInEvent($event, $user);
        $this->assertCount(1, $event->getListParticipants(), 'Event should contain 1 entry ListParticipant.');
    }
}
