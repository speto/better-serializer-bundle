<?php
declare(strict_types=1);

/*
 * @author Martin Fris <rasta@lj.sk>
 */

namespace BetterSerializerBundle\DependencyInjection;

use BetterSerializer\Cache\Config\ApcuConfig;
use BetterSerializer\Cache\Config\ConfigInterface;
use BetterSerializer\Cache\Config\FileSystemConfig;
use BetterSerializerBundle\BetterSerializerBundle;
use BetterSerializerBundle\Config\Cache;
// @codingStandardsIgnoreStart
use BetterSerializer\DataBind\MetaData\Type\Factory\Chain\{
    ExtensionMember as TypeFactoryExtensionMember,
    ExtensionCollectionMember as CollectionFactoryExtensionMember
};
use BetterSerializer\DataBind\Reader\Processor\Factory\TypeChain\{
    ExtensionMember as TypeReaderFactoryExtensionMember,
    ExtensionCollectionMember as CollectionReaderFactoryExtensionMember
};
use BetterSerializer\DataBind\Writer\Processor\Factory\TypeChain\{
    ExtensionMember as TypeWriterFactoryExtensionMember,
    ExtensionCollectionMember as CollectionWriterFactoryExtensionMember
};
// @codingStandardsIgnoreEnd
use BetterSerializerBundle\Tests\Helper\BooleanStringExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurationTest extends TestCase
{

    /**
     * @param array $configs
     * @return ContainerBuilder
     * @throws \LogicException
     */
    private function getContainer(array $configs = []): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', true);
        $container->setParameter('kernel.cache_dir', sys_get_temp_dir() . '/better_serializer');
        $container->setParameter('kernel.bundles', ['BetterSerializerBundle' => BetterSerializerBundle::class]);

        $bundle = new BetterSerializerBundle();
        $extension = $bundle->getContainerExtension();
        $extension->load($configs, $container);

        return $container;
    }

    /**
     * @throws \LogicException
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     */
    public function testConfigApcuCache()
    {
        $container = $this->getContainer([
            [
                'cache' => Cache::APCU,
            ],
        ]);

        $cacheConfig = $container->getAlias(ConfigInterface::class);

        $this->assertEquals((string) $cacheConfig, ApcuConfig::class);
    }

    /**
     * @throws \LogicException
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     */
    public function testConfigFilesystemCache()
    {
        $container = $this->getContainer([
            [
                'cache' => Cache::FILESYSTEM,
            ],
        ]);

        $cacheConfig = $container->getAlias(ConfigInterface::class);

        $this->assertEquals((string) $cacheConfig, FileSystemConfig::class);
    }

    /**
     * @throws \LogicException
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \Symfony\Component\DependencyInjection\Exception\OutOfBoundsException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     */
    public function testConfigExtensions()
    {
        $container = $this->getContainer([
            [
                'cache' => Cache::APCU,
                'extensions' => [
                    BooleanStringExtension::class
                ]
            ],
        ]);

        $typeFactoryMemberT = $container->getDefinition(TypeFactoryExtensionMember::class);
        $this->assertGreaterThanOrEqual(
            0,
            array_search(BooleanStringExtension::class, $typeFactoryMemberT->getArgument(2), true)
        );

        $rdProcFactoryMemberT = $container->getDefinition(TypeReaderFactoryExtensionMember::class);
        $this->assertGreaterThanOrEqual(
            0,
            array_search(BooleanStringExtension::class, $rdProcFactoryMemberT->getArgument(0), true)
        );

        $wrProcFactoryMemberT = $container->getDefinition(TypeWriterFactoryExtensionMember::class);
        $this->assertGreaterThanOrEqual(
            0,
            array_search(BooleanStringExtension::class, $wrProcFactoryMemberT->getArgument(0), true)
        );

        $typeFactoryMemberC = $container->getDefinition(CollectionFactoryExtensionMember::class);
        $this->assertGreaterThanOrEqual(
            0,
            array_search(BooleanStringExtension::class, $typeFactoryMemberC->getArgument(1), true)
        );

        $rdProcFactoryMemberC = $container->getDefinition(CollectionReaderFactoryExtensionMember::class);
        $this->assertGreaterThanOrEqual(
            0,
            array_search(BooleanStringExtension::class, $rdProcFactoryMemberC->getArgument(1), true)
        );

        $wrProcFactoryMemberC = $container->getDefinition(CollectionWriterFactoryExtensionMember::class);
        $this->assertGreaterThanOrEqual(
            0,
            array_search(BooleanStringExtension::class, $wrProcFactoryMemberC->getArgument(1), true)
        );
    }
}
