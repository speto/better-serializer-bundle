<?php
declare(strict_types=1);

/*
 * @author Martin Fris <rasta@lj.sk>
 */

namespace BetterSerializerBundle\DependencyInjection;

use BetterSerializer\Cache\Config\ApcuConfig;
use BetterSerializer\Cache\Config\ConfigInterface;
use BetterSerializer\Cache\Config\FileSystemConfig;
use BetterSerializer\Common\NamingStrategy;
use BetterSerializer\DataBind\Naming\PropertyNameTranslator\CamelCaseTranslator;
use BetterSerializer\DataBind\Naming\PropertyNameTranslator\IdenticalTranslator;
use BetterSerializer\DataBind\Naming\PropertyNameTranslator\SnakeCaseTranslator;
use BetterSerializer\DataBind\Naming\PropertyNameTranslator\TranslatorInterface;
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
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurationTest extends TestCase
{

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @param array $configs
     * @return ContainerBuilder
     * @throws \LogicException
     */
    private function getContainer(array $configs = []): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', true);
        $this->cacheDir = sys_get_temp_dir() . '/better_serializer';
        $container->setParameter('kernel.cache_dir', $this->cacheDir);
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

    /**
     * @param string $namingStrategy
     * @param string $expTranslatorClass
     * @throws \LogicException
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @dataProvider namingStrategyDataProvider
     */
    public function testConfigNamingStrategy(string $namingStrategy, string $expTranslatorClass): void
    {
        $container = $this->getContainer([
            [
                'cache' => Cache::APCU,
                'namingStrategy' => $namingStrategy,
            ],
        ]);

        $translatorClass = $container->getDefinition(TranslatorInterface::class);

        $this->assertEquals($expTranslatorClass, $translatorClass->getClass());
    }

    /**
     * @return array
     */
    public function namingStrategyDataProvider(): array
    {
        return [
            [NamingStrategy::SNAKE_CASE, SnakeCaseTranslator::class],
            [NamingStrategy::CAMEL_CASE, CamelCaseTranslator::class],
            [NamingStrategy::IDENTITY, IdenticalTranslator::class],
        ];
    }

    /**
     * @throws \LogicException
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     */
    public function testDefaultConfigNamingStrategy(): void
    {
        $container = $this->getContainer([
            [
                'cache' => Cache::APCU,
            ],
        ]);

        $translatorClass = $container->getDefinition(TranslatorInterface::class);

        $this->assertEquals(IdenticalTranslator::class, $translatorClass->getClass());
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessageRegExp /Invalid naming strategy: .+/
     * @throws \LogicException
     */
    public function testUnknownConfigNamingStrategy(): void
    {
        $this->getContainer([
            [
                'cache' => Cache::APCU,
                'namingStrategy' => 'abcd',
            ],
        ]);
    }

    /**
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     */
    public function __destruct()
    {
        $fileSystem = new Filesystem();
        $fileSystem->remove($this->cacheDir);
    }
}
