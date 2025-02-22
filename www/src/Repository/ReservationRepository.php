<?php

namespace App\Repository;

use App\Entity\Reservation;
use DateTime;
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
     * Méthode qui retourne toutes les informations des réservations
     * @return array
     */
    public function getAllInfos(): array
    {
        $entityManager = $this->getEntityManager();

        $qb = $entityManager->createQueryBuilder();

        $query = $qb->select([
            'r.id',
            't.label as type',
            'r.dateStart',
            'r.dateEnd',
            'r.adultsNumber',
            'r.kidsNumber',
            'r.price',
            'r.status',
            're.title as room',
            're.location',
            'u.firstname',
            'u.lastname',
            'u.email'
        ])
            ->from(Reservation::class, 'r')
            ->leftJoin('r.rental', 're')
            ->leftJoin('r.user', 'u')
            ->Join('re.type', 't')
            ->getQuery();
        
        $result = $query->getResult();

        return $result;
    }

    /**
     * Méthode qui retourne toutes les informations des réservations pour un utilisateur
     * @param int $userId
     * @return array
     */
    public function getAllInfosByIdUser(int $userId): array
    {
        $entityManager = $this->getEntityManager();

        $qb = $entityManager->createQueryBuilder();

        $query = $qb->select([
            'r.id',
            't.label as type',
            'r.dateStart',
            'r.dateEnd',
            'r.adultsNumber',
            'r.kidsNumber',
            'r.price',
            'r.status',
            're.title as room',
            're.location',
            'u.firstname',
            'u.lastname',
            'u.email'
        ])
            ->from(Reservation::class, 'r')
            ->leftJoin('r.rental', 're')
            ->leftJoin('r.user', 'u')
            ->Join('re.type', 't')
            ->where('u.id = :userId')
            ->setParameter('userId', $userId)
            ->getQuery();
        
        $result = $query->getResult();

        return $result;
    }

    /**
     * Méthode pour récupérer les réservations non annulées d'une location
     * @param int $rentalId
     * @return array
     */
    public function getReservationsByRentalId(int $rentalId): array
    {
        $entityManager = $this->getEntityManager();

        $qb = $entityManager->createQueryBuilder();

        $query = $qb->select('r')
            ->from(Reservation::class, 'r')
            ->leftjoin('r.rental', 're')
            ->where('r.rental = :rentalId')
            ->andWhere('re.isActive = 1')
            ->setParameter('rentalId', $rentalId)
            ->getQuery();
        
        $result = $query->getResult();

        return $result;
    }

    /**
     * Méthode pour récupérer les informations d'une réservation par filtre
     * @param string $filter
     * @return array
     */
    public function getReservationsByFilter(string $filter): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery('
            SELECT 
            r.id,
            r.dateStart, 
            r.dateEnd, 
            r.adultsNumber, 
            r.kidsNumber, 
            r.price,
            r.status,
            re.title as room, 
            re.location,
            u.firstname, 
            u.lastname, 
            u.email,
            t.label as type            
            FROM App\Entity\Reservation r
            JOIN r.rental re
            JOIN r.user u
            JOIN re.type t
            ORDER BY ' . $filter
        );

        $result = $query->getResult();

        return $result;
    }

    /**
     * Méthode pour récupérer les réservations d'un jour
     * @param DateTime $date
     * @return array
     */
    public function findByDateStart(DateTime $date): array
    {
        $dateEnd = clone $date;
        $dateEnd->modify('+1 day');

        $entityManager = $this->getEntityManager();
        
        $qb = $entityManager->createQueryBuilder();

        $query = $qb->select([
            'r.id',
            't.label as type',
            'r.dateStart',
            'r.dateEnd',
            're.id as roomId',
            're.title as room',
            're.location',
            're.isClean',
            'u.firstname',
            'u.lastname'
        ])
            ->from(Reservation::class, 'r')
            ->leftJoin('r.rental', 're')
            ->leftJoin('r.user', 'u')
            ->Join('re.type', 't')
            ->where('r.status = 1')
            ->where('r.dateStart BETWEEN :date AND :dateEnd')
            ->setParameter('date', $date)
            ->setParameter('dateEnd', $dateEnd)
            ->getQuery();

        $result = $query->getResult();

        return $result;
    }

    /**
    * Méthode pour récupérer les réservations d'un jour
    * @param DateTime $date
    * @return array
    */
   public function findByDateEnd(DateTime $date): array
   {
       $dateEnd = clone $date;
       $dateEnd->modify('+1 day');

       $entityManager = $this->getEntityManager();
       
       $qb = $entityManager->createQueryBuilder();

       $query = $qb->select([
           'r.id',
           't.label as type',
           'r.dateStart',
           'r.dateEnd',
           're.id as roomId',
           're.title as room',
           're.location',
           're.isClean',
           'u.firstname',
           'u.lastname'
       ])
           ->from(Reservation::class, 'r')
           ->leftJoin('r.rental', 're')
           ->leftJoin('r.user', 'u')
           ->Join('re.type', 't')
           ->where('r.status = 1')
           ->where('r.dateEnd BETWEEN :date AND :dateEnd')
           ->setParameter('date', $date)
           ->setParameter('dateEnd', $dateEnd)
           ->getQuery();

       $result = $query->getResult();

       return $result;
   }

}
