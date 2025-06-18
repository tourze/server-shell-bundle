<?php

namespace ServerShellBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ServerNodeBundle\Entity\Node;
use ServerShellBundle\Enum\CommandStatus;
use ServerShellBundle\Repository\ScriptExecutionRepository;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;

#[ORM\Entity(repositoryClass: ScriptExecutionRepository::class)]
#[ORM\Table(name: 'ims_server_script_execution', options: ['comment' => '脚本执行结果'])]
class ScriptExecution implements \Stringable
{
    use TimestampableAware;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[TrackColumn]
    #[ORM\ManyToOne(targetEntity: Node::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Node $node;

    #[TrackColumn]
    #[ORM\ManyToOne(targetEntity: ShellScript::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ShellScript $script;

    #[ORM\Column(length: 40, nullable: true, enumType: CommandStatus::class, options: ['comment' => '状态'])]
    private ?CommandStatus $status = CommandStatus::PENDING;

    #[TrackColumn]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '执行结果'])]
    private ?string $result = null;

    #[TrackColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '执行时间'])]
    private ?\DateTimeInterface $executedAt = null;

    #[TrackColumn]
    #[ORM\Column(type: Types::FLOAT, nullable: true, options: ['comment' => '执行耗时(秒)'])]
    private ?float $executionTime = null;

    #[TrackColumn]
    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '退出码'])]
    private ?int $exitCode = null;

    #[CreatedByColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '创建人'])]
    private ?string $createdBy = null;

    #[UpdatedByColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '更新人'])]
    private ?string $updatedBy = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNode(): Node
    {
        return $this->node;
    }

    public function setNode(Node $node): static
    {
        $this->node = $node;

        return $this;
    }

    public function getScript(): ShellScript
    {
        return $this->script;
    }

    public function setScript(ShellScript $script): static
    {
        $this->script = $script;

        return $this;
    }

    public function getStatus(): ?CommandStatus
    {
        return $this->status;
    }

    public function setStatus(?CommandStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getResult(): ?string
    {
        return $this->result;
    }

    public function setResult(?string $result): static
    {
        $this->result = $result;

        return $this;
    }

    public function getExecutedAt(): ?\DateTimeInterface
    {
        return $this->executedAt;
    }

    public function setExecutedAt(?\DateTimeInterface $executedAt): static
    {
        $this->executedAt = $executedAt;

        return $this;
    }

    public function getExecutionTime(): ?float
    {
        return $this->executionTime;
    }

    public function setExecutionTime(?float $executionTime): static
    {
        $this->executionTime = $executionTime;

        return $this;
    }

    public function getExitCode(): ?int
    {
        return $this->exitCode;
    }

    public function setExitCode(?int $exitCode): static
    {
        $this->exitCode = $exitCode;

        return $this;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?string $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?string $updatedBy): static
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }public function __toString(): string
    {
        return $this->getScript()->getName() . ' - ' . $this->getExecutedAt()?->format('Y-m-d H:i:s');
    }
}
