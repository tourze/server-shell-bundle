<?php

namespace ServerShellBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use SebastianBergmann\Timer\Timer;
use ServerCommandBundle\Entity\RemoteCommand;
use ServerCommandBundle\Enum\CommandStatus as RemoteCommandStatus;
use ServerCommandBundle\Service\RemoteCommandService;
use ServerNodeBundle\Entity\Node;
use ServerShellBundle\Entity\ScriptExecution;
use ServerShellBundle\Entity\ShellScript;
use ServerShellBundle\Enum\CommandStatus;
use ServerShellBundle\Exception\ScriptDisabledException;
use ServerShellBundle\Exception\ScriptUploadException;
use ServerShellBundle\Message\ScriptExecutionMessage;
use ServerShellBundle\Repository\ScriptExecutionRepository;
use ServerShellBundle\Repository\ShellScriptRepository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Messenger\MessageBusInterface;

#[WithMonologChannel(channel: 'server_shell')]
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
    /**
     * @param array<string>|null $tags
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
    /**
     * @param array<string>|null $tags
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
        if (null !== $name) {
            $script->setName($name);
        }
        if (null !== $content) {
            $script->setContent($content);
        }
        if (null !== $workingDirectory) {
            $script->setWorkingDirectory($workingDirectory);
        }
        if (null !== $useSudo) {
            $script->setUseSudo($useSudo);
        }
        if (null !== $timeout) {
            $script->setTimeout($timeout);
        }
        if (null !== $tags) {
            $script->setTags($tags);
        }
        if (null !== $description) {
            $script->setDescription($description);
        }
        if (null !== $enabled) {
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
    /** @return array<ShellScript> */
    public function findAllEnabledScripts(): array
    {
        return $this->shellScriptRepository->findAllEnabled();
    }

    /**
     * 按标签查找脚本
     */
    /** @param array<string> $tags
     * @return array<ShellScript>
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
        if (!($script->isEnabled() ?? false)) {
            throw new ScriptDisabledException('脚本已禁用，无法执行');
        }

        // 创建执行记录
        $execution = $this->createExecutionRecord($script, $node);

        try {
            // 标记为正在执行
            $this->markExecutionAsRunning($execution);

            // 执行脚本
            $timer = new Timer();
            $timer->start();

            // 创建临时脚本文件
            $localScriptPath = $this->createTempScriptFile($script);

            // 上传脚本到远程节点
            $remoteScriptPath = $this->uploadScriptToRemoteNode($script, $node, $localScriptPath);

            // 执行远程脚本
            $execResult = $this->executeRemoteScript($script, $node, $remoteScriptPath);

            // 清理脚本文件
            $this->cleanupScripts($node, $localScriptPath, $remoteScriptPath);

            $executionTime = $timer->stop()->asSeconds();

            // 更新执行结果
            $this->updateExecutionResult($execution, $execResult, $executionTime);

            return $execution;
        } catch (\Throwable $e) {
            $this->handleExecutionError($execution, $e);

            return $execution;
        }
    }

    /**
     * 创建脚本执行记录
     */
    private function createExecutionRecord(ShellScript $script, Node $node): ScriptExecution
    {
        $execution = new ScriptExecution();
        $execution->setScript($script);
        $execution->setNode($node);
        $execution->setStatus(CommandStatus::PENDING);

        $this->entityManager->persist($execution);
        $this->entityManager->flush();

        return $execution;
    }

    /**
     * 将执行状态标记为运行中
     */
    private function markExecutionAsRunning(ScriptExecution $execution): void
    {
        $execution->setStatus(CommandStatus::RUNNING);
        $execution->setExecutedAt(new \DateTime());
        $this->entityManager->flush();
    }

    /**
     * 上传脚本到远程节点
     */
    private function uploadScriptToRemoteNode(ShellScript $script, Node $node, string $localScriptPath): string
    {
        $remoteScriptPath = '/tmp/' . basename($localScriptPath);
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
        if (RemoteCommandStatus::COMPLETED !== $uploadCommand->getStatus()) {
            throw new ScriptUploadException('脚本上传失败: ' . $uploadResult->getResult());
        }

        return $remoteScriptPath;
    }

    /**
     * 执行远程脚本
     */
    private function executeRemoteScript(ShellScript $script, Node $node, string $remoteScriptPath): RemoteCommand
    {
        $workingDir = $script->getWorkingDirectory() ?? '/tmp';
        $execCommand = $this->remoteCommandService->createCommand(
            $node,
            '执行脚本: ' . $script->getName(),
            $remoteScriptPath,
            $workingDir,
            $script->isUseSudo(),
            $script->getTimeout()
        );

        // 执行脚本
        return $this->remoteCommandService->executeCommand($execCommand);
    }

    /**
     * 清理本地和远程脚本文件
     */
    private function cleanupScripts(Node $node, string $localScriptPath, string $remoteScriptPath): void
    {
        // 清理远程脚本
        $cleanupCommand = $this->remoteCommandService->createCommand(
            $node,
            '清理脚本文件',
            "rm -f {$remoteScriptPath}",
            '/tmp',
            false,
            30
        );
        $this->remoteCommandService->executeCommand($cleanupCommand);

        // 本地清理
        if (file_exists($localScriptPath)) {
            unlink($localScriptPath);
        }
    }

    /**
     * 更新执行结果
     */
    private function updateExecutionResult(ScriptExecution $execution, RemoteCommand $execResult, float $executionTime): void
    {
        $execution->setResult($execResult->getResult());
        $execution->setStatus($this->mapRemoteCommandStatus($execResult->getStatus()));
        $execution->setExecutionTime($executionTime);
        $execution->setExitCode(RemoteCommandStatus::COMPLETED === $execResult->getStatus() ? 0 : 1);

        $this->entityManager->flush();
    }

    /**
     * 将远程命令状态映射为脚本执行状态
     */
    private function mapRemoteCommandStatus(?RemoteCommandStatus $status): ?CommandStatus
    {
        return match ($status) {
            RemoteCommandStatus::PENDING => CommandStatus::PENDING,
            RemoteCommandStatus::RUNNING => CommandStatus::RUNNING,
            RemoteCommandStatus::COMPLETED => CommandStatus::COMPLETED,
            RemoteCommandStatus::FAILED => CommandStatus::FAILED,
            RemoteCommandStatus::TIMEOUT => CommandStatus::TIMEOUT,
            RemoteCommandStatus::CANCELED => CommandStatus::CANCELED,
            null => null,
        };
    }

    /**
     * 处理执行过程中的错误
     */
    private function handleExecutionError(ScriptExecution $execution, \Throwable $e): void
    {
        $this->logger->error('执行脚本时出错: ' . $e->getMessage(), [
            'script_id' => $execution->getScript()->getId(),
            'node_id' => $execution->getNode()->getId(),
            'exception' => $e,
        ]);

        $execution->setStatus(CommandStatus::FAILED);
        $execution->setResult('执行出错: ' . $e->getMessage());
        $this->entityManager->flush();
    }

    /**
     * 创建临时脚本文件
     */
    private function createTempScriptFile(ShellScript $script): string
    {
        $fs = new Filesystem();

        // 确保临时目录存在
        if (!$fs->exists(self::TEMP_SCRIPT_DIR)) {
            $fs->mkdir(self::TEMP_SCRIPT_DIR, 0o700);
        }

        // 创建临时脚本文件
        $filename = self::TEMP_SCRIPT_DIR . '/script_' . $script->getId() . '_' . uniqid() . '.sh';
        file_put_contents($filename, $script->getContent());
        chmod($filename, 0o700);

        return $filename;
    }

    /**
     * 异步执行脚本
     */
    public function scheduleScript(ShellScript $script, Node $node): ScriptExecution
    {
        if (!($script->isEnabled() ?? false)) {
            throw new ScriptDisabledException('脚本已禁用，无法执行');
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
    /** @return array<ScriptExecution> */
    public function findExecutionsByNode(Node $node): array
    {
        return $this->scriptExecutionRepository->findByNode($node);
    }

    /**
     * 查找指定脚本的执行记录
     */
    /** @return array<ScriptExecution> */
    public function findExecutionsByScript(ShellScript $script): array
    {
        return $this->scriptExecutionRepository->findByScript($script);
    }

    /**
     * 查找指定节点和脚本的执行记录
     */
    /** @return array<ScriptExecution> */
    public function findExecutionsByNodeAndScript(Node $node, ShellScript $script): array
    {
        return $this->scriptExecutionRepository->findByNodeAndScript($node, $script);
    }
}
