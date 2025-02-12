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
}
