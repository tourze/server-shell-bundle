<?php

namespace ServerShellBundle\Service;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use SebastianBergmann\Timer\Timer;
use ServerNodeBundle\Entity\Node;
use ServerShellBundle\Entity\ScriptExecution;
use ServerShellBundle\Entity\ShellScript;
use ServerShellBundle\Enum\CommandStatus;
use ServerShellBundle\Message\ScriptExecutionMessage;
use ServerShellBundle\Repository\ScriptExecutionRepository;
use ServerShellBundle\Repository\ShellScriptRepository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Messenger\MessageBusInterface;

class ShellScriptService
{
    private const TEMP_SCRIPT_DIR = '/tmp/shell_scripts';

    public function __construct(
        private readonly RemoteCommandService $remoteCommandService,
        private readonly ShellScriptRepository $shellScriptRepository,
        private readonly ScriptExecutionRepository $scriptExecutionRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    /**
     * 创建新的Shell脚本
     */
    public function createScript(
        string $name,
        string $content,
        ?string $workingDirectory = null,
        ?bool $useSudo = false,
        ?int $timeout = 300,
        ?array $tags = null,
        ?string $description = null,
    ): ShellScript {
        $script = new ShellScript();
        $script->setName($name);
        $script->setContent($content);
        $script->setWorkingDirectory($workingDirectory);
        $script->setUseSudo($useSudo);
        $script->setTimeout($timeout);
        $script->setTags($tags);
        $script->setDescription($description);
        $script->setEnabled(true);

        $this->entityManager->persist($script);
        $this->entityManager->flush();

        return $script;
    }

    /**
     * 更新Shell脚本
     */
    public function updateScript(
        ShellScript $script,
        ?string $name = null,
        ?string $content = null,
        ?string $workingDirectory = null,
        ?bool $useSudo = null,
        ?int $timeout = null,
        ?array $tags = null,
        ?string $description = null,
        ?bool $enabled = null,
    ): ShellScript {
        if ($name !== null) {
            $script->setName($name);
        }
        if ($content !== null) {
            $script->setContent($content);
        }
        if ($workingDirectory !== null) {
            $script->setWorkingDirectory($workingDirectory);
        }
        if ($useSudo !== null) {
            $script->setUseSudo($useSudo);
        }
        if ($timeout !== null) {
            $script->setTimeout($timeout);
        }
        if ($tags !== null) {
            $script->setTags($tags);
        }
        if ($description !== null) {
            $script->setDescription($description);
        }
        if ($enabled !== null) {
            $script->setEnabled($enabled);
        }

        $this->entityManager->flush();

        return $script;
    }

    /**
     * 按ID查找脚本
     */
    public function findScriptById(int $id): ?ShellScript
    {
        return $this->shellScriptRepository->find($id);
    }

    /**
     * 查找所有启用的脚本
     */
    public function findAllEnabledScripts(): array
    {
        return $this->shellScriptRepository->findAllEnabled();
    }

    /**
     * 按标签查找脚本
     */
    public function findScriptsByTags(array $tags): array
    {
        return $this->shellScriptRepository->findByTags($tags);
    }

    /**
     * 执行Shell脚本
     */
    public function executeScript(ShellScript $script, Node $node): ScriptExecution
    {
        if (!$script->isEnabled()) {
            throw new \RuntimeException('脚本已禁用，无法执行');
        }

        // 创建执行记录
        $execution = new ScriptExecution();
        $execution->setScript($script);
        $execution->setNode($node);
        $execution->setStatus(CommandStatus::PENDING);
        
        $this->entityManager->persist($execution);
        $this->entityManager->flush();

        try {
            // 标记为正在执行
            $execution->setStatus(CommandStatus::RUNNING);
            $execution->setExecutedAt(new DateTime());
            $this->entityManager->flush();

            // 执行脚本
            $timer = new Timer();
            $timer->start();

            // 创建临时脚本文件
            $scriptPath = $this->createTempScriptFile($script);
            
            // 构建上传命令
            $remoteScriptPath = '/tmp/' . basename($scriptPath);
            $uploadCommand = $this->remoteCommandService->createCommand(
                $node,
                '上传脚本: ' . $script->getName(),
                "cat > {$remoteScriptPath} << 'EOL'\n{$script->getContent()}\nEOL\nchmod +x {$remoteScriptPath}",
                '/tmp',
                false,
                30
            );
            
            // 执行上传
            $uploadResult = $this->remoteCommandService->executeCommand($uploadCommand);
            if ($uploadCommand->getStatus() !== CommandStatus::COMPLETED) {
                throw new \RuntimeException('脚本上传失败: ' . $uploadResult->getResult());
            }
            
            // 构建执行命令
            $workingDir = $script->getWorkingDirectory() ?: '/tmp';
            $execCommand = $this->remoteCommandService->createCommand(
                $node,
                '执行脚本: ' . $script->getName(),
                $remoteScriptPath,
                $workingDir,
                $script->isUseSudo(),
                $script->getTimeout()
            );
            
            // 执行脚本
            $execResult = $this->remoteCommandService->executeCommand($execCommand);
            
            // 清理远程脚本
            $cleanupCommand = $this->remoteCommandService->createCommand(
                $node,
                '清理脚本: ' . $script->getName(),
                "rm -f {$remoteScriptPath}",
                '/tmp',
                false,
                30
            );
            $this->remoteCommandService->executeCommand($cleanupCommand);
            
            // 本地清理
            if (file_exists($scriptPath)) {
                unlink($scriptPath);
            }

            $executionTime = $timer->stop()->asSeconds();

            // 更新执行结果
            $execution->setResult($execResult->getResult());
            $execution->setStatus($execResult->getStatus());
            $execution->setExecutionTime($executionTime);
            $execution->setExitCode($execResult->getStatus() === CommandStatus::COMPLETED ? 0 : 1);
            
            $this->entityManager->flush();
            
            return $execution;
        } catch (\Exception $e) {
            $this->logger->error('执行脚本时出错: ' . $e->getMessage(), [
                'script_id' => $script->getId(),
                'node_id' => $node->getId(),
                'exception' => $e,
            ]);
            
            $execution->setStatus(CommandStatus::FAILED);
            $execution->setResult('执行出错: ' . $e->getMessage());
            $this->entityManager->flush();
            
            return $execution;
        }
    }

    /**
     * 创建临时脚本文件
     */
    private function createTempScriptFile(ShellScript $script): string
    {
        $fs = new Filesystem();
        
        // 确保临时目录存在
        if (!$fs->exists(self::TEMP_SCRIPT_DIR)) {
            $fs->mkdir(self::TEMP_SCRIPT_DIR, 0700);
        }
        
        // 创建临时脚本文件
        $filename = self::TEMP_SCRIPT_DIR . '/script_' . $script->getId() . '_' . uniqid() . '.sh';
        file_put_contents($filename, $script->getContent());
        chmod($filename, 0700);
        
        return $filename;
    }

    /**
     * 异步执行脚本
     */
    public function scheduleScript(ShellScript $script, Node $node): ScriptExecution
    {
        if (!$script->isEnabled()) {
            throw new \RuntimeException('脚本已禁用，无法执行');
        }

        // 创建执行记录
        $execution = new ScriptExecution();
        $execution->setScript($script);
        $execution->setNode($node);
        $execution->setStatus(CommandStatus::PENDING);
        
        $this->entityManager->persist($execution);
        $this->entityManager->flush();

        // 发送异步执行消息
        $this->messageBus->dispatch(new ScriptExecutionMessage($execution->getId()));
        
        return $execution;
    }

    /**
     * 查找脚本执行结果
     */
    public function findExecutionById(int $id): ?ScriptExecution
    {
        return $this->scriptExecutionRepository->find($id);
    }

    /**
     * 查找指定节点上的脚本执行记录
     */
    public function findExecutionsByNode(Node $node): array
    {
        return $this->scriptExecutionRepository->findByNode($node);
    }

    /**
     * 查找指定脚本的执行记录
     */
    public function findExecutionsByScript(ShellScript $script): array
    {
        return $this->scriptExecutionRepository->findByScript($script);
    }

    /**
     * 查找指定节点和脚本的执行记录
     */
    public function findExecutionsByNodeAndScript(Node $node, ShellScript $script): array
    {
        return $this->scriptExecutionRepository->findByNodeAndScript($node, $script);
    }
}
