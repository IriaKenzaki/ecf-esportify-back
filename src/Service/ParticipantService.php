<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Event;
use App\Entity\ListParticipant;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;

class ParticipantService
{
    private EntityManagerInterface $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

   
    public function addParticipantToEvent(Event $event, ListParticipant $listParticipant): void
    {
        if (!$event->getListParticipants()->contains($listParticipant)) {
            $event->getListParticipants()->add($listParticipant);
            $listParticipant->setEvent($event);
            $this->manager->persist($listParticipant);
            $this->manager->persist($event);
        }
    }

   
    public function removeParticipantFromEvent(Event $event, ListParticipant $listParticipant): void
    {
        if ($event->getListParticipants()->contains($listParticipant)) {
            $event->getListParticipants()->removeElement($listParticipant);
            $this->manager->remove($listParticipant);
            $this->manager->persist($event);
        }
    }

    public function addListParticipant(User $user, ListParticipant $listParticipant): void
    {
        if (!$listParticipant->getParticipants()->contains($user)) {
            $listParticipant->addParticipant($user);
            $this->manager->persist($listParticipant);
        }
    }

    public function removeListParticipant(User $user, ListParticipant $listParticipant): void
    {
        if ($listParticipant->getParticipants()->contains($user)) {
            $listParticipant->removeParticipant($user);
            $this->manager->persist($listParticipant);
        }
    }

    public function getParticipants(Event $event): Collection
    {
        $participants = new ArrayCollection();
        foreach ($event->getListParticipants() as $listParticipant) {
            foreach ($listParticipant->getParticipants() as $participant) {
                if (!$participants->contains($participant)) {
                    $participants->add($participant);
                }
            }
        }
        return $participants;
    }

    public function isUserInEvent(Event $event, User $user): bool
    {
        foreach ($event->getListParticipants() as $listParticipant){
            if ($listParticipant->getParticipants()->contains($user)){
                return true;
            }
        }
        return false;
    }

    public function isEventFull(Event $event): bool
    {
        $maxPlayers = $event->getPlayers();
        $currentParticipants = 0;
    
        foreach ($event->getListParticipants() as $listParticipant) {
            $currentParticipants += count($listParticipant->getParticipants());
        }
    
        return $currentParticipants >= $maxPlayers;
    }
}
