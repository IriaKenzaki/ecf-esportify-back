<?php

namespace App\Tests\Entity;

use App\Entity\Score;
use App\Entity\Event;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class ScoreTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $score = new Score();

        $this->assertNull($score->getId(), 'ID should be null by default.');
        $this->assertNull($score->getScore(), 'Score should be null by default.');
        $this->assertNull($score->getEvent(), 'Event should be null by default.');
        $this->assertNull($score->getUser(), 'User should be null by default.');
        $this->assertInstanceOf(\DateTimeImmutable::class, $score->getCreatedAt(), 'CreatedAt should be an instance of DateTimeImmutable.');
    }

    public function testSetAndGetScore(): void
    {
        $score = new Score();
        $score->setScore(100);

        $this->assertEquals(100, $score->getScore(), 'Score should be correctly set and retrieved.');
    }

    public function testSetAndGetEvent(): void
    {
        $score = new Score();
        $event = new Event();

        $score->setEvent($event);

        $this->assertSame($event, $score->getEvent(), 'Event should be correctly set and retrieved.');
    }

    public function testSetAndGetUser(): void
    {
        $score = new Score();
        $user = new User();

        $score->setUser($user);

        $this->assertSame($user, $score->getUser(), 'User should be correctly set and retrieved.');
    }

    public function testSetAndGetCreatedAt(): void
    {
        $score = new Score();
        $createdAt = new \DateTimeImmutable('2024-12-24 10:00:00');
        $score->setCreatedAt($createdAt);

        $this->assertSame($createdAt, $score->getCreatedAt(), 'CreatedAt should be correctly set and retrieved.');
    }
}
