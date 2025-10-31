<?php

namespace ServerShellBundle;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use ServerCommandBundle\ServerCommandBundle;
use ServerNodeBundle\ServerNodeBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;

class ServerShellBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            DoctrineBundle::class => ['all' => true],
            ServerNodeBundle::class => ['all' => true],
            ServerCommandBundle::class => ['all' => true],
        ];
    }
}
