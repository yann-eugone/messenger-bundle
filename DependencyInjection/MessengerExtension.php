<?php

namespace MessengerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Yann Eugoné <yann.eugone@gmail.com>
 */
class MessengerExtension extends Extension
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(
            $this->getConfiguration($configs, $container),
            $configs
        );

        $container->setParameter(
            'messenger.content_builder_defaults',
            $config['content_builder']
        );
        $container->setParameter(
            'messenger.logging_channel',
            $config['logging_channel']
        );

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        if (class_exists('Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle') &&
            $config['channels']['swiftmailer']['enabled']
        ) {
            $this->registerSwiftmailer($config['channels']['swiftmailer'], $container, $loader);
        }
        if (class_exists('Doctrine\Bundle\DoctrineBundle\DoctrineBundle') &&
            $config['channels']['doctrine']['enabled']
        ) {
            $this->registerDoctrine($config['channels']['doctrine'], $container, $loader);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration($this->name);
    }

    /**
     * @param array            $config
     * @param ContainerBuilder $container
     * @param XmlFileLoader    $loader
     */
    private function registerSwiftmailer(array $config, ContainerBuilder $container, XmlFileLoader $loader)
    {
        $container->setParameter(
            'messenger.swiftmailer_channel_defaults',
            [
                'from' => $config['from_addr'],
                'translator_catalog' => $config['translator_catalog'],
            ]
        );
        $loader->load('swiftmailer.xml');
    }

    /**
     * @param array            $config
     * @param ContainerBuilder $container
     * @param XmlFileLoader    $loader
     */
    private function registerDoctrine(array $config, ContainerBuilder $container, XmlFileLoader $loader)
    {
        $container->setParameter(
            'messenger.doctrine_channel_defaults',
            $config
        );
        $loader->load('doctrine.xml');
    }
}