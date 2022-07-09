<?php

namespace Admin;

use Admin\AdminBundleCompilerPass;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AdminBundle extends AbstractBundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new AdminBundleCompilerPass);
    }
}
