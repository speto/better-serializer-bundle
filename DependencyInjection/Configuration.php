<?php
declare(strict_types=1);

/*
 * @author mfris
 */

namespace BetterSerializerBundle\DependencyInjection;

use BetterSerializerBundle\Config\Cache;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @codingStandardsIgnoreStart
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{

    /**
     * @return TreeBuilder
     * @throws \RuntimeException
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('better_serializer');

        $rootNode->children()
                ->scalarNode('cache')
                    ->defaultValue(Cache::APCU)
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
/**
 * @codingStandardsIgnoreEnd
 */
