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
     * Méthode pour créer un type
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
     * Méthode pour récupérer toutes les informations d'un type
     * @return array
     */
    public function getAllInfos(): array
    {
        $entityManager = $this->getEntityManager();

        $qb = $entityManager->createQueryBuilder();

        $query = $qb->select([
            't.id',
            't.label',
            't.imagePath',
            'p.label as priceLabel',
            'p.price',
            'p.dateStart',
            'p.dateEnd'
        ])
            ->from(Type::class, 't')
            ->join('t.prices', 'p')
            ->getQuery();
        
        $results = $query->getResult();

        // On regroupe les résultats par prix
        $groupedResults = [];
        foreach ($results as $result) {
            $id = $result['id'];
            if (!isset($groupedResults[$id])) {
                $groupedResults[$id] = [
                    'id' => $result['id'],
                    'label' => $result['label'],
                    'imagePath' => $result['imagePath'],
                    'prices' => []
                ];
            }

            // On ajoute le prix au tableau des prix
            $groupedResults[$id]['prices'][] = [
                'label' => $result['priceLabel'],
                'price' => $result['price'],
                'dateStart' => $result['dateStart'],
                'dateEnd' => $result['dateEnd']
            ];
        }

        // On convertit les tableaux associatifs en tableaux indexés
        foreach($groupedResults as &$type) {
            $type['prices'] = array_values($type['prices']);
        }

        return array_values($groupedResults);
    }
}
