<?php

namespace ServerShellBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ServerShellBundle\Entity\ShellScript;
use ServerShellBundle\Repository\ShellScriptRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(ShellScriptRepository::class)]
#[RunTestsInSeparateProcesses]
final class ShellScriptRepositoryTest extends AbstractRepositoryTestCase
{
    private ShellScriptRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(ShellScriptRepository::class);
    }

    public function testSaveEntityShouldPersistToDatabase(): void
    {
        $entity = new ShellScript();
        $entity->setName('test-script');
        $entity->setContent('echo "test"');
        $this->repository->save($entity);

        $this->assertNotNull($entity->getId());

        $found = $this->repository->find($entity->getId());
        $this->assertInstanceOf(ShellScript::class, $found);
    }

    public function testRemoveEntityShouldDeleteFromDatabase(): void
    {
        $entity = new ShellScript();
        $entity->setName('test-script');
        $entity->setContent('echo "test"');
        $this->repository->save($entity);
        $id = $entity->getId();

        $this->repository->remove($entity);

        $found = $this->repository->find($id);
        $this->assertNull($found);
    }

    public function testFindByTags(): void
    {
        $result = $this->repository->findByTags(['test', 'demo']);
        $this->assertIsArray($result);
    }

    public function testFindAllEnabled(): void
    {
        $result = $this->repository->findAllEnabled();
        $this->assertIsArray($result);
    }

    public function testFindOneByWithOrderBy(): void
    {
        $entity1 = new ShellScript();
        $entity1->setName('oneby-order-test-1');
        $entity1->setContent('echo "oneby order test 1"');
        $this->repository->save($entity1);

        $entity2 = new ShellScript();
        $entity2->setName('oneby-order-test-2');
        $entity2->setContent('echo "oneby order test 2"');
        $this->repository->save($entity2);

        $found = $this->repository->findOneBy([], ['id' => 'DESC']);
        $this->assertInstanceOf(ShellScript::class, $found);
    }

    public function testFindByWithNullableFields(): void
    {
        $entity = new ShellScript();
        $entity->setName('nullable-test-script');
        $entity->setContent('echo "nullable test"');
        $entity->setWorkingDirectory(null);
        $entity->setUseSudo(null);
        $entity->setTimeout(null);
        $entity->setEnabled(null);
        $entity->setTags(null);
        $entity->setDescription(null);
        $this->repository->save($entity);

        $resultWithNullTags = $this->repository->findBy(['tags' => null]);
        $this->assertIsArray($resultWithNullTags);

        $resultWithNullDescription = $this->repository->findBy(['description' => null]);
        $this->assertIsArray($resultWithNullDescription);

        $resultWithNullWorkingDirectory = $this->repository->findBy(['workingDirectory' => null]);
        $this->assertIsArray($resultWithNullWorkingDirectory);

        $resultWithNullUseSudo = $this->repository->findBy(['useSudo' => null]);
        $this->assertIsArray($resultWithNullUseSudo);

        $resultWithNullTimeout = $this->repository->findBy(['timeout' => null]);
        $this->assertIsArray($resultWithNullTimeout);

        $resultWithNullEnabled = $this->repository->findBy(['enabled' => null]);
        $this->assertIsArray($resultWithNullEnabled);
    }

    public function testCountWithNullableFields(): void
    {
        $entity = new ShellScript();
        $entity->setName('count-nullable-test-script');
        $entity->setContent('echo "count nullable test"');
        $entity->setWorkingDirectory(null);
        $entity->setUseSudo(null);
        $entity->setTimeout(null);
        $entity->setEnabled(null);
        $entity->setTags(null);
        $entity->setDescription(null);
        $this->repository->save($entity);

        $countWithNullTags = $this->repository->count(['tags' => null]);
        $this->assertGreaterThanOrEqual(1, $countWithNullTags);

        $countWithNullDescription = $this->repository->count(['description' => null]);
        $this->assertGreaterThanOrEqual(1, $countWithNullDescription);

        $countWithNullWorkingDirectory = $this->repository->count(['workingDirectory' => null]);
        $this->assertGreaterThanOrEqual(1, $countWithNullWorkingDirectory);

        $countWithNullUseSudo = $this->repository->count(['useSudo' => null]);
        $this->assertGreaterThanOrEqual(1, $countWithNullUseSudo);

        $countWithNullTimeout = $this->repository->count(['timeout' => null]);
        $this->assertGreaterThanOrEqual(1, $countWithNullTimeout);

        $countWithNullEnabled = $this->repository->count(['enabled' => null]);
        $this->assertGreaterThanOrEqual(1, $countWithNullEnabled);
    }

    protected function createNewEntity(): object
    {
        $entity = new ShellScript();
        $entity->setName('Test ShellScript ' . uniqid());
        $entity->setContent('echo "test"');

        return $entity;
    }

    /**
     * @return ServiceEntityRepository<ShellScript>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
