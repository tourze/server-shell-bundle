<?php

namespace ServerShellBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use ServerShellBundle\Enum\CommandStatus;
use ServerShellBundle\Message\ScriptExecutionMessage;
use ServerShellBundle\Repository\ScriptExecutionRepository;
use ServerShellBundle\Service\ShellScriptService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ScriptExecutionMessageHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ScriptExecutionRepository $scriptExecutionRepository,
        private readonly ShellScriptService $shellScriptService,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(ScriptExecutionMessage $message): void
    {
        $executionId = $message->getExecutionId();
        $execution = $this->scriptExecutionRepository->find($executionId);

        if ($execution === null) {
            $this->logger->error('找不到脚本执行记录', ['execution_id' => $executionId]);
            return;
        }

        if ($execution->getStatus() !== CommandStatus::PENDING) {
            $this->logger->warning('脚本执行状态不是待处理', [
                'execution_id' => $executionId,
                'status' => $execution->getStatus()->value,
            ]);
            return;
        }

        try {
            $script = $execution->getScript();
            $node = $execution->getNode();

            // 使用服务执行脚本，但跳过创建执行记录的步骤
            $this->shellScriptService->executeScript($script, $node);
        } catch  (\Throwable $e) {
            $this->logger->error('异步执行脚本时出错', [
                'execution_id' => $executionId,
                'exception' => $e,
            ]);

            // 更新执行状态为失败
            $execution->setStatus(CommandStatus::FAILED);
            $execution->setResult('异步执行出错: ' . $e->getMessage());
            $this->entityManager->flush();
        }
    }
}
