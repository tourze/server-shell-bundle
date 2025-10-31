<?php

namespace ServerShellBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use ServerNodeBundle\Entity\Node;
use ServerShellBundle\Entity\ScriptExecution;
use ServerShellBundle\Entity\ShellScript;
use ServerShellBundle\Enum\CommandStatus;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<ScriptExecution>
 */
#[AsRepository(entityClass: ScriptExecution::class)]
class ScriptExecutionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ScriptExecution::class);
    }

    /**
     * 查找指定节点上的脚本执行记录
     * @return array<ScriptExecution>
     */
    public function findByNode(Node $node): array
    {
        /** @var array<ScriptExecution> */
        return $this->createQueryBuilder('e')
            ->where('e.node = :node')
            ->setParameter('node', $node)
            ->orderBy('e.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找指定脚本的执行记录
     * @return array<ScriptExecution>
     */
    public function findByScript(ShellScript $script): array
    {
        /** @var array<ScriptExecution> */
        return $this->createQueryBuilder('e')
            ->where('e.script = :script')
            ->setParameter('script', $script)
            ->orderBy('e.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找指定状态的执行记录
     * @return array<ScriptExecution>
     */
    public function findByStatus(CommandStatus $status): array
    {
        /** @var array<ScriptExecution> */
        return $this->createQueryBuilder('e')
            ->where('e.status = :status')
            ->setParameter('status', $status)
            ->orderBy('e.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找指定节点和脚本的执行记录
     * @return array<ScriptExecution>
     */
    public function findByNodeAndScript(Node $node, ShellScript $script): array
    {
        /** @var array<ScriptExecution> */
        return $this->createQueryBuilder('e')
            ->where('e.node = :node')
            ->andWhere('e.script = :script')
            ->setParameter('node', $node)
            ->setParameter('script', $script)
            ->orderBy('e.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function save(ScriptExecution $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ScriptExecution $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
