<?php

namespace App\Test\Entity;

use App\Entity\User;
use DateTime;
use PHPUnit\Framework\TestCase;

use function Symfony\Component\Clock\now;

class UserTest extends TestCase
{
    public function testTheAutomaticApiTokenSettingWhenAnUserIsCreated(): void
    {
        $user = new User();
        $this->assertNotNull($user->getApiToken());
    }

    public function testThanAnUserHasAtLeastOneRoleUser(): void
    {
        $user = new User();
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testTheAuthomaticCreatedAtSettingWhenAnUserIsCreated(): void
    {
        $user = new User();
        $this->assertNotNull($user->getCreatedAt(), "The createdAt field should not be null after user creation.");
    }

    public function testTheUsernameIsNullWhenAnUserIsCreated(): void
    {
        $user = new User();
        $this->assertNull($user->getUsername());
    }
    
    public function testEmail(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        
        $this->assertEquals('test@example.com', $user->getEmail(), 'The email should be set and retrieved correctly.');
    }

    public function testPassword(): void
    {
        $user = new User();
        $user->setPassword('secretpassword');
        
        $this->assertEquals('secretpassword', $user->getPassword(), 'The password should be set and retrieved correctly.');
    }

    public function testUpdatedAt(): void
    {
        $user = new User();
        $now = new \DateTimeImmutable();
        $user->setUpdatedAt($now);
        
        $this->assertEquals($now, $user->getUpdatedAt(), 'The updatedAt should be set and retrieved correctly.');
    }

    public function testUserIdentifier(): void
    {
        $user = new User();
        $user->setUsername('Erwan');
        
        $this->assertEquals('Erwan', $user->getUserIdentifier(), 'The user identifier should return the username.');
    }

    public function testListParticipantsCollection(): void
    {
        $user = new User();
        
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $user->getListParticipantsCollection(), 'The listParticipants should return a collection.');
    }

    public function testScoresCollection(): void
    {
        $user = new User();
        
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $user->getScoresCollection(), 'The scores should return a collection.');
    }
    
    public function testEraseCredentials(): void
    {
        $user = new User();
        $user->eraseCredentials();
        
        $this->assertTrue(true, 'eraseCredentials should not do anything.');
    }

    public function testSetAndGetLastLogin (): void
   {
        $user = new User();
        $lastLogin = new DateTime();
        $user->setLastLogin($lastLogin);
        $this->assertNotNull($user->getLastLogin(), 'The test should return a datetime');
   }
}
