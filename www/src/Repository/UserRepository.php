<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

        /**
     * Méthode qui retourne tous les utlisateurs avec ROLE_ADMIN
     * @return User[]
     */
    public function findAllAdmins(): array
    {
        $entityManager = $this->getEntityManager();

        $qb = $entityManager->createQueryBuilder();

        $query = $qb->select([
            'u.id',
            'u.username',
            'u.firstname',
            'u.lastname',
            'u.email',
            'u.roles',
        ])
        ->from(User::class, 'u')
        ->where('u.roles LIKE :roles')
        ->setParameter('roles', '%ROLE_ADMIN%')
        ->getQuery();
        
        return $query->getResult();
    }

    /**
     * Méthode qui retourne tous les utlisateurs avec ROLE_USER
     * @return User[]
     */
    public function findAllUsers(): array
    {
        $entityManager = $this->getEntityManager();

        $qb = $entityManager->createQueryBuilder();

        $query = $qb->select([
            'u.id',
            'u.username',
            'u.firstname',
            'u.lastname',
            'u.email',
            'u.roles',
        ])
        ->from(User::class, 'u')
        ->where('u.roles LIKE :roles')
        ->setParameter('roles', '%ROLE_USER%')
        ->getQuery();
        
        return $query->getResult();
    }

}
