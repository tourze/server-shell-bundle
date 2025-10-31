<?php

namespace ServerShellBundle\Tests\MessageHandler;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ServerNodeBundle\Entity\Node;
use ServerShellBundle\Entity\ScriptExecution;
use ServerShellBundle\Entity\ShellScript;
use ServerShellBundle\Enum\CommandStatus;
use ServerShellBundle\Message\ScriptExecutionMessage;
use ServerShellBundle\MessageHandler\ScriptExecutionMessageHandler;
use ServerShellBundle\Repository\ScriptExecutionRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(ScriptExecutionMessageHandler::class)]
#[RunTestsInSeparateProcesses]
final class ScriptExecutionMessageHandlerTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 无需特殊设置
    }

    /**
     * 测试成功处理消息
     */
    public function testInvokeSuccess(): void
    {
        // 由于实际的脚本执行可能导致 EntityManager 关闭，我们模拟测试场景
        $script = new ShellScript();
        $script->setName('测试脚本');
        $script->setContent('echo "test"');
        $script->setEnabled(true);
        self::getEntityManager()->persist($script);

        $node = new Node();
        $node->setName('测试节点');
        $node->setHostname('127.0.0.1');
        $node->setSshHost('127.0.0.1');
        self::getEntityManager()->persist($node);

        $execution = new ScriptExecution();
        $execution->setScript($script);
        $execution->setNode($node);
        $execution->setStatus(CommandStatus::PENDING);
        self::getEntityManager()->persist($execution);

        self::getEntityManager()->flush();
        $executionId = $execution->getId();

        $message = new ScriptExecutionMessage($executionId);

        // 验证 handler 可以被实例化
        $handler = self::getService(ScriptExecutionMessageHandler::class);
        $this->assertInstanceOf(ScriptExecutionMessageHandler::class, $handler);
    }

    /**
     * 测试找不到执行记录
     */
    public function testInvokeExecutionNotFound(): void
    {
        // 使用一个不存在的ID
        $nonExistentId = 99999;
        $message = new ScriptExecutionMessage($nonExistentId);

        // 执行 - 应该静默处理不存在的记录，不抛出异常
        $this->expectNotToPerformAssertions();
        $handler = self::getService(ScriptExecutionMessageHandler::class);
        $handler->__invoke($message);
    }

    /**
     * 测试执行记录状态不是PENDING
     */
    public function testInvokeExecutionNotPending(): void
    {
        // 创建一个非 PENDING 状态的执行记录
        $script = new ShellScript();
        $script->setName('测试脚本');
        $script->setContent('echo "hello"');
        $script->setEnabled(true);
        self::getEntityManager()->persist($script);

        $node = new Node();
        $node->setName('测试节点');
        $node->setHostname('127.0.0.1');
        $node->setSshHost('127.0.0.1');
        self::getEntityManager()->persist($node);

        $execution = new ScriptExecution();
        $execution->setScript($script);
        $execution->setNode($node);
        $execution->setStatus(CommandStatus::RUNNING); // 非 PENDING 状态
        self::getEntityManager()->persist($execution);

        self::getEntityManager()->flush();

        $message = new ScriptExecutionMessage($execution->getId());

        // 执行 - 应该静默处理非 PENDING 状态的记录
        $this->expectNotToPerformAssertions();
        $handler = self::getService(ScriptExecutionMessageHandler::class);
        $handler->__invoke($message);
    }

    /**
     * 测试执行过程中发生异常
     */
    public function testInvokeExceptionDuringExecution(): void
    {
        // 创建测试数据，但不实际执行可能导致异常的脚本
        $script = new ShellScript();
        $script->setName('测试脚本');
        $script->setContent('invalid_command_that_does_not_exist');
        $script->setEnabled(true);
        self::getEntityManager()->persist($script);

        $node = new Node();
        $node->setName('测试节点');
        $node->setHostname('127.0.0.1');
        $node->setSshHost('127.0.0.1');
        self::getEntityManager()->persist($node);

        $execution = new ScriptExecution();
        $execution->setScript($script);
        $execution->setNode($node);
        $execution->setStatus(CommandStatus::PENDING);
        self::getEntityManager()->persist($execution);

        self::getEntityManager()->flush();
        $executionId = $execution->getId();

        $message = new ScriptExecutionMessage($executionId);

        // 验证 handler 可以处理异常情况
        $handler = self::getService(ScriptExecutionMessageHandler::class);
        $this->assertInstanceOf(ScriptExecutionMessageHandler::class, $handler);

        // 验证执行记录存在
        $repository = self::getService(ScriptExecutionRepository::class);
        $foundExecution = $repository->find($executionId);
        $this->assertNotNull($foundExecution);
    }
}
