<?php

namespace App\Repository;

use App\Entity\Reservation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    /**
     * Méthode qui retourne toutes les informations des réservations passées
     * @return array
     */
    public function getAllInfosOld(): array
    {
        $entityManager = $this->getEntityManager();

        $qb = $entityManager->createQueryBuilder();

        $query = $qb->select([
            'r.id',
            'r.dateStart',
            'r.dateEnd',
            'r.adultsNumber',
            'r.kidsNumber',
            'r.price',
            're.title as room',
            're.location',
            'u.firstname',
            'u.lastname',
            'u.email'
        ])
            ->from(Reservation::class, 'r')
            ->leftJoin('r.rental', 're')
            ->leftJoin('r.user', 'u')
            ->where('r.dateEnd < CURRENT_DATE()')
            ->getQuery();
        
        $result = $query->getResult();

        return $result;
    }

    /**
     * Méthode qui retourne toutes les informations des réservations futures
     * @return array
     */
    public function getAllInfosFuture(): array
    {
        $entityManager = $this->getEntityManager();

        $qb = $entityManager->createQueryBuilder();

        $query = $qb->select([
            'r.id',
            'r.dateStart',
            'r.dateEnd',
            'r.adultsNumber',
            'r.kidsNumber',
            'r.price',
            're.title as room',
            're.location',
            'u.firstname',
            'u.lastname',
            'u.email'
        ])
            ->from(Reservation::class, 'r')
            ->leftJoin('r.rental', 're')
            ->leftJoin('r.user', 'u')
            ->where('r.dateStart > CURRENT_DATE()')
            ->getQuery();
        
        $result = $query->getResult();

        return $result;
    }

    /**
     * Méthode qui retourne toutes les informations des réservations en cours
     * @return array
     */
    public function getAllInfosNow(): array
    {
        $entityManager = $this->getEntityManager();

        $qb = $entityManager->createQueryBuilder();

        $query = $qb->select([
            'r.id',
            'r.dateStart',
            'r.dateEnd',
            'r.adultsNumber',
            'r.kidsNumber',
            'r.price',
            're.title as room',
            're.location',
            'u.firstname',
            'u.lastname',
            'u.email'
        ])
            ->from(Reservation::class, 'r')
            ->leftJoin('r.rental', 're')
            ->leftJoin('r.user', 'u')
            ->where('r.dateStart <= CURRENT_DATE()')
            ->andWhere('r.dateEnd >= CURRENT_DATE()')
            ->getQuery();
        
        $result = $query->getResult();

        return $result;
    }

    /**
     * Méthode qui retourne les informations des réservations passées d'un utilisateur
     * @param User $user
     * @return array
     */
    public function getInfosOldByUser(User $user): array
    {
        $entityManager = $this->getEntityManager();

        $qb = $entityManager->createQueryBuilder();

        $query = $qb->select([
            'r.id',
            'r.dateStart',
            'r.dateEnd',
            'r.adultsNumber',
            'r.kidsNumber',
            're.title as room',
            're.location'
        ])
            ->from(Reservation::class, 'r')
            ->leftJoin('r.rental', 're')
            ->leftJoin('r.user', 'u')
            ->where('r.dateEnd < CURRENT_DATE()')
            ->andWhere('r.user = :user')
            ->setParameter('user', $user)
            ->getQuery();
        
        $result = $query->getResult();

        return $result;
    }

    /**
     * Méthode qui retourne les informations des réservations futures d'un utilisateur
     * @param User $user
     * @return array
     */
    public function getInfosFutureByUser(User $user): array
    {
        $entityManager = $this->getEntityManager();

        $qb = $entityManager->createQueryBuilder();

        $query = $qb->select([
            'r.id',
            'r.dateStart',
            'r.dateEnd',
            'r.adultsNumber',
            'r.kidsNumber',
            're.title as room',
            're.location'
        ])
            ->from(Reservation::class, 'r')
            ->leftJoin('r.rental', 're')
            ->leftJoin('r.user', 'u')
            ->where('r.dateStart > CURRENT_DATE()')
            ->andWhere('r.user = :user')
            ->setParameter('user', $user)
            ->getQuery();
        
        $result = $query->getResult();

        return $result;
    }

    /**
     * Méthode qui retourne les informations des réservations en cours d'un utilisateur
     * @param User $user
     * @return array
     */
    public function getInfosNowByUser(User $user): array
    {
        $entityManager = $this->getEntityManager();

        $qb = $entityManager->createQueryBuilder();

        $query = $qb->select([
            'r.id',
            'r.dateStart',
            'r.dateEnd',
            'r.adultsNumber',
            'r.kidsNumber',
            're.title as room',
            're.location'
        ])
            ->from(Reservation::class, 'r')
            ->leftJoin('r.rental', 're')
            ->leftJoin('r.user', 'u')
            ->where('r.dateStart <= CURRENT_DATE()')
            ->andWhere('r.dateEnd >= CURRENT_DATE()')
            ->andWhere('r.user = :user')
            ->setParameter('user', $user)
            ->getQuery();
        
        $result = $query->getResult();

        return $result;
    }

}
