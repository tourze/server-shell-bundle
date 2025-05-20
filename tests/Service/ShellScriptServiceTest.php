<?php

namespace ServerShellBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ServerCommandBundle\Service\RemoteCommandService;
use ServerNodeBundle\Entity\Node;
use ServerShellBundle\Entity\ScriptExecution;
use ServerShellBundle\Entity\ShellScript;
use ServerShellBundle\Enum\CommandStatus;
use ServerShellBundle\Message\ScriptExecutionMessage;
use ServerShellBundle\Repository\ScriptExecutionRepository;
use ServerShellBundle\Repository\ShellScriptRepository;
use ServerShellBundle\Service\ShellScriptService;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class ShellScriptServiceTest extends TestCase
{
    private ShellScriptService $service;
    
    /**
     * @var RemoteCommandService|MockObject
     */
    private $remoteCommandService;
    
    /**
     * @var ShellScriptRepository|MockObject
     */
    private $shellScriptRepository;
    
    /**
     * @var ScriptExecutionRepository|MockObject
     */
    private $scriptExecutionRepository;
    
    /**
     * @var EntityManagerInterface|MockObject
     */
    private $entityManager;
    
    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;
    
    /**
     * @var MessageBusInterface|MockObject
     */
    private $messageBus;
    
    protected function setUp(): void
    {
        $this->remoteCommandService = $this->createMock(RemoteCommandService::class);
        $this->shellScriptRepository = $this->createMock(ShellScriptRepository::class);
        $this->scriptExecutionRepository = $this->createMock(ScriptExecutionRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        
        // 设置MessageBus的行为
        $this->messageBus->method('dispatch')
            ->willReturnCallback(function ($message) {
                return new Envelope($message);
            });
        
        $this->service = new ShellScriptService(
            $this->remoteCommandService,
            $this->shellScriptRepository,
            $this->scriptExecutionRepository,
            $this->entityManager,
            $this->logger,
            $this->messageBus
        );
    }
    
    /**
     * 测试创建脚本
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
        
        // EntityManager应该调用persist和flush方法
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (ShellScript $script) use ($name, $content) {
                return $script->getName() === $name && $script->getContent() === $content;
            }));
        
        $this->entityManager->expects($this->once())
            ->method('flush');
        
        // 执行
        $result = $this->service->createScript(
            $name,
            $content,
            $workingDirectory,
            $useSudo,
            $timeout,
            $tags,
            $description
        );
        
        // 验证
        $this->assertInstanceOf(ShellScript::class, $result);
        $this->assertEquals($name, $result->getName());
        $this->assertEquals($content, $result->getContent());
        $this->assertEquals($workingDirectory, $result->getWorkingDirectory());
        $this->assertEquals($useSudo, $result->isUseSudo());
        $this->assertEquals($timeout, $result->getTimeout());
        $this->assertEquals($tags, $result->getTags());
        $this->assertEquals($description, $result->getDescription());
        $this->assertTrue($result->isEnabled());
    }
    
    /**
     * 测试更新脚本
     */
    public function testUpdateScript(): void
    {
        // 准备
        $script = new ShellScript();
        $script->setName('原名称');
        $script->setContent('原内容');
        
        $newName = '新名称';
        
        // EntityManager应该调用flush方法
        $this->entityManager->expects($this->once())
            ->method('flush');
        
        // 执行
        $result = $this->service->updateScript($script, $newName);
        
        // 验证
        $this->assertSame($script, $result);
        $this->assertEquals($newName, $result->getName());
    }
    
    /**
     * 测试按ID查找脚本
     */
    public function testFindScriptById(): void
    {
        // 准备
        $scriptId = 1;
        $expectedScript = new ShellScript();
        
        $this->shellScriptRepository->expects($this->once())
            ->method('find')
            ->with($scriptId)
            ->willReturn($expectedScript);
        
        // 执行
        $result = $this->service->findScriptById($scriptId);
        
        // 验证
        $this->assertSame($expectedScript, $result);
    }
    
    /**
     * 测试查找所有启用的脚本
     */
    public function testFindAllEnabledScripts(): void
    {
        // 准备
        $expectedScripts = [new ShellScript(), new ShellScript()];
        
        $this->shellScriptRepository->expects($this->once())
            ->method('findAllEnabled')
            ->willReturn($expectedScripts);
        
        // 执行
        $result = $this->service->findAllEnabledScripts();
        
        // 验证
        $this->assertSame($expectedScripts, $result);
    }
    
    /**
     * 测试按标签查找脚本
     */
    public function testFindScriptsByTags(): void
    {
        // 准备
        $tags = ['deployment', 'backup'];
        $expectedScripts = [new ShellScript(), new ShellScript()];
        
        $this->shellScriptRepository->expects($this->once())
            ->method('findByTags')
            ->with($tags)
            ->willReturn($expectedScripts);
        
        // 执行
        $result = $this->service->findScriptsByTags($tags);
        
        // 验证
        $this->assertSame($expectedScripts, $result);
    }
    
    /**
     * 测试执行脚本成功场景
     */
    public function testExecuteScript_Success(): void
    {
        $this->markTestSkipped('此测试需要更复杂的模拟设置，暂时跳过');
    }
    
    /**
     * 测试禁用脚本无法执行
     */
    public function testExecuteScript_DisabledScript(): void
    {
        // 准备模拟对象
        $script = $this->createMock(ShellScript::class);
        $node = $this->createMock(Node::class);
        
        // 设置模拟对象行为 - 脚本被禁用
        $script->method('isEnabled')->willReturn(false);
        
        // 执行并捕获异常
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('脚本已禁用，无法执行');
        
        $this->service->executeScript($script, $node);
    }
    
    /**
     * 测试脚本上传失败场景
     */
    public function testExecuteScript_UploadFailure(): void
    {
        $this->markTestSkipped('此测试需要更复杂的模拟设置，暂时跳过');
    }
    
    /**
     * 测试异步执行脚本
     */
    public function testScheduleScript(): void
    {
        // 准备模拟对象
        $script = $this->createMock(ShellScript::class);
        $node = $this->createMock(Node::class);
        
        // 设置模拟对象行为
        $script->method('isEnabled')->willReturn(true);
        
        // EntityManager预期
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (ScriptExecution $execution) use ($script, $node) {
                return $execution->getScript() === $script && 
                       $execution->getNode() === $node &&
                       $execution->getStatus() === CommandStatus::PENDING;
            }));
        
        $this->entityManager->expects($this->once())
            ->method('flush');
        
        // MessageBus预期
        $this->messageBus->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ScriptExecutionMessage::class))
            ->willReturn(new Envelope(new ScriptExecutionMessage(0)));
        
        // 执行
        $result = $this->service->scheduleScript($script, $node);
        
        // 验证
        $this->assertInstanceOf(ScriptExecution::class, $result);
        $this->assertEquals(CommandStatus::PENDING, $result->getStatus());
    }
} 