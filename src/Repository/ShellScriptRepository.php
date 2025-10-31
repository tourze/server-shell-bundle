<?php

namespace ServerShellBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use ServerShellBundle\Entity\ShellScript;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<ShellScript>
 */
#[AsRepository(entityClass: ShellScript::class)]
class ShellScriptRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShellScript::class);
    }

    /**
     * 按标签查找脚本
     * @param array<string> $tags
     * @return array<ShellScript>
     */
    public function findByTags(array $tags): array
    {
        /** @var array<ShellScript> */
        return $this->createQueryBuilder('s')
            ->where('s.tags IS NOT NULL')
            ->andWhere('s.tags IN (:tags)')
            ->setParameter('tags', $tags)
            ->orderBy('s.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找所有启用的脚本
     * @return array<ShellScript>
     */
    public function findAllEnabled(): array
    {
        /** @var array<ShellScript> */
        return $this->createQueryBuilder('s')
            ->where('s.enabled = :enabled')
            ->setParameter('enabled', true)
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function save(ShellScript $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ShellScript $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
