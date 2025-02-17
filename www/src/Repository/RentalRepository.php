<?php

namespace App\Repository;

use App\Entity\Rental;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rental>
 */
class RentalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rental::class);
    }

    /**
     * Méthode pour récupérer toutes les informations d'une location
     * @return array
     */
    public function getAllInfos(): array
    {
        $entityManager = $this->getEntityManager();

        $qb = $entityManager->createQueryBuilder();

        $query = $qb->select([
            'r.id',
            'r.title',
            'r.description',
            'r.bedding',
            'r.surface',
            'r.location',
            'r.isClean',
            'r.isActive',
            't.label as typeLabel',
            'e.label as equipmentLabel',
        ])
            ->from(Rental::class, 'r')
            ->join('r.type', 't')
            ->join('r.equipments', 'e')
            ->getQuery();
        
        $results = $query->getResult();

        // Regroupement des résultats par location
        $groupedResults = [];
        foreach ($results as $result) {
            $id = $result['id'];
            if (!isset($groupedResults[$id])) {
                $groupedResults[$id] = [
                    'id' => $result['id'],
                    'title' => $result['title'],
                    'description' => $result['description'],
                    'bedding' => $result['bedding'],
                    'surface' => $result['surface'],
                    'location' => $result['location'],
                    'isClean' => $result['isClean'],
                    'isActive' => $result['isActive'],
                    'type' => $result['typeLabel'],
                    'equipments' => [],
                ];
            }

            // Ajout des équipements
            $groupedResults[$id]['equipments'][] = [
                'equipmentLabel' => $result['equipmentLabel'],
            ];
        }

        // Conversion de l'array associatif en array indexé
        foreach($groupedResults as &$rental) {
            $rental['equipments'] = array_values($rental['equipments']);
        }

        return array_values($groupedResults);
    }

    /**
     * Méthode pour récupérer toutes les informations d'une location par son id
     * @param int $id
     * @return array
     */
    public function getAllInfosById(int $id): array
    {
        $entityManager = $this->getEntityManager();

        $qb = $entityManager->createQueryBuilder();

        $query = $qb->select([
            'r.id',
            'r.title',
            'r.description',
            'r.bedding',
            'r.surface',
            'r.location',
            'r.isClean',
            't.label as typeLabel',
            't.imagePath'
        ])
            ->from(Rental::class, 'r')
            ->join('r.type', 't')
            ->where('r.id = :id')
            ->andWhere('r.isActive = 1')
            ->setParameter('id', $id)
            ->getQuery();

        $results = $query->getResult();

        dd($results);

        return $results;
    }
}
