<?php

namespace ServerShellBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use ServerNodeBundle\Entity\Node;
use ServerShellBundle\Entity\ScriptExecution;
use ServerShellBundle\Entity\ShellScript;
use ServerShellBundle\Enum\CommandStatus;

/**
 * @extends ServiceEntityRepository<ScriptExecution>
 */
class ScriptExecutionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ScriptExecution::class);
    }

    /**
     * 查找指定节点上的脚本执行记录
     */
    public function findByNode(Node $node): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.node = :node')
            ->setParameter('node', $node)
            ->orderBy('e.createTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找指定脚本的执行记录
     */
    public function findByScript(ShellScript $script): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.script = :script')
            ->setParameter('script', $script)
            ->orderBy('e.createTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找指定状态的执行记录
     */
    public function findByStatus(CommandStatus $status): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.status = :status')
            ->setParameter('status', $status)
            ->orderBy('e.createTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找指定节点和脚本的执行记录
     */
    public function findByNodeAndScript(Node $node, ShellScript $script): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.node = :node')
            ->andWhere('e.script = :script')
            ->setParameter('node', $node)
            ->setParameter('script', $script)
            ->orderBy('e.createTime', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
