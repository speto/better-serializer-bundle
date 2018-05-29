<?php
declare(strict_types=1);

/*
 * @author mfris
 */

namespace BetterSerializerBundle\DependencyInjection;

use BetterSerializer\Cache\Config\ApcuConfig;
use BetterSerializer\Cache\Config\ConfigInterface;
use BetterSerializer\Cache\Config\FileSystemConfig;
use BetterSerializer\Common\CollectionExtensionInterface;
use BetterSerializer\Common\NamingStrategy;
use BetterSerializer\Common\TypeExtensionInterface;
// @codingStandardsIgnoreStart
use BetterSerializer\DataBind\MetaData\Type\Factory\Chain\{
    ExtensionMember as TypeFactoryExtensionMember,
    ExtensionCollectionMember as CollectionFactoryExtensionMember
};
use BetterSerializer\DataBind\Naming\PropertyNameTranslator\CamelCaseTranslator;
use BetterSerializer\DataBind\Naming\PropertyNameTranslator\IdenticalTranslator;
use BetterSerializer\DataBind\Naming\PropertyNameTranslator\SnakeCaseTranslator;
use BetterSerializer\DataBind\Reader\Processor\Factory\TypeChain\{
    ExtensionMember as TypeReaderFactoryExtensionMember,
    ExtensionCollectionMember as CollectionReaderFactoryExtensionMember
};
use BetterSerializer\DataBind\Writer\Processor\Factory\TypeChain\{
    ExtensionMember as TypeWriterFactoryExtensionMember,
    ExtensionCollectionMember as CollectionWriterFactoryExtensionMember
};
// @codingStandardsIgnoreEnd
use BetterSerializer\Extension\DoctrineCollection;
use BetterSerializer\Extension\Registry\RegistryInterface;
use BetterSerializerBundle\Config\Cache;
use BetterSerializerBundle\Config\ContainerService;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\DependencyInjection\Loader;
use Exception;
use UnexpectedValueException;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
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

        $this->applyCache($mergedConfig, $container);
        $this->applyExtensions($mergedConfig, $container);
        $this->applyNamingStrategy($mergedConfig, $container);
    }

    /**
     * @param array $mergedConfig
     * @param ContainerBuilder $container
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    private function applyCache(array $mergedConfig, ContainerBuilder $container): void
    {
        if ($mergedConfig['cache'] === Cache::APCU) {
            $container->setAlias(ConfigInterface::class, new Alias(ApcuConfig::class, false));
        } elseif ($mergedConfig['cache'] === Cache::FILESYSTEM) {
            $container->setAlias(ConfigInterface::class, new Alias(FileSystemConfig::class, false));
        }
    }

    /**
     * @param array $mergedConfig
     * @param ContainerBuilder $container
     * @throws Exception
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     */
    private function applyExtensions(array $mergedConfig, ContainerBuilder $container): void
    {
        $extClasses = [
            DoctrineCollection::class,
        ];

        if (isset($mergedConfig['extensions']) && !empty($mergedConfig['extensions'])) {
            $extClasses = array_merge($extClasses, $mergedConfig['extensions']);
        }

        $registry = $container->get(RegistryInterface::class);

        foreach ($extClasses as $extClass) {
            $registry->registerExtension($extClass);
        }

        $typeCollections = $registry->getTypeCollections();

        $typeExtensions = array_values($typeCollections[TypeExtensionInterface::class]->toArray());

        $typeFactoryMemberT = $container->getDefinition(TypeFactoryExtensionMember::class);
        $typeFactoryMemberT->setArgument(2, $typeExtensions);

        $rdProcFactoryMemberT = $container->getDefinition(TypeReaderFactoryExtensionMember::class);
        $rdProcFactoryMemberT->setArgument(0, $typeExtensions);

        $wrProcFactoryMemberT = $container->getDefinition(TypeWriterFactoryExtensionMember::class);
        $wrProcFactoryMemberT->setArgument(0, $typeExtensions);

        $collectionExtensions = array_values($typeCollections[CollectionExtensionInterface::class]->toArray());

        $typeFactoryMemberC = $container->getDefinition(CollectionFactoryExtensionMember::class);
        $typeFactoryMemberC->setArgument(1, $collectionExtensions);

        $rdProcFactoryMemberC = $container->getDefinition(CollectionReaderFactoryExtensionMember::class);
        $rdProcFactoryMemberC->setArgument(1, $collectionExtensions);

        $wrProcFactoryMemberC = $container->getDefinition(CollectionWriterFactoryExtensionMember::class);
        $wrProcFactoryMemberC->setArgument(1, $collectionExtensions);
    }

    /**
     * @param array $mergedConfig
     * @param ContainerBuilder $container
     * @throws UnexpectedValueException
     * @throws \LogicException
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function applyNamingStrategy(array $mergedConfig, ContainerBuilder $container): void
    {
        $namingStrategy = $mergedConfig['namingStrategy'];

        if (!NamingStrategy::has($namingStrategy)) {
            throw new UnexpectedValueException(sprintf('Invalid naming strategy: %s', $namingStrategy));
        }

        $translatorClass = IdenticalTranslator::class;

        if ($namingStrategy === NamingStrategy::CAMEL_CASE) {
            $translatorClass = CamelCaseTranslator::class;
        } elseif ($namingStrategy === NamingStrategy::SNAKE_CASE) {
            $translatorClass = SnakeCaseTranslator::class;
        }

        $container->setAlias(ContainerService::NAMING_STRATEGY_TRANSLATOR, $translatorClass);
    }
}
