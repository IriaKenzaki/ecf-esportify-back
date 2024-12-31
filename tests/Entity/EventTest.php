<?php
namespace App\Test\Entity;

use App\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EventTest extends WebTestCase
{
    public function testSetAndGetTitle(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/login', [
            'username' => 'test@test.com',
            'password' => 'toto'
        ]);
        $event = new Event();
        $event->setTitle('Tournament 2024');
        
        $this->assertEquals('Tournament 2024', $event->getTitle(), 'The title should be set and retrieved correctly.');
    }

    public function testSetAndGetDescription(): void
    {
        $event = new Event();
        $event->setDescription('A grand tournament for the best players.');
        
        $this->assertEquals('A grand tournament for the best players.', $event->getDescription(), 'The description should be set and retrieved correctly.');
    }

    public function testSetAndGetPlayers(): void
    {
        $event = new Event();
        $event->setPlayers(16);
        
        $this->assertEquals(16, $event->getPlayers(), 'The players count should be set and retrieved correctly.');
    }
    
    public function testSetAndGetCreatedAt(): void
    {
        $event = new Event();
        $createdAt = new \DateTimeImmutable('2024-01-01');
        $event->setCreatedAt($createdAt);
        
        $this->assertEquals($createdAt, $event->getCreatedAt(), 'The createdAt date should be set and retrieved correctly.');
    }

    public function testSetAndGetDateTimeStart(): void
    {
        $event = new Event();
        $startDate = new \DateTime('2024-01-01 10:00:00');
        $event->setDateTimeStart($startDate);
        
        $this->assertEquals($startDate, $event->getDateTimeStart(), 'The start date should be set and retrieved correctly.');
    }

    public function testSetAndGetCreatedBy(): void
    {
        $event = new Event();
        $event->setCreatedBy('admin');
        
        $this->assertEquals('admin', $event->getCreatedBy(), 'The createdBy field should be set and retrieved correctly.');
    }

    public function testSetAndGetImage(): void
    {
        $event = new Event();
        $event->setImage('event_image.jpg');
        
        $this->assertEquals('event_image.jpg', $event->getImage(), 'The image should be set and retrieved correctly.');
    }

    public function testSetAndGetVisibility(): void
    {
        $event = new Event();
        $event->setVisibility(true);
        
        $this->assertTrue($event->isVisibility(), 'The visibility should be set and retrieved correctly.');
    }


    public function testBlacklist(): void
    {
        $event = new Event();
        
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $event->getBlacklist(), 'The blacklist should return a collection.');
    }

    public function testScores(): void
    {
        $event = new Event();
        
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $event->getScores(), 'The scores should return a collection.');
    }

    public function testDefaultValues(): void
    {
        $event = new Event();
        
        $this->assertInstanceOf(\Doctrine\Common\Collections\ArrayCollection::class, $event->getBlacklist(), 'Blacklist should be an instance of ArrayCollection.');
        $this->assertInstanceOf(\Doctrine\Common\Collections\ArrayCollection::class, $event->getScores(), 'Scores should be an instance of ArrayCollection.');
        $this->assertInstanceOf(\Doctrine\Common\Collections\ArrayCollection::class, $event->getListParticipants(), 'ListParticipants should be an instance of ArrayCollection.');
    }
}
