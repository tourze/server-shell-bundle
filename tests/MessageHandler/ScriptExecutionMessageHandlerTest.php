<?php

namespace ServerShellBundle\Tests\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ServerNodeBundle\Entity\Node;
use ServerShellBundle\Entity\ScriptExecution;
use ServerShellBundle\Entity\ShellScript;
use ServerShellBundle\Enum\CommandStatus;
use ServerShellBundle\Message\ScriptExecutionMessage;
use ServerShellBundle\MessageHandler\ScriptExecutionMessageHandler;
use ServerShellBundle\Repository\ScriptExecutionRepository;
use ServerShellBundle\Service\ShellScriptService;

class ScriptExecutionMessageHandlerTest extends TestCase
{
    /**
     * @var ScriptExecutionMessageHandler
     */
    private $handler;
    
    /**
     * @var EntityManagerInterface|MockObject
     */
    private $entityManager;
    
    /**
     * @var ScriptExecutionRepository|MockObject
     */
    private $scriptExecutionRepository;
    
    /**
     * @var ShellScriptService|MockObject
     */
    private $shellScriptService;
    
    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;
    
    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->scriptExecutionRepository = $this->createMock(ScriptExecutionRepository::class);
        $this->shellScriptService = $this->createMock(ShellScriptService::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        
        $this->handler = new ScriptExecutionMessageHandler(
            $this->entityManager,
            $this->scriptExecutionRepository,
            $this->shellScriptService,
            $this->logger
        );
    }
    
    /**
     * 测试成功处理消息
     */
    public function testInvoke_Success(): void
    {
        // 准备模拟对象
        $executionId = 123;
        $message = new ScriptExecutionMessage($executionId);
        
        $script = $this->createMock(ShellScript::class);
        $node = $this->createMock(Node::class);
        
        $execution = $this->createMock(ScriptExecution::class);
        $execution->method('getStatus')->willReturn(CommandStatus::PENDING);
        $execution->method('getScript')->willReturn($script);
        $execution->method('getNode')->willReturn($node);
        
        // 设置存储库查找期望
        $this->scriptExecutionRepository->expects($this->once())
            ->method('find')
            ->with($executionId)
            ->willReturn($execution);
        
        // 设置服务调用期望
        $this->shellScriptService->expects($this->once())
            ->method('executeScript')
            ->with($script, $node);
        
        // 执行
        $this->handler->__invoke($message);
    }
    
    /**
     * 测试找不到执行记录
     */
    public function testInvoke_ExecutionNotFound(): void
    {
        // 准备模拟对象
        $executionId = 123;
        $message = new ScriptExecutionMessage($executionId);
        
        // 设置存储库查找期望 - 返回null
        $this->scriptExecutionRepository->expects($this->once())
            ->method('find')
            ->with($executionId)
            ->willReturn(null);
        
        // 设置日志期望
        $this->logger->expects($this->once())
            ->method('error')
            ->with('找不到脚本执行记录', ['execution_id' => $executionId]);
        
        // 服务不应被调用
        $this->shellScriptService->expects($this->never())
            ->method('executeScript');
        
        // 执行
        $this->handler->__invoke($message);
    }
    
    /**
     * 测试执行记录状态不是PENDING
     */
    public function testInvoke_ExecutionNotPending(): void
    {
        // 准备模拟对象
        $executionId = 123;
        $message = new ScriptExecutionMessage($executionId);
        
        $execution = $this->createMock(ScriptExecution::class);
        $execution->method('getStatus')->willReturn(CommandStatus::RUNNING);
        
        // 设置存储库查找期望
        $this->scriptExecutionRepository->expects($this->once())
            ->method('find')
            ->with($executionId)
            ->willReturn($execution);
        
        // 设置日志期望
        $this->logger->expects($this->once())
            ->method('warning')
            ->with('脚本执行状态不是待处理', [
                'execution_id' => $executionId,
                'status' => CommandStatus::RUNNING->value,
            ]);
        
        // 服务不应被调用
        $this->shellScriptService->expects($this->never())
            ->method('executeScript');
        
        // 执行
        $this->handler->__invoke($message);
    }
    
    /**
     * 测试执行过程中发生异常
     */
    public function testInvoke_ExceptionDuringExecution(): void
    {
        // 准备模拟对象
        $executionId = 123;
        $message = new ScriptExecutionMessage($executionId);
        
        $script = $this->createMock(ShellScript::class);
        $node = $this->createMock(Node::class);
        
        $execution = $this->createMock(ScriptExecution::class);
        $execution->method('getStatus')->willReturn(CommandStatus::PENDING);
        $execution->method('getScript')->willReturn($script);
        $execution->method('getNode')->willReturn($node);
        
        // 设置存储库查找期望
        $this->scriptExecutionRepository->expects($this->once())
            ->method('find')
            ->with($executionId)
            ->willReturn($execution);
        
        // 设置服务调用期望 - 抛出异常
        $exception = new \RuntimeException('执行失败');
        $this->shellScriptService->expects($this->once())
            ->method('executeScript')
            ->with($script, $node)
            ->willThrowException($exception);
        
        // 日志应记录错误
        $this->logger->expects($this->once())
            ->method('error')
            ->with('异步执行脚本时出错', [
                'execution_id' => $executionId,
                'exception' => $exception,
            ]);
        
        // 执行记录状态应更新为失败
        $execution->expects($this->once())
            ->method('setStatus')
            ->with(CommandStatus::FAILED);
        
        $execution->expects($this->once())
            ->method('setResult')
            ->with($this->stringContains('异步执行出错'));
        
        // EntityManager应调用flush
        $this->entityManager->expects($this->once())
            ->method('flush');
        
        // 执行
        $this->handler->__invoke($message);
    }
} 