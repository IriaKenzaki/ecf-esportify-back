<?php

namespace App\DataFixtures;

use App\Entity\Score;
use App\Entity\Event;
use App\Entity\ListParticipant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ScoreFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $events = $manager->getRepository(Event::class)->findAll();

        foreach ($events as $event) {
            $listParticipants = $event->getListParticipants();

            $filteredParticipants = $listParticipants->filter(function (ListParticipant $listParticipant) {
                foreach ($listParticipant->getParticipants() as $user) {
                    if ($user->getId() >= 1 && $user->getId() <= 15) {
                        return true;
                    }
                }
                return false;
            });

            foreach ($filteredParticipants as $listParticipant) {
                foreach ($listParticipant->getParticipants() as $user) {
                    $score = (new Score())
                        ->setEvent($event)
                        ->setUser($user)
                        ->setScore(random_int(0, 100));
                    $manager->persist($score);
                }
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            EventFixtures::class,
            ListParticipantFixtures::class,
        ];
    }
}
