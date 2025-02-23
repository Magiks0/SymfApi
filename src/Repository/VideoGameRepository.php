<?php

namespace App\Repository;

use App\Entity\VideoGame;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VideoGame>
 */
class VideoGameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VideoGame::class);
    }

    public function findAllWithPagination(int $page, int $limit)
    {
        return $this->createQueryBuilder('vg')
            ->setFirstResult(($page-1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function find7daysReleaseGames(): array
    {
        $today = new \DateTime(); // Aujourd'hui
        $sevenDaysLater = new \DateTime('+7 days'); // Dans 7 jours

        return $this->createQueryBuilder('vg')
            ->where('vg.releaseDate BETWEEN :today AND :sevenDaysLater')
            ->setParameter('today', $today)
            ->setParameter('sevenDaysLater', $sevenDaysLater)
            ->getQuery()
            ->getResult();
    }
}
