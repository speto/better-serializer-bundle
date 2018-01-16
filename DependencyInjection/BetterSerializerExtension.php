<?php
declare(strict_types=1);

/*
 * @author mfris
 */

namespace BetterSerializerBundle\DependencyInjection;

use BetterSerializer\Cache\Config\ApcuConfig;
use BetterSerializer\Cache\Config\ConfigInterface;
use BetterSerializer\Cache\Config\FileSystemConfig;
use BetterSerializerBundle\Config\Cache;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\DependencyInjection\Loader;
use Exception;

/**
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class BetterSerializerExtension extends ConfigurableExtension
{
    /**
     * @param array            $mergedConfig
     * @param ContainerBuilder $container
     *
     * @throws Exception
     */
    public function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        if ($mergedConfig['cache'] === Cache::APCU) {
            $container->setAlias(ConfigInterface::class, new Alias(ApcuConfig::class, false));
        } elseif ($mergedConfig['cache'] === Cache::FILESYSTEM) {
            $container->setAlias(ConfigInterface::class, new Alias(FileSystemConfig::class, false));
        }
    }
}
