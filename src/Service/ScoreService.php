<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Score;
use App\Entity\Event;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ScoreService
{
    public function getScores(User $user): Collection
    {
        return $user->getScoresCollection();
    }

    public function addScore(User $user, Score $score): void
    {
        $user->getScoresCollection()->add($score);
        $score->setUser($user);
    }

    public function removeScore(User $user, Score $score): void
    {
        if ($user->getScoresCollection()->removeElement($score) && $score->getUser() === $user) {
            $score->setUser(null);
        }
    }

    public function getSortedScores(User $user): Collection
    {
        $scores = $user->getScoresCollection()->toArray();
        usort($scores, fn(Score $a, Score $b) => $b->getCreatedAt() <=> $a->getCreatedAt());
        return new ArrayCollection($scores);
    }

    public function addScoreToEvent(Event $event, Score $score): void
    {
        $event->getScores()->add($score);
        $score->setEvent($event);
    }

    public function removeScoreFromEvent(Event $event, Score $score): void
    {
        $event->getScores()->removeElement($score);
        $score->setEvent(null);
    }

}
