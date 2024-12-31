<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Entity\Score;
use App\Entity\Event;
use App\Service\ScoreService;
use PHPUnit\Framework\TestCase;

class ScoreServiceTest extends TestCase
{
    private ScoreService $scoreService;

    protected function setUp(): void
    {
        $this->scoreService = new ScoreService();
    }

    public function testGetScores(): void
    {
        $user = new User();
        $score1 = new Score();
        $score2 = new Score();

        $user->getScoresCollection()->add($score1);
        $user->getScoresCollection()->add($score2);

        $retrievedScores = $this->scoreService->getScores($user);

        $this->assertCount(2, $retrievedScores, 'The user should have 2 scores.');
        $this->assertSame($score1, $retrievedScores->first(), 'The first score should match.');
        $this->assertSame($score2, $retrievedScores->last(), 'The second score should match.');
    }

    public function testAddScore(): void
    {
        $user = new User();
        $score = new Score();

        $this->scoreService->addScore($user, $score);

        $this->assertCount(1, $user->getScoresCollection(), 'The user should have 1 score.');
        $this->assertSame($score, $user->getScoresCollection()->first(), 'The added score should match.');
        $this->assertSame($user, $score->getUser(), 'The score should reference the user.');
    }

    public function testRemoveScore(): void
    {
        $user = new User();
        $score = new Score();

        $user->getScoresCollection()->add($score);
        $score->setUser($user);

        $this->scoreService->removeScore($user, $score);

        $this->assertCount(0, $user->getScoresCollection(), 'The user should have no scores after removal.');
        $this->assertNull($score->getUser(), 'The score should not reference any user after removal.');
    }

    public function testGetSortedScores(): void
    {
        $user = new User();
        $score1 = new Score();
        $score2 = new Score();

        $score1->setCreatedAt(new \DateTimeImmutable('-1 day'));
        $score2->setCreatedAt(new \DateTimeImmutable('now'));

        $user->getScoresCollection()->add($score1);
        $user->getScoresCollection()->add($score2);

        $sortedScores = $this->scoreService->getSortedScores($user);

        $this->assertCount(2, $sortedScores, 'The user should have 2 scores.');
        $this->assertSame($score2, $sortedScores->first(), 'The most recent score should come first.');
        $this->assertSame($score1, $sortedScores->last(), 'The oldest score should come last.');
    }

    public function testAddScoreToEvent(): void
    {
        $event = new Event();
        $score = new Score();

        $this->scoreService->addScoreToEvent($event, $score);

        $this->assertCount(1, $event->getScores(), 'The event should have 1 score.');
        $this->assertSame($score, $event->getScores()->first(), 'The added score should match.');
        $this->assertSame($event, $score->getEvent(), 'The score should reference the event.');
    }

    public function testRemoveScoreFromEvent(): void
    {
        $event = new Event();
        $score = new Score();

        $event->getScores()->add($score);
        $score->setEvent($event);

        $this->scoreService->removeScoreFromEvent($event, $score);

        $this->assertCount(0, $event->getScores(), 'The event should have no scores after removal.');
        $this->assertNull($score->getEvent(), 'The score should not reference any event after removal.');
    }
}
