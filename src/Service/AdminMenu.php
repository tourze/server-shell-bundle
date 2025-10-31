<?php

namespace ServerShellBundle\Service;

use Knp\Menu\ItemInterface;
use ServerShellBundle\Entity\ScriptExecution;
use ServerShellBundle\Entity\ShellScript;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;

#[Autoconfigure(public: true)]
readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(private LinkGeneratorInterface $linkGenerator)
    {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (null === $item->getChild('服务器管理')) {
            $item->addChild('服务器管理');
        }

        $serverMenu = $item->getChild('服务器管理');
        if (null !== $serverMenu) {
            $serverMenu
                ->addChild('Shell脚本')
                ->setUri($this->linkGenerator->getCurdListPage(ShellScript::class))
                ->setAttribute('icon', 'fas fa-code')
            ;
        }

        $serverMenu = $item->getChild('服务器管理');
        if (null !== $serverMenu) {
            $serverMenu
                ->addChild('Shell结果')
                ->setUri($this->linkGenerator->getCurdListPage(ScriptExecution::class))
                ->setAttribute('icon', 'fas fa-list-alt')
            ;
        }
    }
}
