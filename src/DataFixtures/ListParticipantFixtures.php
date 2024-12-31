<?php

namespace App\DataFixtures;

use App\Entity\ListParticipant;
use App\Entity\Event;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ListParticipantFixtures extends Fixture implements DependentFixtureInterface
{
    
    public function load(ObjectManager $manager): void
    {
        $users = $manager->getRepository(User::class)
        ->createQueryBuilder('u')
        ->where('u.id BETWEEN :min AND :max')
        ->setParameter('min', 1)
        ->setParameter('max', 15)
        ->getQuery()
        ->getResult();
        $events = $manager->getRepository(Event::class)->findAll();

        foreach ($events as $event) {
            $participantsCount = random_int(1, 10);
            $listParticipant = (new ListParticipant())
            ->setEvent($event);

            for ($j = 0; $j < $participantsCount; $j++) {
                $randomUser = $users[array_rand($users)];
                $listParticipant->addParticipant($randomUser);
            }

                $manager->persist($listParticipant);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            EventFixtures::class,
        ];
    }
}
