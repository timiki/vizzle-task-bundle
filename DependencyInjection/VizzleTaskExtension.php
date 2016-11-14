<?php

namespace Vizzle\TaskBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Vizzle\TaskBundle\Mapper\TaskMapper;
use Vizzle\TaskBundle\Manager\TaskManager;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class VizzleTaskExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->setMapper($container);
        $this->setManager($container);
    }

    /**
     * Task mapping.
     *
     * @param ContainerBuilder $container
     */
    public function setMapper(ContainerBuilder $container)
    {
        $mapper = new Definition(TaskMapper::class);
        $mapper->addMethodCall('setContainer', [new Reference('service_container')]);

        foreach ($container->getParameter('kernel.bundles') as $bundle => $class) {
            $mapper->addMethodCall('addPath', ['@' . $bundle . '/Task']);
        }

        $container->setDefinition('vizzle.task.mapper', $mapper);
    }

    /**
     * Task manager.
     *
     * @param ContainerBuilder $container
     */
    public function setManager(ContainerBuilder $container)
    {
        $manager = new Definition(TaskManager::class);

        $manager->addMethodCall('setContainer', [new Reference('service_container')]);
        $manager->addMethodCall('setMapper', [new Reference('vizzle.task.mapper')]);

        $container->setDefinition('vizzle.task.manager', $manager);
    }
}
