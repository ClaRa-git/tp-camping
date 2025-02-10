<?php

namespace App\Repository;

use App\Entity\Price;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Price>
 */
class PriceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Price::class);
    }

    /**
     * Méthode permettant de récupérer le prix d'un type de logement
     * @param int $typeId
     * @return array
     */
    public function getPriceByType(int $typeId): array
    {
        $entityManager = $this->getEntityManager();

        $qb = $entityManager->createQueryBuilder();

        $query = $qb->select('p')
        ->from(Price::class, 'p')
        ->join('p.types', 't')
        ->where('t.id = :typeId')
        ->setParameter('typeId', $typeId)
        ->getQuery();

        return $query->getResult();
    }
}
