<?php

namespace ServerShellBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use ServerShellBundle\Entity\ShellScript;

/**
 * @extends ServiceEntityRepository<ShellScript>
 */
class ShellScriptRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShellScript::class);
    }

    /**
     * 按标签查找脚本
     */
    public function findByTags(array $tags): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.tags IS NOT NULL')
            ->andWhere('s.tags IN (:tags)')
            ->setParameter('tags', $tags)
            ->orderBy('s.createTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找所有启用的脚本
     */
    public function findAllEnabled(): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.enabled = :enabled')
            ->setParameter('enabled', true)
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
