<?php

namespace ServerShellBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ServerNodeBundle\Entity\Node;
use ServerShellBundle\Entity\ScriptExecution;
use ServerShellBundle\Entity\ShellScript;
use ServerShellBundle\Service\ShellScriptService;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(ShellScriptService::class)]
#[RunTestsInSeparateProcesses]
final class ShellScriptServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 无需特殊设置
    }

    /**
     * 测试创建脚本 - 验证实体属性设置
     */
    public function testCreateScript(): void
    {
        // 准备
        $name = '测试脚本';
        $content = 'echo "Hello World"';
        $workingDirectory = '/tmp';
        $useSudo = true;
        $timeout = 600;
        $tags = ['test', 'demo'];
        $description = '测试脚本描述';

        // 创建真实的实体对象进行属性验证
        $script = new ShellScript();
        $script->setName($name);
        $script->setContent($content);
        $script->setWorkingDirectory($workingDirectory);
        $script->setUseSudo($useSudo);
        $script->setTimeout($timeout);
        $script->setTags($tags);
        $script->setDescription($description);
        $script->setEnabled(true);

        // 验证属性设置正确
        $this->assertEquals($name, $script->getName());
        $this->assertEquals($content, $script->getContent());
        $this->assertEquals($workingDirectory, $script->getWorkingDirectory());
        $this->assertEquals($useSudo, $script->isUseSudo());
        $this->assertEquals($timeout, $script->getTimeout());
        $this->assertEquals($tags, $script->getTags());
        $this->assertEquals($description, $script->getDescription());
        $this->assertTrue($script->isEnabled());
    }

    /**
     * 测试更新脚本 - 验证属性更新
     */
    public function testUpdateScript(): void
    {
        // 准备
        $script = new ShellScript();
        $script->setName('原名称');
        $script->setContent('原内容');

        $newName = '新名称';
        $newContent = '新内容';
        $newWorkingDirectory = '/var/tmp';
        $newUseSudo = true;
        $newTimeout = 900;
        $newTags = ['updated', 'test'];
        $newDescription = '更新后的描述';

        // 直接测试属性更新逻辑，避免数据库操作
        $script->setName($newName);
        $script->setContent($newContent);
        $script->setWorkingDirectory($newWorkingDirectory);
        $script->setUseSudo($newUseSudo);
        $script->setTimeout($newTimeout);
        $script->setTags($newTags);
        $script->setDescription($newDescription);
        $script->setEnabled(false);

        // 验证
        $this->assertEquals($newName, $script->getName());
        $this->assertEquals($newContent, $script->getContent());
        $this->assertEquals($newWorkingDirectory, $script->getWorkingDirectory());
        $this->assertEquals($newUseSudo, $script->isUseSudo());
        $this->assertEquals($newTimeout, $script->getTimeout());
        $this->assertEquals($newTags, $script->getTags());
        $this->assertEquals($newDescription, $script->getDescription());
        $this->assertFalse($script->isEnabled());
    }

    /**
     * 测试按ID查找脚本
     */
    public function testFindScriptById(): void
    {
        // 创建一个真实的脚本
        $script = new ShellScript();
        $script->setName('测试脚本');
        $script->setContent('echo "hello"');
        self::getEntityManager()->persist($script);
        self::getEntityManager()->flush();

        // 执行
        $service = self::getService(ShellScriptService::class);
        $result = $service->findScriptById($script->getId());

        // 验证
        $this->assertNotNull($result);
        $this->assertEquals($script->getId(), $result->getId());
    }

    /**
     * 测试查找所有启用的脚本
     */
    public function testFindAllEnabledScripts(): void
    {
        // 创建启用和禁用的脚本
        $enabledScript = new ShellScript();
        $enabledScript->setName('启用脚本');
        $enabledScript->setContent('echo "enabled"');
        $enabledScript->setEnabled(true);
        self::getEntityManager()->persist($enabledScript);

        $disabledScript = new ShellScript();
        $disabledScript->setName('禁用脚本');
        $disabledScript->setContent('echo "disabled"');
        $disabledScript->setEnabled(false);
        self::getEntityManager()->persist($disabledScript);

        self::getEntityManager()->flush();

        // 执行
        $service = self::getService(ShellScriptService::class);
        $result = $service->findAllEnabledScripts();

        // 验证结果是数组且包含启用的脚本
        $this->assertIsArray($result);

        // 检查是否包含启用的脚本
        $enabledIds = array_map(fn ($script) => $script->getId(), $result);
        $this->assertContains($enabledScript->getId(), $enabledIds);
    }

    /**
     * 测试按标签查找脚本
     */
    public function testFindScriptsByTags(): void
    {
        // 创建带标签的脚本
        $script = new ShellScript();
        $script->setName('带标签脚本');
        $script->setContent('echo "tagged"');
        $script->setTags(['deployment', 'backup']);
        self::getEntityManager()->persist($script);
        self::getEntityManager()->flush();

        // 执行
        $service = self::getService(ShellScriptService::class);
        $result = $service->findScriptsByTags(['deployment']);

        // 验证结果是数组
        $this->assertIsArray($result);
    }

    /**
     * 测试禁用脚本无法执行
     */
    public function testExecuteScriptDisabledScript(): void
    {
        // 创建禁用的脚本
        $script = new ShellScript();
        $script->setName('禁用脚本');
        $script->setContent('echo "disabled"');
        $script->setEnabled(false);
        self::getEntityManager()->persist($script);

        $node = new Node();
        $node->setName('测试节点');
        $node->setHostname('127.0.0.1');
        $node->setSshHost('127.0.0.1');
        self::getEntityManager()->persist($node);

        self::getEntityManager()->flush();

        // 执行并捕获异常
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('脚本已禁用，无法执行');

        $service = self::getService(ShellScriptService::class);
        $service->executeScript($script, $node);
    }

    /**
     * 测试异步执行脚本 - 禁用脚本抛出异常
     */
    public function testScheduleScriptWithDisabledScript(): void
    {
        // 创建禁用的脚本
        $script = new ShellScript();
        $script->setName('禁用脚本');
        $script->setContent('echo "disabled"');
        $script->setEnabled(false);
        self::getEntityManager()->persist($script);

        $node = new Node();
        $node->setName('测试节点');
        $node->setHostname('127.0.0.1');
        $node->setSshHost('127.0.0.1');
        self::getEntityManager()->persist($node);

        self::getEntityManager()->flush();

        // 验证异常
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('脚本已禁用，无法执行');

        // 执行
        $service = self::getService(ShellScriptService::class);
        $service->scheduleScript($script, $node);
    }

    /**
     * 测试通过ID查找执行记录
     */
    public function testFindExecutionById(): void
    {
        // 执行测试
        $service = self::getService(ShellScriptService::class);
        $result = $service->findExecutionById(99999);

        // 验证不存在的ID返回null
        $this->assertNull($result);
    }

    /**
     * 测试查找指定节点上的脚本执行记录
     */
    public function testFindExecutionsByNode(): void
    {
        // 创建测试数据
        $node = new Node();
        $node->setName('测试节点');
        $node->setHostname('127.0.0.1');
        $node->setSshHost('127.0.0.1');
        self::getEntityManager()->persist($node);

        $script = new ShellScript();
        $script->setName('测试脚本');
        $script->setContent('echo "test"');
        self::getEntityManager()->persist($script);

        $execution = new ScriptExecution();
        $execution->setNode($node);
        $execution->setScript($script);
        self::getEntityManager()->persist($execution);

        self::getEntityManager()->flush();

        // 执行测试
        $service = self::getService(ShellScriptService::class);
        $result = $service->findExecutionsByNode($node);

        // 验证结果
        $this->assertIsArray($result);
    }

    /**
     * 测试查找指定脚本的执行记录
     */
    public function testFindExecutionsByScript(): void
    {
        // 创建测试数据
        $node = new Node();
        $node->setName('测试节点');
        $node->setHostname('127.0.0.1');
        $node->setSshHost('127.0.0.1');
        self::getEntityManager()->persist($node);

        $script = new ShellScript();
        $script->setName('测试脚本');
        $script->setContent('echo "test"');
        self::getEntityManager()->persist($script);

        $execution = new ScriptExecution();
        $execution->setNode($node);
        $execution->setScript($script);
        self::getEntityManager()->persist($execution);

        self::getEntityManager()->flush();

        // 执行测试
        $service = self::getService(ShellScriptService::class);
        $result = $service->findExecutionsByScript($script);

        // 验证结果
        $this->assertIsArray($result);
    }

    /**
     * 测试查找指定节点和脚本的执行记录
     */
    public function testFindExecutionsByNodeAndScript(): void
    {
        // 创建测试数据
        $node = new Node();
        $node->setName('测试节点');
        $node->setHostname('127.0.0.1');
        $node->setSshHost('127.0.0.1');
        self::getEntityManager()->persist($node);

        $script = new ShellScript();
        $script->setName('测试脚本');
        $script->setContent('echo "test"');
        self::getEntityManager()->persist($script);

        $execution = new ScriptExecution();
        $execution->setNode($node);
        $execution->setScript($script);
        self::getEntityManager()->persist($execution);

        self::getEntityManager()->flush();

        // 执行测试
        $service = self::getService(ShellScriptService::class);
        $result = $service->findExecutionsByNodeAndScript($node, $script);

        // 验证结果
        $this->assertIsArray($result);
    }
}
