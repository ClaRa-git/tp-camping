<?php

namespace App\Repository;

use App\Entity\Equipment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Equipment>
 */
class EquipmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Equipment::class);
    }

    /**
     * Méthode permettant de récupérer tous les équipements d'une location
     * @param int $id
     * @return array
     */
    public function getEquipmentsForRental(int $id): array
    {
        $entityManager = $this->getEntityManager();

        $qb = $entityManager->createQueryBuilder();

        $query = $qb->select([
            'e.label',
        ])
            ->from(Equipment::class, 'e')
            ->join('e.rentals', 'r')
            ->where('r.id = :id')
            ->setParameter('id', $id)
            ->getQuery();
        
        return $query->getResult();
    }
}
