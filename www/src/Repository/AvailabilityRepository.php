<?php

namespace App\Repository;

use App\Entity\Availability;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Availability>
 */
class AvailabilityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Availability::class);
    }

    /**
     * Méthode permettant de récupérer toutes les informations des disponibilités
     * @return array
     */
    public function getAllInfos(): array
    {
        $entityManager = $this->getEntityManager();

        $qb = $entityManager->createQueryBuilder();

        $query = $qb->select([
            'a.id',
            'a.dateStart',
            'a.dateEnd',
            'r.title',
            'r.location',
        ])
            ->from(Availability::class, 'a')
            ->join('a.rental', 'r')
            ->getQuery();
        
        $results = $query->getResult();

        return $results;
    }
}
