<?php


namespace App\Service;

use App\Entity\User;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class StatisticsService
{
    private $eventRepository;
    private $userRepository;
    private $entityManager;

    public function __construct(EventRepository $eventRepository, UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $this->eventRepository = $eventRepository;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    public function getEventCount(\DateTime $startDate = null, \DateTime $endDate = null): int
    {
        $queryBuilder = $this->eventRepository->createQueryBuilder('e');

        if ($startDate) {
            $queryBuilder->andWhere('e.createdAt >= :startDate')
                         ->setParameter('startDate', $startDate->format('Y-m-d H:i:s'));
        }

        if ($endDate) {
            $queryBuilder->andWhere('e.createdAt <= :endDate')
                         ->setParameter('endDate', $endDate->format('Y-m-d H:i:s'));
        }

        return (int) $queryBuilder->select('COUNT(e)')->getQuery()->getSingleScalarResult();
    }

    public function getUserCount(\DateTime $startDate = null, \DateTime $endDate = null): int
    {
        $queryBuilder = $this->userRepository->createQueryBuilder('u');

        if ($startDate) {
            $queryBuilder->andWhere('u.createdAt >= :startDate')
                         ->setParameter('startDate', $startDate->format('Y-m-d H:i:s'));
        }

        if ($endDate) {
            $queryBuilder->andWhere('u.createdAt <= :endDate')
                         ->setParameter('endDate', $endDate->format('Y-m-d H:i:s'));
        }

        return (int) $queryBuilder->select('COUNT(u)')->getQuery()->getSingleScalarResult();
    }

    public function getUserConnectedCount(\DateTime $startDate = null, \DateTime $endDate = null): int
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();

        $queryBuilder->select('COUNT(u)')
                     ->from(User::class, 'u')
                     ->where('u.lastLogin IS NOT NULL');

        if ($startDate) {
            $queryBuilder->andWhere('u.lastLogin >= :startDate')
                         ->setParameter('startDate', $startDate->format('Y-m-d H:i:s'));
        }

        if ($endDate) {
            $queryBuilder->andWhere('u.lastLogin <= :endDate')
                         ->setParameter('endDate', $endDate->format('Y-m-d H:i:s'));
        }

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }
}
