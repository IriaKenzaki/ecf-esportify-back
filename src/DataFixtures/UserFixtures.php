<?php

namespace App\DataFixtures;

use App\Entity\User;
use DateTimeImmutable;
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
        $adminUser->setUsername('admin')
                  ->setEmail('admin@example.com')
                  ->setRoles(['ROLE_ADMIN'])
                  ->setPassword($this->passwordHasher->hashPassword($adminUser, 'adminpassword'));

        $orgaUser = new User();
        $orgaUser->setUsername('organisateur')
             ->setEmail('organisateur@example.com')
             ->setRoles(['ROLE_ORGANISATEUR'])
             ->setPassword($this->passwordHasher->hashPassword($orgaUser, 'organisateurpassword'));

        for ($i = 1; $i <= 20; $i++) {
            $user = (new User())
                ->setUsername("Username $i")
                ->setEmail("test.$i@test.com")
                ->setCreatedAt(new DateTimeImmutable());

            $user->setPassword($this->passwordHasher->hashPassword($user, 'password.$i'));

            $manager->persist($user);
            $manager->persist($adminUser);
            $manager->persist($orgaUser);
            $this->addReference("Username" . $i, $user);
        }
        $manager->flush();
    }
}
