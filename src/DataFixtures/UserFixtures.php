<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }
    /** @throws Exception */
    public function load(ObjectManager $manager): void
    {
        $adminUser = new User();
        $adminUser->setUsername('Armin')
                  ->setEmail('armin@admin.com')
                  ->setRoles(['ROLE_ADMIN'])
                  ->setPassword($this->passwordHasher->hashPassword($adminUser, 'Arm1npa$$'));

        $orgaUser = new User();
        $orgaUser->setUsername('Camille')
             ->setEmail('camille@orga.com')
             ->setRoles(['ROLE_ORGANISATEUR'])
             ->setPassword($this->passwordHasher->hashPassword($orgaUser, 'C@mpa$$w0rd'));

             $user = new User();
             $user->setUsername('Andréa')
                  ->setEmail('andi@player.com')
                  ->setRoles(['ROLE_USER'])
                  ->setPassword($this->passwordHasher->hashPassword($orgaUser, '@ndyA23!'));

        $manager->persist($user);
        $manager->persist($adminUser);
        $manager->persist($orgaUser);
        $manager->flush();
    }
}
