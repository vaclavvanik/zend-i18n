<?php
/**
 * @link      http://github.com/zendframework/zend-i18n for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\I18n\Translator;

use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\I18n\Translator\LoaderPluginManager;
use Zend\I18n\Translator\LoaderPluginManagerFactory;
use Zend\I18n\Translator\Loader\FileLoaderInterface;
use Zend\I18n\Translator\Loader\PhpArray;
use Zend\ServiceManager\ServiceLocatorInterface;

class LoaderPluginManagerFactoryTest extends TestCase
{
    public function testFactoryReturnsUnconfiguredPluginManagerWhenNoOptionsPresent()
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();

        $factory = new LoaderPluginManagerFactory();
        $loaders = $factory($container, 'TranslatorPluginManager');
        $this->assertInstanceOf(LoaderPluginManager::class, $loaders);
        $this->assertFalse($loaders->has('test'));
    }

    public function testCreateServiceReturnsUnconfiguredPluginManagerWhenNoOptionsPresent()
    {
        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $factory = new LoaderPluginManagerFactory();
        $loaders = $factory->createService($container->reveal());
        $this->assertInstanceOf(LoaderPluginManager::class, $loaders);
        $this->assertFalse($loaders->has('test'));
    }

    public function testFactoryCanConfigurePluginManagerViaOptions()
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();

        $factory = new LoaderPluginManagerFactory();
        $loaders = $factory($container, 'TranslatorPluginManager', ['aliases' => [
            'test' => 'phparray',
        ]]);
        $this->assertInstanceOf(LoaderPluginManager::class, $loaders);
        $this->assertTrue($loaders->has('test'));
    }

    public function testCreateServiceCanConfigurePluginManagerViaOptions()
    {
        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $factory = new LoaderPluginManagerFactory();
        $factory->setCreationOptions(['aliases' => [
            'test' => 'phparray',
        ]]);
        $loaders = $factory->createService($container->reveal());
        $this->assertInstanceOf(LoaderPluginManager::class, $loaders);
        $this->assertTrue($loaders->has('test'));
    }

    public function testConfiguresTranslatorServicesWhenFound()
    {
        $translator = $this->prophesize(FileLoaderInterface::class)->reveal();
        $config = [
            'translator_plugins' => [
                'aliases' => [
                    'test' => PhpArray::class,
                ],
                'factories' => [
                    'test-too' => function ($container) use ($translator) {
                        return $translator;
                    },
                ],
            ],
        ];

        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $container->has('ServiceListener')->willReturn(false);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn($config);

        $factory = new LoaderPluginManagerFactory();
        $translators = $factory($container->reveal(), 'TranslatorPluginManager');

        $this->assertInstanceOf(LoaderPluginManager::class, $translators);
        $this->assertTrue($translators->has('test'));
        $this->assertInstanceOf(PhpArray::class, $translators->get('test'));
        $this->assertTrue($translators->has('test-too'));
        $this->assertSame($translator, $translators->get('test-too'));
    }

    public function testDoesNotConfigureTranslatorServicesWhenServiceListenerPresent()
    {
        $translator = $this->prophesize(FileLoaderInterface::class)->reveal();
        $config = [
            'translator_plugins' => [
                'aliases' => [
                    'test' => PhpArray::class,
                ],
                'factories' => [
                    'test-too' => function ($container) use ($translator) {
                        return $translator;
                    },
                ],
            ],
        ];

        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $container->has('ServiceListener')->willReturn(true);
        $container->has('config')->shouldNotBeCalled();
        $container->get('config')->shouldNotBeCalled();

        $factory = new LoaderPluginManagerFactory();
        $translators = $factory($container->reveal(), 'TranslatorPluginManager');

        $this->assertInstanceOf(LoaderPluginManager::class, $translators);
        $this->assertFalse($translators->has('test'));
        $this->assertFalse($translators->has('test-too'));
    }

    public function testDoesNotConfigureTranslatorServicesWhenConfigServiceNotPresent()
    {
        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $container->has('ServiceListener')->willReturn(false);
        $container->has('config')->willReturn(false);
        $container->get('config')->shouldNotBeCalled();

        $factory = new LoaderPluginManagerFactory();
        $translators = $factory($container->reveal(), 'TranslatorPluginManager');

        $this->assertInstanceOf(LoaderPluginManager::class, $translators);
    }

    public function testDoesNotConfigureTranslatorServicesWhenConfigServiceDoesNotContainTranslatorsConfig()
    {
        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $container->has('ServiceListener')->willReturn(false);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn(['foo' => 'bar']);

        $factory = new LoaderPluginManagerFactory();
        $translators = $factory($container->reveal(), 'TranslatorPluginManager');

        $this->assertInstanceOf(LoaderPluginManager::class, $translators);
        $this->assertFalse($translators->has('foo'));
    }
}
