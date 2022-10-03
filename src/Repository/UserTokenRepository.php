<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserToken;
use Cake\Chronos\Chronos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserToken>
 *
 * @method UserToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserToken[]    findAll()
 * @method UserToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @codeCoverageIgnore
 */
class UserTokenRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserToken::class);
    }

    public function save(UserToken $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function invalidateAllTokens()
    {
        $this->getEntityManager()->createQueryBuilder()
            ->update(UserToken::class, 'uj')
            ->set('uj.invalidatedAt', ':invalidatedAt')
            ->andWhere('uj.invalidatedAt IS NULL')
            ->getQuery()
            ->setParameter('invalidatedAt', Chronos::now())
            ->execute();
    }

    public function invalidateUserTokens(User $user)
    {
        $this->getEntityManager()->createQueryBuilder()
            ->update(UserToken::class, 'uj')
            ->set('uj.invalidatedAt', ':invalidatedAt')
            ->andWhere('uj.invalidatedAt IS NULL')
            ->andWhere('uj.user  = :user')
            ->getQuery()
            ->setParameter('invalidatedAt', Chronos::now())
            ->setParameter('user', $user)
            ->execute();
    }
}
