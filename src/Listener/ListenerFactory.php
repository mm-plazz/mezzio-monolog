<?php

namespace Plazz\Mezzio\Monolog\Listener;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ListenerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array<string, mixed>|null $options
     * @return Listener
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): Listener
    {
        /** @var array<string, mixed> $config */
        $config = $container->get('config');

        /** @var array<string, mixed> $monolog */
        $monolog = $config['monolog'] ?? [];
        $debug = (bool) ($config['debug'] ?? false);

        return new Listener($monolog, $debug);
    }
}
