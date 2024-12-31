<?php

namespace App\DataFixtures;

use App\Entity\Blacklist;
use App\Entity\Event;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class BlacklistFixtures extends Fixture implements DependentFixtureInterface
{
    
    public function load(ObjectManager $manager): void
    {
        $users = $manager->getRepository(User::class)
        ->createQueryBuilder('u')
        ->where('u.id BETWEEN :min AND :max')
        ->setParameter('min', 15)
        ->setParameter('max', 20)
        ->getQuery()
        ->getResult();
        $events = $manager->getRepository(Event::class)->findAll();

        foreach ($events as $event) {
            $participantsCount = random_int(1, 5);
            $blacklist = (new Blacklist)
            ->setEvent($event);

            for ($j = 0; $j < $participantsCount; $j++) {
                $randomUser = $users[array_rand($users)];
                $blacklist->setUser($randomUser);
            }

                $manager->persist($blacklist);
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
