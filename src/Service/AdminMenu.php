<?php

namespace ServerShellBundle\Service;

use Knp\Menu\ItemInterface;
use ServerShellBundle\Entity\ScriptExecution;
use ServerShellBundle\Entity\ShellScript;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;

class AdminMenu implements MenuProviderInterface
{
    public function __construct(private readonly LinkGeneratorInterface $linkGenerator)
    {
    }

    public function __invoke(ItemInterface $item): void
    {
        if ($item->getChild('服务器管理') === null) {
            $item->addChild('服务器管理');
        }

        $item->getChild('服务器管理')
            ->addChild('Shell脚本')
            ->setUri($this->linkGenerator->getCurdListPage(ShellScript::class))
            ->setAttribute('icon', 'fas fa-code');

        $item->getChild('服务器管理')
            ->addChild('Shell结果')
            ->setUri($this->linkGenerator->getCurdListPage(ScriptExecution::class))
            ->setAttribute('icon', 'fas fa-list-alt');
    }
}
