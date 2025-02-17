<?php

namespace App\Repository;

use App\Entity\Type;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Type>
 */
class TypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Type::class);
    }

    /**
     * Méthode pour sauvegarder un type
     * @param Type $type
     * @param bool $flush
     * @return void
     */
    public function save(Type $type, bool $flush = true): void
    {
        $entityManager = $this->getEntityManager();

        $entityManager->persist($type);

        if ($flush) {
            $entityManager->flush();
        }
    }

    /**
     * Méthode pour récupérer l'image d'un type pour une location
     * @param int $typeId
     * @return string
     */
    public function getImageForRental(int $typeId): string
    {
        $entityManager = $this->getEntityManager();

        $qb = $entityManager->createQueryBuilder();

        $query = $qb->select('t.imagePath')
            ->from(Type::class, 't')
            ->where('t.id = :typeId')
            ->setParameter('typeId', $typeId)
            ->getQuery();
        
        $result = $query->getOneOrNullResult();

        return $result['imagePath'];
    }

    /**
     * Méthode pour récupérer le type d'une location
     * @param int $rentalId
     * @return Type
     */
    public function getTypeForRental(int $rentalId): Type
    {
        $entityManager = $this->getEntityManager();

        $qb = $entityManager->createQueryBuilder();

        $query = $qb->select('t')
            ->from(Type::class, 't')
            ->join('t.rentals', 'r')
            ->where('r.id = :rentalId')
            ->setParameter('rentalId', $rentalId)
            ->getQuery();
        
        return $query->getOneOrNullResult();
    }
}
