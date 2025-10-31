<?php

namespace ServerShellBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use ServerShellBundle\Entity\ShellScript;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[When(env: 'dev')]
class ShellScriptFixtures extends Fixture implements FixtureGroupInterface
{
    /**
     * 系统信息脚本引用名称
     */
    public const SYSTEM_INFO_SCRIPT_REFERENCE = 'system-info-script';

    /**
     * 磁盘清理脚本引用名称
     */
    public const DISK_CLEANUP_SCRIPT_REFERENCE = 'disk-cleanup-script';

    /**
     * 服务状态检查脚本引用名称
     */
    public const SERVICE_CHECK_SCRIPT_REFERENCE = 'service-check-script';

    public function load(ObjectManager $manager): void
    {
        // 1. 系统信息脚本
        $systemInfoScript = new ShellScript();
        $systemInfoScript->setName('系统信息脚本');
        $systemInfoScript->setContent(<<<'EOT'
            #!/bin/bash
            # 系统信息收集脚本

            echo "=== 系统基本信息 ==="
            uname -a
            echo ""

            echo "=== CPU 信息 ==="
            lscpu
            echo ""

            echo "=== 内存使用情况 ==="
            free -h
            echo ""

            echo "=== 磁盘使用情况 ==="
            df -h
            echo ""

            echo "=== 系统负载 ==="
            uptime
            echo ""

            echo "=== 运行时间 ==="
            who -b
            echo ""

            echo "=== 已登录用户 ==="
            who
            echo ""
            EOT
        );
        $systemInfoScript->setWorkingDirectory('/tmp');
        $systemInfoScript->setUseSudo(false);
        $systemInfoScript->setTimeout(120);
        $systemInfoScript->setTags(['system', 'info', 'monitor']);
        $systemInfoScript->setDescription('收集系统基本信息，包括系统版本、CPU、内存、磁盘等状态');
        $systemInfoScript->setEnabled(true);

        $manager->persist($systemInfoScript);
        $this->addReference(self::SYSTEM_INFO_SCRIPT_REFERENCE, $systemInfoScript);

        // 2. 磁盘清理脚本
        $diskCleanupScript = new ShellScript();
        $diskCleanupScript->setName('磁盘清理脚本');
        $diskCleanupScript->setContent(<<<'EOT'
            #!/bin/bash
            # 磁盘空间清理脚本

            echo "=== 开始清理临时目录 ==="
            echo "清理前状态:"
            df -h /tmp
            echo ""

            echo "正在清理超过30天未访问的文件..."
            find /tmp -type f -atime +30 -delete

            echo "清理后状态:"
            df -h /tmp
            echo ""

            echo "=== 开始清理日志目录 ==="
            echo "清理前状态:"
            df -h /var/log
            echo ""

            echo "正在清理超过90天的日志文件..."
            find /var/log -name "*.gz" -type f -mtime +90 -delete
            find /var/log -name "*.log.*" -type f -mtime +90 -delete

            echo "清理后状态:"
            df -h /var/log
            echo ""

            echo "=== 清理完成 ==="
            EOT
        );
        $diskCleanupScript->setWorkingDirectory('/');
        $diskCleanupScript->setUseSudo(true);
        $diskCleanupScript->setTimeout(300);
        $diskCleanupScript->setTags(['system', 'cleanup', 'maintenance']);
        $diskCleanupScript->setDescription('清理系统临时文件和过期日志，释放磁盘空间');
        $diskCleanupScript->setEnabled(true);

        $manager->persist($diskCleanupScript);
        $this->addReference(self::DISK_CLEANUP_SCRIPT_REFERENCE, $diskCleanupScript);

        // 3. 服务状态检查脚本
        $serviceCheckScript = new ShellScript();
        $serviceCheckScript->setName('服务状态检查脚本');
        $serviceCheckScript->setContent(<<<'EOT'
            #!/bin/bash
            # 服务状态检查脚本

            # 定义要检查的服务列表
            SERVICES=("nginx" "mysql" "php-fpm" "redis-server" "memcached")

            echo "=== 检查关键服务状态 ==="
            echo "时间: $(date)"
            echo ""

            for SERVICE in "${SERVICES[@]}"; do
              echo "检查 $SERVICE 服务状态..."
              if systemctl is-active --quiet $SERVICE; then
                echo "✅ $SERVICE 服务运行正常"
              else
                echo "❌ $SERVICE 服务未运行或异常"
                echo "尝试启动 $SERVICE 服务..."
                systemctl start $SERVICE
                
                if systemctl is-active --quiet $SERVICE; then
                  echo "✅ $SERVICE 服务已成功启动"
                else
                  echo "❌ $SERVICE 服务启动失败，请检查日志"
                fi
              fi
              echo ""
            done

            echo "=== 服务状态检查完成 ==="
            EOT
        );
        $serviceCheckScript->setWorkingDirectory('/');
        $serviceCheckScript->setUseSudo(true);
        $serviceCheckScript->setTimeout(180);
        $serviceCheckScript->setTags(['service', 'monitor', 'maintenance']);
        $serviceCheckScript->setDescription('检查关键服务运行状态，并尝试启动未运行的服务');
        $serviceCheckScript->setEnabled(true);

        $manager->persist($serviceCheckScript);
        $this->addReference(self::SERVICE_CHECK_SCRIPT_REFERENCE, $serviceCheckScript);

        // 保存所有实体
        $manager->flush();
    }

    /**
     * 返回此 Fixture 所属的组
     */
    public static function getGroups(): array
    {
        return ['server_ssh', 'server_ssh_script'];
    }
}
