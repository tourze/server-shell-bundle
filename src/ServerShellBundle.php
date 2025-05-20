<?php

namespace ServerShellBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;

class ServerShellBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            \ServerNodeBundle\ServerNodeBundle::class => ['all' => true],
            \ServerCommandBundle\ServerCommandBundle::class => ['all' => true],
        ];
    }
}
