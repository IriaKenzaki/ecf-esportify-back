<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\User;
use App\Entity\Blacklist;

class BlacklistService
{
    public function addToBlacklist(Event $event, User $user): void
    {
        if (!$this->isUserBlacklisted($event, $user)) {
            $entry = new Blacklist();
            $entry->setEvent($event);
            $entry->setUser($user);
            $event->getBlacklist()->add($entry);
        }
    }

    public function removeFromBlacklist(Event $event, User $user): void
    {
        foreach ($event->getBlacklist() as $entry) {
            if ($entry->getUser() === $user) {
                $event->getBlacklist()->removeElement($entry);
                return;
            }
        }
    }

    public function isUserBlacklisted(Event $event, User $user): bool
    {
        foreach ($event->getBlacklist() as $entry) {
            if ($entry->getUser() === $user) {
                return true;
            }
        }
        return false;
    }
}
