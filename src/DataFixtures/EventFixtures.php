<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\User;
use DateTime;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;


class EventFixtures extends Fixture implements DependentFixtureInterface
{
    /** @throws Exception */
    public function load(ObjectManager $manager): void
    {
        
        for ($i = 1; $i <= 20; $i++) {
            
            $event = (new Event())
                ->setTitle("Event n°$i")
                ->setDescription("Description n°$i")
                ->setPlayers(random_int(1,10))
                ->setDateTimeStart(new DateTime())
                ->setDateTimeEnd((new DateTime())->modify('+1 day'))
                ->setCreatedBy("Username" . random_int(1, 20))
                ->setVisibility(false)
                ->setCreatedAt(new DateTimeImmutable());

            $manager->persist($event);
            $this->addReference("Event" . $i, $event);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
