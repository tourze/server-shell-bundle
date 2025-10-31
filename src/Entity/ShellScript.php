<?php

namespace ServerShellBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ServerShellBundle\Repository\ShellScriptRepository;
use Symfony\Component\Validator\Constraints as Assert;
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
    private int $id = 0;

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[TrackColumn]
    #[ORM\Column(length: 100, options: ['comment' => '脚本名称'])]
    private string $name;

    #[Assert\NotBlank]
    #[Assert\Length(max: 65535)]
    #[TrackColumn]
    #[ORM\Column(type: Types::TEXT, options: ['comment' => '脚本内容'])]
    private string $content;

    #[Assert\Length(max: 200)]
    #[TrackColumn]
    #[ORM\Column(length: 200, nullable: true, options: ['comment' => '执行目录'])]
    private ?string $workingDirectory = null;

    #[Assert\Type(type: 'bool')]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '是否使用sudo执行', 'default' => false])]
    private ?bool $useSudo = false;

    #[Assert\Positive]
    #[TrackColumn]
    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '超时时间(秒)', 'default' => 300])]
    private ?int $timeout = 300;

    #[Assert\Type(type: 'bool')]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '是否启用', 'default' => true])]
    private ?bool $enabled = true;

    /**
     * @var array<string>|null
     */
    #[Assert\Type(type: 'array')]
    #[TrackColumn]
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '标签列表'])]
    private ?array $tags = null;

    #[Assert\Length(max: 65535)]
    #[TrackColumn]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '描述'])]
    private ?string $description = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getWorkingDirectory(): ?string
    {
        return $this->workingDirectory;
    }

    public function setWorkingDirectory(?string $workingDirectory): void
    {
        $this->workingDirectory = $workingDirectory;
    }

    public function isUseSudo(): ?bool
    {
        return $this->useSudo;
    }

    public function setUseSudo(?bool $useSudo): void
    {
        $this->useSudo = $useSudo;
    }

    public function getTimeout(): ?int
    {
        return $this->timeout;
    }

    public function setTimeout(?int $timeout): void
    {
        $this->timeout = $timeout;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(?bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /** @return array<string>|null */
    public function getTags(): ?array
    {
        return $this->tags;
    }

    /** @param array<string>|null $tags */
    public function setTags(?array $tags): void
    {
        $this->tags = $tags;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
