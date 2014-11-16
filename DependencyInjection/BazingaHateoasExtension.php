<?php

/**
 * This file is part of the HateoasBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Bazinga\Bundle\HateoasBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class BazingaHateoasExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $configFiles = array('serializer', 'configuration', 'generator', 'helper', 'twig');
        if ($useJmsFileLocator = $config['metadata']['directories']['use_jms_file_locator']) {
            $configFiles[] = 'file_locator_default';
        } else {
            $configFiles[] = 'file_locator_custom';
        }

        foreach ($configFiles as $file) {
            $loader->load($file . '.xml');
        }

        if (!$useJmsFileLocator) {
            $this->setFileLocatorMetadataFolder($config['metadata']['directories'], $container);
        }

        // Based on JMSSerializerBundle
        if ('none' === $config['metadata']['cache']) {
            $container->removeAlias('hateoas.configuration.metadata.cache');
        } elseif ('file' === $config['metadata']['cache']) {
            $container
                ->getDefinition('hateoas.configuration.metadata.cache.file_cache')
                ->replaceArgument(0, $config['metadata']['file_cache']['dir']);

            $dir = $container->getParameterBag()->resolveValue($config['metadata']['file_cache']['dir']);

            if (!file_exists($dir)) {
                if (!$rs = @mkdir($dir, 0777, true)) {
                    throw new \RuntimeException(sprintf('Could not create cache directory "%s".', $dir));
                }
            }
        } else {
            $container->setAlias(
                'hateoas.configuration.metadata.cache',
                new Alias($config['metadata']['cache'], false)
            );
        }

        $container
            ->getDefinition('hateoas.event_subscriber.json')
            ->replaceArgument(0, new Reference($config['serializer']['json']));

        $container
            ->getDefinition('hateoas.event_subscriber.xml')
            ->replaceArgument(0, new Reference($config['serializer']['xml']));
    }

    /**
     * Configures the FileLocator according to the bundle configuration
     *
     * Inspired from JMSSerializerBundle
     *
     * @param array $metadataDirConfig
     * @param ContainerBuilder $container
     */
    private function setFileLocatorMetadataFolder(array $metadataDirConfig, ContainerBuilder $container)
    {
        $locatorDefinition = $container->getDefinition('hateoas.configuration.metadata.file_locator');

        $directories = array();
        $bundles = $container->getParameter('kernel.bundles');

        if ($metadataDirConfig['all_bundles']) {
            foreach ($bundles as $name => $class) {
                $ref = new \ReflectionClass($class);

                $directories[$ref->getNamespaceName()] = dirname($ref->getFileName()) . '/Resources/config/hateoas';
            }
        } else {
            foreach ($metadataDirConfig['bundles'] as $bundleName) {
                if ($class = $bundles[$bundleName]) {
                    $ref = new \ReflectionClass($class);

                    $directories[$ref->getNamespaceName()] = dirname($ref->getFileName()) . '/Resources/config/hateoas';
                }
            }
        }

        $locatorDefinition->replaceArgument(0, $directories);
    }
}
