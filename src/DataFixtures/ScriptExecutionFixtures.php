<?php

namespace ServerShellBundle\DataFixtures;

use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use ServerNodeBundle\Entity\Node;
use ServerShellBundle\Entity\ScriptExecution;
use ServerShellBundle\Entity\ShellScript;
use ServerShellBundle\Enum\CommandStatus;

class ScriptExecutionFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    /**
     * 加载测试数据
     */
    public function load(ObjectManager $manager): void
    {
        // 检查是否有可用的 Node 实体
        $nodeRepository = $manager->getRepository(Node::class);
        $nodes = $nodeRepository->findAll();

        if (empty($nodes)) {
            // 如果没有 Node 实体，创建一个虚拟节点用于测试
            $node = new Node();
            $node->setName('测试服务器');
            $node->setSshHost('localhost');
            $node->setSshPort(22);
            $node->setSshUser('root');
            $node->setSshPassword('password');

            $manager->persist($node);
            $manager->flush();
        } else {
            // 使用第一个可用的节点
            $node = $nodes[0];
        }

        // 1. 系统信息脚本 - 已成功执行
        $this->createScriptExecution(
            $manager,
            $this->getReference(ShellScriptFixtures::SYSTEM_INFO_SCRIPT_REFERENCE, ShellScript::class),
            $node,
            CommandStatus::COMPLETED,
            $this->generateSystemInfoOutput(),
            new DateTime('-2 hours'),
            1.23,
            0
        );

        // 2. 磁盘清理脚本 - 执行失败
        $this->createScriptExecution(
            $manager,
            $this->getReference(ShellScriptFixtures::DISK_CLEANUP_SCRIPT_REFERENCE, ShellScript::class),
            $node,
            CommandStatus::FAILED,
            "清理临时目录时出错: Permission denied\n无法清理 /tmp 目录，请检查权限",
            new DateTime('-1 day'),
            0.45,
            1
        );

        // 3. 服务状态检查脚本 - 执行超时
        $this->createScriptExecution(
            $manager,
            $this->getReference(ShellScriptFixtures::SERVICE_CHECK_SCRIPT_REFERENCE, ShellScript::class),
            $node,
            CommandStatus::TIMEOUT,
            "=== 检查关键服务状态 ===\n时间: Wed Apr 3 10:15:30 CST 2024\n\n检查 nginx 服务状态...\n✅ nginx 服务运行正常\n\n检查 mysql 服务状态...\n执行超时，脚本被终止",
            new DateTime('-3 days'),
            180.0,
            124
        );

        // 4. 正在运行的系统信息脚本
        $this->createScriptExecution(
            $manager,
            $this->getReference(ShellScriptFixtures::SYSTEM_INFO_SCRIPT_REFERENCE, ShellScript::class),
            $node,
            CommandStatus::RUNNING,
            null,
            new DateTime('now'),
            null,
            null
        );

        // 5. 待执行的服务状态检查脚本
        $this->createScriptExecution(
            $manager,
            $this->getReference(ShellScriptFixtures::SERVICE_CHECK_SCRIPT_REFERENCE, ShellScript::class),
            $node,
            CommandStatus::PENDING,
            null,
            null,
            null,
            null
        );
    }

    /**
     * 创建脚本执行记录
     */
    private function createScriptExecution(
        ObjectManager $manager,
        ShellScript   $script,
        Node          $node,
        CommandStatus $status,
        ?string       $result,
        ?DateTime     $executedAt,
        ?float        $executionTime,
        ?int          $exitCode
    ): void
    {
        $execution = new ScriptExecution();
        $execution->setScript($script);
        $execution->setNode($node);
        $execution->setStatus($status);
        $execution->setResult($result);
        $execution->setExecutedAt($executedAt);
        $execution->setExecutionTime($executionTime);
        $execution->setExitCode($exitCode);

        $manager->persist($execution);
        $manager->flush();
    }

    /**
     * 生成系统信息输出示例
     */
    private function generateSystemInfoOutput(): string
    {
        return <<<'EOT'
=== 系统基本信息 ===
Linux server123 5.15.0-88-generic #98-Ubuntu SMP Mon Oct 2 15:18:55 UTC 2023 x86_64 GNU/Linux

=== CPU 信息 ===
Architecture:                    x86_64
CPU op-mode(s):                  32-bit, 64-bit
Byte Order:                      Little Endian
Address sizes:                   46 bits physical, 48 bits virtual
CPU(s):                          8
On-line CPU(s) list:             0-7
Thread(s) per core:              2
Core(s) per socket:              4
Socket(s):                       1
NUMA node(s):                    1
Vendor ID:                       GenuineIntel
CPU family:                      6
Model:                           142
Model name:                      Intel(R) Core(TM) i7-8565U CPU @ 1.80GHz
Stepping:                        11
CPU MHz:                         1992.002
CPU max MHz:                     4600.0000
CPU min MHz:                     400.0000
BogoMIPS:                        3984.00
Virtualization:                  VT-x
L1d cache:                       128 KiB
L1i cache:                       128 KiB
L2 cache:                        1 MiB
L3 cache:                        8 MiB

=== 内存使用情况 ===
               total        used        free      shared  buff/cache   available
Mem:            15Gi       2.6Gi       8.5Gi       338Mi       4.3Gi        12Gi
Swap:          2.0Gi          0B       2.0Gi

=== 磁盘使用情况 ===
Filesystem      Size  Used Avail Use% Mounted on
/dev/sda1       235G   98G  125G  44% /
tmpfs           1.6G     0  1.6G   0% /dev/shm
/dev/sdb1       1.8T  1.2T  521G  71% /data

=== 系统负载 ===
 10:15:30 up 23 days,  5:43,  3 users,  load average: 0.42, 0.58, 0.65

=== 运行时间 ===
system boot  2023-03-11 05:32:12

=== 已登录用户 ===
admin    pts/0        2023-04-03 08:45 (192.168.1.101)
user1    pts/1        2023-04-03 09:30 (192.168.1.105) 
user2    pts/2        2023-04-03 10:05 (192.168.1.110)
EOT;
    }

    /**
     * 定义依赖关系
     */
    public function getDependencies(): array
    {
        return [
            ShellScriptFixtures::class,
        ];
    }

    /**
     * 返回此 Fixture 所属的组
     */
    public static function getGroups(): array
    {
        return ['server_ssh', 'server_ssh_execution'];
    }
}
