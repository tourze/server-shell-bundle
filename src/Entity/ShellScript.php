<?php

namespace ServerShellBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ServerShellBundle\Repository\ShellScriptRepository;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

#[ORM\Entity(repositoryClass: ShellScriptRepository::class)]
#[ORM\Table(name: 'ims_server_shell_script', options: ['comment' => 'Shell脚本'])]
class ShellScript implements \Stringable
{
    use TimestampableAware;
    use BlameableAware;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[TrackColumn]
    #[ORM\Column(length: 100, options: ['comment' => '脚本名称'])]
    private string $name;

    #[TrackColumn]
    #[ORM\Column(type: Types::TEXT, options: ['comment' => '脚本内容'])]
    private string $content;

    #[TrackColumn]
    #[ORM\Column(length: 200, nullable: true, options: ['comment' => '执行目录'])]
    private ?string $workingDirectory = null;

    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '是否使用sudo执行', 'default' => false])]
    private ?bool $useSudo = false;

    #[TrackColumn]
    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '超时时间(秒)', 'default' => 300])]
    private ?int $timeout = 300;

    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '是否启用', 'default' => true])]
    private ?bool $enabled = true;

    #[TrackColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '标签列表'])]
    private ?array $tags = null;

    #[TrackColumn]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '描述'])]
    private ?string $description = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getWorkingDirectory(): ?string
    {
        return $this->workingDirectory;
    }

    public function setWorkingDirectory(?string $workingDirectory): static
    {
        $this->workingDirectory = $workingDirectory;

        return $this;
    }

    public function isUseSudo(): ?bool
    {
        return $this->useSudo;
    }

    public function setUseSudo(?bool $useSudo): static
    {
        $this->useSudo = $useSudo;

        return $this;
    }

    public function getTimeout(): ?int
    {
        return $this->timeout;
    }

    public function setTimeout(?int $timeout): static
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(?bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getTags(): ?array
    {
        return $this->tags;
    }

    public function setTags(?array $tags): static
    {
        $this->tags = $tags;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
