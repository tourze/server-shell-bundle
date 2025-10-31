<?php

namespace ServerShellBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ServerNodeBundle\Entity\Node;
use ServerShellBundle\Entity\ScriptExecution;
use ServerShellBundle\Entity\ShellScript;
use ServerShellBundle\Enum\CommandStatus;
use ServerShellBundle\Repository\ScriptExecutionRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(ScriptExecutionRepository::class)]
#[RunTestsInSeparateProcesses]
final class ScriptExecutionRepositoryTest extends AbstractRepositoryTestCase
{
    private ScriptExecutionRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(ScriptExecutionRepository::class);
    }

    public function testFindByStatus(): void
    {
        $result = $this->repository->findByStatus(CommandStatus::PENDING);
        $this->assertIsArray($result);
    }

    public function testCountBasicFunctionality(): void
    {
        $result = $this->repository->count([]);
        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function testFindAllBasicFunctionality(): void
    {
        $result = $this->repository->findAll();
        $this->assertIsArray($result);
    }

    public function testFindByBasicFunctionality(): void
    {
        $result = $this->repository->findBy(['status' => CommandStatus::PENDING]);
        $this->assertIsArray($result);
    }

    public function testFindOneByBasicFunctionality(): void
    {
        $result = $this->repository->findOneBy(['status' => CommandStatus::PENDING]);
        $this->assertTrue(null === $result or $result instanceof ScriptExecution);
    }

    public function testFindOneByWithOrderBy(): void
    {
        $entity1 = $this->createTestEntity();
        $entity1->setExecutionTime(1.0);
        $this->repository->save($entity1);

        $entity2 = $this->createTestEntity();
        $entity2->setExecutionTime(2.0);
        $this->repository->save($entity2);

        $result = $this->repository->findOneBy([], ['executionTime' => 'DESC']);
        $this->assertInstanceOf(ScriptExecution::class, $result);
        $this->assertGreaterThanOrEqual(2.0, $result->getExecutionTime() ?? 0);
    }

    public function testFindByNode(): void
    {
        $node = $this->createTestNode();
        $entity = $this->createTestEntity();
        $entity->setNode($node);
        $this->repository->save($entity);

        $result = $this->repository->findByNode($node);
        $this->assertIsArray($result);
        $this->assertContainsOnlyInstancesOf(ScriptExecution::class, $result);
    }

    public function testFindByScript(): void
    {
        $script = $this->createTestScript();
        $entity = $this->createTestEntity();
        $entity->setScript($script);
        $this->repository->save($entity);

        $result = $this->repository->findByScript($script);
        $this->assertIsArray($result);
        $this->assertContainsOnlyInstancesOf(ScriptExecution::class, $result);
    }

    public function testFindByNodeAndScript(): void
    {
        $node = $this->createTestNode();
        $script = $this->createTestScript();
        $entity = $this->createTestEntity();
        $entity->setNode($node);
        $entity->setScript($script);
        $this->repository->save($entity);

        $result = $this->repository->findByNodeAndScript($node, $script);
        $this->assertIsArray($result);
        $this->assertContainsOnlyInstancesOf(ScriptExecution::class, $result);
    }

    public function testSave(): void
    {
        $entity = $this->createTestEntity();

        $this->repository->save($entity);

        $this->assertGreaterThan(0, $entity->getId());
        $savedEntity = $this->repository->find($entity->getId());
        $this->assertInstanceOf(ScriptExecution::class, $savedEntity);
    }

    public function testRemove(): void
    {
        $entity = $this->createTestEntity();
        $this->repository->save($entity);
        $id = $entity->getId();

        $this->repository->remove($entity);

        $deletedEntity = $this->repository->find($id);
        $this->assertNull($deletedEntity);
    }

    public function testNullableFieldsQuery(): void
    {
        $entity = $this->createTestEntity();
        $entity->setResult(null);
        $entity->setExecutedAt(null);
        $entity->setExecutionTime(null);
        $entity->setExitCode(null);
        $this->repository->save($entity);

        $result = $this->repository->findBy(['result' => null]);
        $this->assertIsArray($result);

        $result2 = $this->repository->findBy(['executedAt' => null]);
        $this->assertIsArray($result2);

        $result3 = $this->repository->findBy(['executionTime' => null]);
        $this->assertIsArray($result3);

        $result4 = $this->repository->findBy(['exitCode' => null]);
        $this->assertIsArray($result4);
    }

    public function testCountNullableFields(): void
    {
        $entity = $this->createTestEntity();
        $entity->setResult(null);
        $entity->setExecutedAt(null);
        $entity->setExecutionTime(null);
        $entity->setExitCode(null);
        $this->repository->save($entity);

        $count = $this->repository->count(['result' => null]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(1, $count);

        $count2 = $this->repository->count(['executedAt' => null]);
        $this->assertIsInt($count2);
        $this->assertGreaterThanOrEqual(1, $count2);

        $count3 = $this->repository->count(['executionTime' => null]);
        $this->assertIsInt($count3);
        $this->assertGreaterThanOrEqual(1, $count3);

        $count4 = $this->repository->count(['exitCode' => null]);
        $this->assertIsInt($count4);
        $this->assertGreaterThanOrEqual(1, $count4);
    }

    public function testAssociationQuery(): void
    {
        $node = $this->createTestNode();
        $script = $this->createTestScript();

        $entity = $this->createTestEntity();
        $entity->setNode($node);
        $entity->setScript($script);
        $this->repository->save($entity);

        $result = $this->repository->findBy(['node' => $node]);
        $this->assertIsArray($result);
        $this->assertContainsOnlyInstancesOf(ScriptExecution::class, $result);

        $result2 = $this->repository->findBy(['script' => $script]);
        $this->assertIsArray($result2);
        $this->assertContainsOnlyInstancesOf(ScriptExecution::class, $result2);
    }

    public function testAssociationCountQuery(): void
    {
        $node = $this->createTestNode();
        $script = $this->createTestScript();

        $entity = $this->createTestEntity();
        $entity->setNode($node);
        $entity->setScript($script);
        $this->repository->save($entity);

        $count = $this->repository->count(['node' => $node]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(1, $count);

        $count2 = $this->repository->count(['script' => $script]);
        $this->assertIsInt($count2);
        $this->assertGreaterThanOrEqual(1, $count2);
    }

    private function createTestEntity(): ScriptExecution
    {
        $entity = new ScriptExecution();
        $entity->setNode($this->createTestNode());
        $entity->setScript($this->createTestScript());
        $entity->setStatus(CommandStatus::PENDING);

        return $entity;
    }

    private function createTestNode(): Node
    {
        $node = new Node();
        $node->setName('test-node');
        $node->setSshHost('127.0.0.1');
        $node->setSshPort(22);
        $node->setSshUser('test');
        self::getEntityManager()->persist($node);
        self::getEntityManager()->flush();

        return $node;
    }

    private function createTestScript(): ShellScript
    {
        $script = new ShellScript();
        $script->setName('test-script');
        $script->setContent('echo "test"');
        $script->setEnabled(true);
        self::getEntityManager()->persist($script);
        self::getEntityManager()->flush();

        return $script;
    }

    public function testCountByAssociationNodeShouldReturnCorrectNumber(): void
    {
        $node = $this->createTestNode();
        $entity = $this->createTestEntity();
        $entity->setNode($node);
        $this->repository->save($entity);

        $count = $this->repository->count(['node' => $node]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testCountByAssociationScriptShouldReturnCorrectNumber(): void
    {
        $script = $this->createTestScript();
        $entity = $this->createTestEntity();
        $entity->setScript($script);
        $this->repository->save($entity);

        $count = $this->repository->count(['script' => $script]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindOneByAssociationNodeShouldReturnMatchingEntity(): void
    {
        $node = $this->createTestNode();
        $entity = $this->createTestEntity();
        $entity->setNode($node);
        $this->repository->save($entity);

        $result = $this->repository->findOneBy(['node' => $node]);
        $this->assertInstanceOf(ScriptExecution::class, $result);
        $this->assertEquals($node->getId(), $result->getNode()->getId());
    }

    public function testFindOneByAssociationScriptShouldReturnMatchingEntity(): void
    {
        $script = $this->createTestScript();
        $entity = $this->createTestEntity();
        $entity->setScript($script);
        $this->repository->save($entity);

        $result = $this->repository->findOneBy(['script' => $script]);
        $this->assertInstanceOf(ScriptExecution::class, $result);
        $this->assertEquals($script->getId(), $result->getScript()->getId());
    }

    protected function createNewEntity(): object
    {
        // 使用已存在的测试方法，它会正确处理关联实体的持久化
        return $this->createTestEntity();
    }

    /**
     * @return ServiceEntityRepository<ScriptExecution>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
