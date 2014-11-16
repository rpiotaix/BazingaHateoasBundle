<?php

/**
 * This file is part of the HateoasBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Bazinga\Bundle\HateoasBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('bazinga_hateoas');

        $rootNode
            ->children()
            ->arrayNode('metadata')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('directories')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->booleanNode('use_jms_file_locator')
                                ->info("Use the FileLocator provided by the JMSSerializerBundle to find the metadata files. This is the default behavior. If true, the other settings of this node are ignored.")
                                ->defaultTrue()
                            ->end()
                            ->booleanNode('all_bundles')
                                ->info("If true, the metadata files are expected to be in the directory Resources/config/hateoas of each Bundle.")
                                ->defaultFalse()
                            ->end()
                            ->arrayNode('bundles')
                                ->info('The bundles in which to search for metadata')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                    ->scalarNode('cache')->defaultValue('file')->end()
                    ->arrayNode('file_cache')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('dir')->defaultValue('%kernel.cache_dir%/hateoas')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('serializer')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('json')->defaultValue('hateoas.serializer.json_hal')->end()
                    ->scalarNode('xml')->defaultValue('hateoas.serializer.xml')->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
