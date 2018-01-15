<?php
declare(strict_types=1);

/*
 * @author mfris
 */

namespace BetterSerializerBundle\DependencyInjection;

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
        $treeBuilder->root('better_serializer');

        return $treeBuilder;
    }
}
/**
 * @codingStandardsIgnoreEnd
 */
