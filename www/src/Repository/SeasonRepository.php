<?php

namespace App\Repository;

use App\Entity\Season;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Season>
 */
class SeasonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Season::class);
    }

    /**
     * Méthode qui retourne la/les saison(s) entre deux dates
     * @return array
     */
    public function findSeasonsBetweenDates(\DateTime $dateStart, \DateTime $dateEnd): array
    {
        $entityManager = $this->getEntityManager();

        $qb = $entityManager->createQueryBuilder();

        $query = $qb->select('s')
            ->from(Season::class, 's')
            ->where('s.dateStart <= :dateEnd')
            ->andWhere('s.dateEnd >= :dateStart')
            ->setParameter('dateStart', $dateStart)
            ->setParameter('dateEnd', $dateEnd)
            ->getQuery();

        $result = $query->getResult();

        return $result;
    }

    /**
     * Méthode qui retourne les saisons où le camping est fermé
     * @return array
     */
    public function findSeasonsClosed(): array
    {
        $entityManager = $this->getEntityManager();

        $qb = $entityManager->createQueryBuilder();

        $query = $qb->select('s')
            ->from(Season::class, 's')
            ->where('s.isClosed = 1')
            ->getQuery();

        $result = $query->getResult();

        return $result;
    }
}
