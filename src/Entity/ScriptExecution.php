<?php

namespace ServerShellBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ServerNodeBundle\Entity\Node;
use ServerShellBundle\Enum\CommandStatus;
use ServerShellBundle\Repository\ScriptExecutionRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

#[ORM\Entity(repositoryClass: ScriptExecutionRepository::class)]
#[ORM\Table(name: 'ims_server_script_execution', options: ['comment' => '脚本执行结果'])]
class ScriptExecution implements \Stringable
{
    use TimestampableAware;
    use BlameableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private int $id = 0;

    #[ORM\ManyToOne(targetEntity: Node::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Node $node;

    #[ORM\ManyToOne(targetEntity: ShellScript::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ShellScript $script;

    #[Assert\Choice(callback: [CommandStatus::class, 'cases'])]
    #[ORM\Column(length: 40, nullable: true, enumType: CommandStatus::class, options: ['comment' => '状态'])]
    private ?CommandStatus $status = CommandStatus::PENDING;

    #[Assert\Length(max: 65535)]
    #[TrackColumn]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '执行结果'])]
    private ?string $result = null;

    #[Assert\Type(type: \DateTimeInterface::class)]
    #[TrackColumn]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '执行时间'])]
    private ?\DateTimeInterface $executedAt = null;

    #[Assert\PositiveOrZero]
    #[TrackColumn]
    #[ORM\Column(type: Types::FLOAT, nullable: true, options: ['comment' => '执行耗时(秒)'])]
    private ?float $executionTime = null;

    #[Assert\Range(min: 0, max: 255)]
    #[TrackColumn]
    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '退出码'])]
    private ?int $exitCode = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getNode(): Node
    {
        return $this->node;
    }

    public function setNode(Node $node): void
    {
        $this->node = $node;
    }

    public function getScript(): ShellScript
    {
        return $this->script;
    }

    public function setScript(ShellScript $script): void
    {
        $this->script = $script;
    }

    public function getStatus(): ?CommandStatus
    {
        return $this->status;
    }

    public function setStatus(?CommandStatus $status): void
    {
        $this->status = $status;
    }

    public function getResult(): ?string
    {
        return $this->result;
    }

    public function setResult(?string $result): void
    {
        $this->result = $result;
    }

    public function getExecutedAt(): ?\DateTimeInterface
    {
        return $this->executedAt;
    }

    public function setExecutedAt(?\DateTimeInterface $executedAt): void
    {
        $this->executedAt = $executedAt;
    }

    public function getExecutionTime(): ?float
    {
        return $this->executionTime;
    }

    public function setExecutionTime(?float $executionTime): void
    {
        $this->executionTime = $executionTime;
    }

    public function getExitCode(): ?int
    {
        return $this->exitCode;
    }

    public function setExitCode(?int $exitCode): void
    {
        $this->exitCode = $exitCode;
    }

    public function __toString(): string
    {
        return $this->getScript()->getName() . ' - ' . $this->getExecutedAt()?->format('Y-m-d H:i:s');
    }
}
